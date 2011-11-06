<?php

require_once '../../config.php';
require_once '../../backend/vendor/SplClassLoader.php';
require_once '../../backend/vendor/orient-php-raw/OrientDB/OrientDB.php';
use asvis\Config as Config;

class NewImporter {
	protected $_nodeClusterID = null;
	protected $_connClusterID = null;
	protected $_poolClusterID = null;
	protected $_db = null;
	protected $_asrids = null;
	protected $_asconns = null;
	protected $_connStatuses = null;

	const LIMIT_CONNS = -1;

	function __construct($doImport = false) {
		if($doImport === true) {
			$this->import();
		}
	}

	function import() {
		$this->_connect2MySQLDB();
		$this->_connect2OrientDB();

		$this->_deleteAll();

		echo PHP_EOL .'OrientDB now ready for import.'. PHP_EOL;

		$this->_insertASNodes();
		
		$this->_loadConns();		
// 		$this->_insertASPools();
		
		$this->_updateASNodes();
		
		$this->_insertASConns();
	}

	protected function _deleteAll() {
		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASNode');
		echo PHP_EOL.'DELETED '.$result.' ASNodes'. PHP_EOL;

		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASConn');
		echo 'DELETED '.$result.' ASConns'. PHP_EOL;

		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASPool');
		echo 'DELETED '.$result.' ASPools'. PHP_EOL;
	}

	protected function _insertASNodes() {
		$this->_asrids = array();

		$ases = mysql_query(
			'SELECT ases.asnum AS asnum, ases.asname AS asname FROM ases ' .
			'INNER JOIN (' .
				'SELECT DISTINCT(asnum) as asnum FROM (' .
					'SELECT asnum FROM asup WHERE asnumup <> -1 UNION ' .
					'SELECT asnum FROM asdown WHERE asnumdown <> -1 UNION ' .
					'SELECT asnumup AS asnum FROM asup WHERE asnumup <> -1 UNION ' .
					'SELECT asnumdown AS asnum FROM asdown WHERE asnumdown <> -1' .
				') AS asnums) ' .
			'AS asnums ON ases.asnum = asnums.asnum ' .
			'ORDER BY ases.asnum'
		);
		echo PHP_EOL . 'MySQL query finished.'. PHP_EOL;

		echo PHP_EOL . 'Beginning OrientDB ASNode INSERT(s).'. PHP_EOL;
		$timeBegin = microtime(true);

		while ($as = mysql_fetch_assoc($ases)) {
			$asnum  = $as['asnum'];
			$asname = $as['asname'];

			$rid = $this->_insertASNode($asnum, $asname);
			$this->_asrids[$asnum] = array(
				'rid'	=> '#'. $this->_nodeClusterID .':'. $rid,
				'in'	=> array(),
				'out'	=> array(),
				'pools' => array()
			);
		}

		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASNode INSERT(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}


	protected function _loadConns() {
		$this->_loadConnsForDir('up');
		$this->_loadConnsForDir('down');
	} 

	protected function _insertASPools() {
		$pools = mysql_query('SELECT asnum, asnetwork, asnetmask FROM aspool ORDER BY asnum');
		echo PHP_EOL . 'MySQL query finished.'. PHP_EOL;
	
		echo PHP_EOL . 'Beginning OrientDB ASPool INSERT(s).'. PHP_EOL;
		$timeBegin = microtime(true);
	
		while ($pool = mysql_fetch_assoc($pools)) {
			$asnum = $pool['asnum'];
			$asnetwork = $pool['asnetwork'];
			$asnetmask = $pool['asnetmask'];
				
			$rid = $this->_insertASPool($this->_asrids[$asnum]['rid'], $asnetwork, $asnetmask);
			$this->_asrids[$asnum]['pools'][] = '#'. $this->_poolClusterID .':'. $rid;
		}
	
	
		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASPool INSERT(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}
	
	protected function _updateASNodes() {
		echo PHP_EOL . 'Beginning ASNode UPDATE(s).' . PHP_EOL;
		$timeBegin = microtime(true);
		
		foreach ($this->_asrids as $asnum => $asdata) {
			$this->_updateASNode($asdata['rid'], $asdata['conns'], $asdata['pools']);
		}
		
		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASNode UPDATE(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}
	
	protected function _insertASConns() {
		echo PHP_EOL . 'Beginning ASConn INSERT(s).' . PHP_EOL;
		$timeBegin = microtime(true);
		
		$this->_connStatuses = array();
		
		foreach ($this->_asconns['up'] as $conn) {
			$this->_connStatuses[$conn['from']][$conn['to']]['up'] = true;
			$this->_connStatuses[$conn['from']][$conn['to']]['down'] = false;
		}
		
		foreach ($this->_asconns['down'] as $conn) {
			$this->_connStatuses[$conn['from']][$conn['to']]['down'] = true;
			if (!isset($this->_connStatuses[$conn['from']][$conn['to']]['up'])) {
				$this->_connStatuses[$conn['from']][$conn['to']]['up'] = false;
			}
		}
		
		foreach ($this->_connStatuses as $from => $data) {
			foreach ($data as $to => $status) {
				$statusNum = 0;
				if (($status['up'] === true) && ($status['down'] !== false)) {
					$statusNum == 2;
				} else {
					$statusNum == 1;
				}
				
				$this->_insertASConn($from, $to, $statusNum);
			}
		}
		
		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASConn INSERT(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}
	
	protected function _connect2OrientDB() {
		echo 'Connecting to server...' . PHP_EOL;
		
		try {
			$this->_db = new OrientDB(Config::get('orient_db_host'), 2424);
		} catch (Exception $e) {
			die('Failed to connect: ' . $e->getMessage().PHP_EOL);
		}
	
		echo 'Opening DB...' . PHP_EOL;
		
		try {
			$clusters = $this->_db->DBOpen(
				Config::get('orient_db_name'),
				Config::get('orient_db_user'),
				Config::get('orient_db_pass')
			);
		
			foreach ($clusters['clusters'] as $cluster) {
				if ($cluster->name === 'asnode') {
					$this->_nodeClusterID = $cluster->id;
				}
				if ($cluster->name === 'asconn') {
					$this->_connClusterID = $cluster->id;
				}
				if ($cluster->name === 'aspool') {
					$this->_poolClusterID = $cluster->id;
				}
			}
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}
	}
	
	protected function _connect2MySQLDB() {
		$mysqlHost = Config::get('mysql_db_host');
		$mysqlName = Config::get('mysql_db_name');
		$mysqlUser = Config::get('mysql_db_user');
		$mysqlPass = Config::get('mysql_db_pass');
	
		$mysqlConnection = mysql_connect($mysqlHost, $mysqlUser, $mysqlPass);
	
		if(!$mysqlConnection) {
			die('Nie można połączyć z bazą MySQL '.$mysqlName.' jako uzytkownik '.$mysqlUser.PHP_EOL);
		}

		$isDBSelected = mysql_select_db($mysqlName);
	
		if(!$isDBSelected) {
			die('Nie można wybrać bazy '.$mysqlName.PHP_EOL);
		}
	}

	protected function _loadConnsForDir($dir) {
		echo PHP_EOL . "Querying MySQL as{$dir}... ";
		$ases = mysql_query(
			"SELECT asnum, asnum{$dir} FROM as{$dir} WHERE asnum{$dir} <> -1".
			(NewImporter::LIMIT_CONNS > 0 ? ' LIMIT '. NewImporter::LIMIT_CONNS : '')
		);
		
		echo 'finished' . PHP_EOL;
	
		while ($as = mysql_fetch_assoc($ases)) {
			$asnum		= $as['asnum'];
			$asnumdir	= $as['asnum'. $dir];
	
			if (!isset($this->_asrids[$asnum])) {
				echo "Aborting inserting ASConn from inexisting ASNode (no asnum = {$asnum})" . PHP_EOL;
				continue;
			}
			if (!isset($this->_asrids[$asnumdir])) {
				echo "Aborting inserting ASConn to inexisting ASNode (no asnum{$dir} = {$asnumdir})" . PHP_EOL;
				continue;
			}
	
			$this->_addConnection($asnum, $asnumdir);
			
			$this->_asconns[$dir][] = array(
				'from'	=> $this->_asrids[$asnum]['rid'],
				'to'	=> $this->_asrids[$asnumdir]['rid']
			);
		}
	}

	protected function _addConnection($fromNum, $toNum) {
		$from	= $this->_asrids[$fromNum]['rid'];
		$to		= $this->_asrids[$toNum]['rid'];
		
		if (!in_array($to, $this->_asrids[$fromNum]['out']) ) {
			$this->_asrids[$fromNum]['conns'][] = $to;
		}
		
		if (!in_array($from, $this->_asrids[$toNum]['in']) ) {
			$this->_asrids[$toNum]['conns'][] = $from;
		}
	}
	
	protected function _insertASNode($num, $name) {
		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				"INSERT INTO ASNode (name, num, num_as_string) VALUES ('{$name}', {$num}, '{$num}')"
			);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}
		
		$recordPosition = $result->__get('recordPos');
		
		return $recordPosition;
	}
	
	protected function _insertASPool($node, $network, $netmask) {
		
// 		echo PHP_EOL.'Inserting pool : node='.$node.' network='.$network.' netmask='.$netmask.PHP_EOL;
		
		$network_as_string = long2ip($network);
		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				"INSERT INTO ASPool (network, network_as_string, netmask, node) VALUES ({$network}, '{$network_as_string}', {$netmask}, {$node})"
			);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}
	
		$recordPosition = $result->__get('recordPos');
		return $recordPosition;
	}
		
	protected function _updateASNode($asNodeRID, $conns, $pools) {
		$connList	= implode(',', $conns);
		$poolList	= implode(',', $pools);
		
		$query = "UPDATE {$asNodeRID} SET conns = [{$connList}], pools = [{$poolList}]";
// 		echo PHP_EOL.$query;
		
		try {
			$result = $this->_db->command(OrientDB::COMMAND_QUERY, $query);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}
	}
	
	protected function _insertASConn($from, $to, $status) {
		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				"INSERT INTO ASConn (from, to, status) VALUES ({$from}, {$to}, {$status})"
			);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}
	}
	
}











