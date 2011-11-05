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
	protected $_asrids = null;

	// -1 to disable limits
	const LIMIT_CONNS = -1;

	function __construct($doImport = false) {
		if($doImport === true) {
			$this->import();
		}
	}
	
	/*
	 * Wyrzuciłem poniższe z konstruktora do osobnej funkcji,
	 * z mądrej książki wyczytałem że wrzucanie do konstruktora
	 * tego czym funkcja ma się zajmować jest złą praktyką.
	 * 
	 * W zamian dodałem do konstruktora nieobowiązkowy parametr
	 * $doImport aby zachować twoją funkcjonalność.
	 */
	function import() {		
		$this->_connect2MySQLDB();
		$this->_connect2OrientDB();
		
		$this->_deleteAll();
		
		echo PHP_EOL .'OrientDB now ready for import.'. PHP_EOL;
		
		$this->_insertASNodes();
		$this->_updateASNodes();
		$this->_insertASConns();
		$this->_insertASPools();
	}

	protected function _connect2OrientDB() {
		echo 'Connecting to server...' . PHP_EOL;
		try {
			$this->_db = new OrientDB(Config::get('orient_db_host'), 2424);
		}
		catch (Exception $e) {
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
		}
		catch (OrientDBException $e) {
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

	protected function _insertASNodes() {
		$this->_asrids = array();
		
		// wszystkie ASy które są połączone do jakichkolwiek
		// nieterminalnych Connów
		// inner join jest 1e10x szybszy niż IN ()
		// należy się zastanowić czy inne zapytania do bazy też nie powinny
		// odsiać niepotrzebnych wierszy
		// w przypadku asconn nnajpewniej sam warunek <>-1 załatwia sprawę
		// a co w przypadku aspooli?
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
		//var_dump(mysql_fetch_assoc($ases));
		//die();

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

	public function _insertASConns() {
		echo PHP_EOL . 'Beginning OrientDB ASConn INSERT(s).'. PHP_EOL;
		$timeBegin = microtime(true);

		$this->_insertASConnsForDir('up');
		$this->_insertASConnsForDir('down');

		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASConn INSERT(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}
	
	public function _insertASPools() {
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

	protected function _insertASConn($from, $to, $isUp) {
		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				"INSERT INTO ASConn (in, out, up) VALUES ({$from},{$to},{$isUp})"
			);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}

		$recordPosition = $result->__get('recordPos');
		return $recordPosition;
	}
	
	protected function _insertASPool($node, $network, $netmask) {
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

	protected function _updateASNode($asNodeRID, $fromList, $toList, $poolList) {
		$fromList = implode(',', $fromList);
		$toList = implode(',', $toList);
		$poolList = implode(',', $poolList);

		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				"UPDATE {$asNodeRID} SET in = [{$fromList}], out = [{$toList}], pools = [{$poolList}]" 
			);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}
	}

	/*
	 * Insert ASConn for one given direction
	*/
	protected function _insertASConnsForDir($dir) {
		echo PHP_EOL . "Querying MySQL as{$dir}... ";
		$ases = mysql_query(
			"SELECT asnum, asnum{$dir} FROM as{$dir} WHERE asnum{$dir} <> -1".
			(ASImporter::LIMIT_CONNS > 0 ? ' LIMIT '. ASImporter::LIMIT_CONNS : '')
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
				
			$from = $this->_asrids[$asnum]['rid'];
			$to = $this->_asrids[$asnumdir]['rid'];

			$rid = $this->_insertASConn($from, $to, $dir === 'up' ? 'true' : 'false');
				
			$this->_asrids[$asnum]['out'][] = '#'. $this->_connClusterID .':'. $rid;
			$this->_asrids[$asnumdir]['in'][] = '#'. $this->_connClusterID .':'. $rid;
		}
	}
}
