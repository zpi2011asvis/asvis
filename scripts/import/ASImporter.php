<?php

require_once '../../config.php';
require_once '../../backend/vendor/SplClassLoader.php';
require_once '../../backend/vendor/orient-php-raw/OrientDB/OrientDB.php';
use asvis\Config as Config;

class ASImporter {
	
	protected $_nodeClusterID = null;
	protected $_connClusterID = null;
	protected $_poolClusterID = null;
	protected $_db = null;
	
	protected $_connectionsUp = array();
	protected $_connectionsDown = array();
	protected $_connections = array();
	
	protected $_asrids = array();
	
	const LIMIT = -1;
	
	function __construct($doImport = false) {
		if($doImport === true) {
			$this->import();
		}
	}
	
	public function import() {
		$this->_connect2MySQLDB();
		$this->_connect2OrientDB();
		
		$this->_deleteAll();
		
		$this->_insertASNodes();
		$this->_loadConns();
		$this->_insertASPools();

		$this->_updateASNodes();
		
		$this->_insertASConns();
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
		$this->_loadConnsUp();
		$this->_loadConnsDown();
		
		
		
		foreach ($this->_connectionsUp as $fromNum => $to) {
			foreach ($to as $toNum => $bool) {
				if( !in_array($toNum, $this->_asrids[$fromNum]['out'])) {
					$this->_asrids[$fromNum]['out'][]	= $this->_asrids[$toNum]['rid'];
					$this->_asrids[$toNum]['in'][]		= $this->_asrids[$fromNum]['rid'];
				}
			}
		}
		
		foreach ($this->_connectionsDown as $toNum => $from) {
			foreach ($from as $fromNum => $bool) {
				if( !in_array($fromNum, $this->_asrids[$toNum]['in'])) {
					$this->_asrids[$fromNum]['out'][]	= $this->_asrids[$toNum]['rid'];
					$this->_asrids[$toNum]['in'][]		= $this->_asrids[$fromNum]['rid'];
				}
			}
		}
		
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
			$this->_updateASNode($asdata['rid'], $asdata['in'], $asdata['out'], $asdata['pools']);
		}
			
		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASNode UPDATE(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}
	
	protected function _insertASConns() {
		$this->_calculateStatuses();
		
		echo PHP_EOL . 'Beginning ASConn INSERT(s).' . PHP_EOL;
		$timeBegin = microtime(true);
		
		foreach ($this->_connections as $fromNum => $to) {
			$fromRID = $this->_asrids[$fromNum]['rid'];
			
			foreach ($to as $toNum => $status) {
				$toRID = $this->_asrids[$toNum]['rid'];
				
				$this->_insertASConn($fromRID, $toRID, $status);
			}
		}
		
		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASConn INSERT(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}
	
	protected function _calculateStatuses() {
		echo PHP_EOL . 'Beginning connection status calculation(s).'. PHP_EOL;
		$timeBegin = microtime(true);
		
		foreach ($this->_connectionsUp as $fromNum => $to) {
			foreach ($to as $toNum => $bool) {
				
				if(isset($this->_connectionsDown[$fromNum][$toNum])) {
					$this->_connections[$fromNum][$toNum] = 0;
				} else {
					$this->_connections[$fromNum][$toNum] = 2;
				}
				
			}
		}
		
		foreach ($this->_connectionsDown as $fromNum => $to) {
			foreach ($to as $toNum => $bool) {
		
				if(isset($this->_connectionsUp[$fromNum][$toNum])) {
					$this->_connections[$fromNum][$toNum] = 0;
				} else {
					$this->_connections[$fromNum][$toNum] = 1;
				}
		
			}
		}
		
		$timeEnd = microtime(true);
		echo PHP_EOL . 'Connection status calculation(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
		
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
	
	protected function _loadConnsUp() {
		echo PHP_EOL . "Querying MySQL asup... ";
		
		$ases = mysql_query(
			"SELECT asnum, asnumup FROM asup WHERE asnumup <> -1".
			(ASImporter::LIMIT > 0 ? ' LIMIT '. ASImporter::LIMIT : '')
		);
		
		echo 'finished' . PHP_EOL;
		
		echo PHP_EOL . "Building connections... ";
		
		while ($as = mysql_fetch_assoc($ases)) {
			$this->_connectionsUp[$as['asnum']][$as['asnumup']] = true;
		}
		
		echo 'finished' . PHP_EOL;
	}
	
	protected function _loadConnsDown() {
		echo PHP_EOL . "Querying MySQL asdown... ";
	
		$ases = mysql_query(
				"SELECT asnum, asnumdown FROM asdown WHERE asnumdown <> -1".
		(ASImporter::LIMIT > 0 ? ' LIMIT '. ASImporter::LIMIT : '')
		);
	
		echo 'finished' . PHP_EOL;
	
		echo PHP_EOL . "Building connections... ";
	
		while ($as = mysql_fetch_assoc($ases)) {
			$this->_connectionsDown[$as['asnumdown']][$as['asnum']] = true;
		}
	
		echo 'finished' . PHP_EOL;
	}
	
	protected function _updateASNode($asNodeRID, $in, $out, $pools) {
		$inList		= implode(',', $in);
		$outList	= implode(',', $out);
		$poolList	= implode(',', $pools);
	
		$query = "UPDATE {$asNodeRID} SET in = [{$inList}], out = [{$outList}], pools = [{$poolList}]";
// 		echo PHP_EOL.$query;
	
		try {
			$result = $this->_db->command(OrientDB::COMMAND_QUERY, $query);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}
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
	
	protected function _deleteAll() {
		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASNode');
		echo PHP_EOL.'DELETED '.$result.' ASNodes'. PHP_EOL;
	
		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASConn');
		echo 'DELETED '.$result.' ASConns'. PHP_EOL;
	
		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASPool');
		echo 'DELETED '.$result.' ASPools'. PHP_EOL;
	}
	
}











