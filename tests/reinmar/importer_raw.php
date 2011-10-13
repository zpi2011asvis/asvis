<?php

require_once '../../config.php';
require_once '../../backend/vendor/SplClassLoader.php';
require_once '../../backend/vendor/orient-php-raw/OrientDB/OrientDB.php';
use asvis\Config as Config;

class ASImporter {
	protected $_nodeClusterID = null;
	protected $_connClusterID = null;
	protected $_db = null;
	protected $_asrids = null;

	const LIMIT_CONNS = 2000;

	function __construct() {
		$this->_connect2MySQLDB();
		$this->_connect2OrientDB();

		$this->_deleteAll();

		echo PHP_EOL .'OrientDB now ready for import.'. PHP_EOL;

		$this->_insertASNodes();
		$this->_insertASConns();
		$this->_updateASNodes();
	}

	protected function _connect2OrientDB() {
		echo 'Connecting to server...' . PHP_EOL;
		try {
			$this->_db = new OrientDB(Config::get('orient_db_host'), 2424);
		}
		catch (Exception $e) {
			die('Failed to connect: ' . $e->getMessage());
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
			die('Nie można połączyć z bazą MySQL '.$mysqlName.' jako uzytkownik '.$mysqlUser);
		}

		$isDBSelected = mysql_select_db($mysqlName);

		if(!$isDBSelected) {
			die('Nie można wybrać bazy '.$mysqlName);
		}
	}

	protected function _deleteAll() {
		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'TRUNCATE CLASS ASNode');
		echo PHP_EOL.'DELETED '.$result.' ASNodes'. PHP_EOL;

		$result = $this->_db->command(OrientDB::COMMAND_QUERY, 'TRUNCATE CLASS ASConn');
		echo 'DELETED '.$result.' ASConns'. PHP_EOL;
	}

	protected function _insertASNodes() {
		$this->_asrids = array();

		$ases = mysql_query('SELECT asnum, asname FROM ases ORDER BY asnum');
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

	protected function _updateASNodes() {
		echo PHP_EOL . 'Begginning ASNode UPDATE(s).' . PHP_EOL;
		$timeBegin = microtime(true);

		foreach ($this->_asrids as $asnum => $asdata) {
			$this->_updateASNode($asdata['rid'], $asdata['in'], $asdata['out']);
		}

		$timeEnd = microtime(true);
		echo PHP_EOL . 'ASNode UPDATE(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;
	}

	protected function _insertASNode($num, $name) {
		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				"INSERT INTO ASNode (num, name) VALUES ('{$num}', '{$name}')"
			);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}

		$recordPosition = $result->__get('recordPos');
		// 	echo 'Created record: ' . $result . '. Record position: '.$recordPosition . PHP_EOL;
		return $recordPosition;
	}

	protected function _insertASConn($from, $to, $isUp) {
		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				'INSERT INTO ASConn (in, out, up) VALUES ('.$from.','.$to.','.$isUp.')'
			);
		} catch (OrientDBException $e) {
			echo $e->getMessage() . PHP_EOL;
		}

		$recordPosition = $result->__get('recordPos');
		return $recordPosition;
	}

	protected function _updateASNode($asNodeRID, $fromList, $toList) {
		$fromList = implode(',', $fromList);
		$toList = implode(',', $toList);

		echo 'UPDATING ASNode: ' .
			"UPDATE ASNode SET in = [{$fromList}], out = [{$toList}] WHERE @rid = {$asNodeRID}" . PHP_EOL;
		
		try {
			$result = $this->_db->command(
				OrientDB::COMMAND_QUERY,
				"UPDATE ASNode SET in = [{$fromList}], out = [{$toList}] WHERE @rid = {$asNodeRID}"
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


new ASImporter();
