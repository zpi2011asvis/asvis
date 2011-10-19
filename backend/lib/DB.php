<?php

namespace asvis\lib;
require_once '../config.php';
require_once 'vendor/orient-php-raw/OrientDB/OrientDB.php';
use asvis\Config as Config;
use \OrientDB as OrientDB;

class DB {
	/**
	 * @var OrientDB
	 */
	protected $_driver = null;

	function __construct() {
		$this->_driver = new OrientDB(Config::get('orient_db_host'), 2424);
		$this->_driver->DBOpen(
			Config::get('orient_db_name'),
			Config::get('orient_db_user'),
			Config::get('orient_db_pass')
		);
	}
	
	public function query($query) {
		$result = $this->_driver->command(OrientDB::COMMAND_QUERY, $query);
		
		return $result;
	}
	
	public function loadGraph($asNum, $fetchPlan) {
// 		$rid = $this->getRID($asNum);
// 		$result = $this->_driver->recordLoad($rid, $fetchPlan);

		$result = $this->_driver->selectAsync('SELECT * FROM ASNode WHERE num = '.$asNum, $fetchPlan);
		
		$ret = array (
			'origin' => $result[0],
			'connected' => $this->_driver->cachedRecords
		);
		
		return $ret;
	}
	
	private function getRID($asNum) {
		$query = 'SELECT FROM ASNode WHERE num = '.$asNum;
		$result = $this->query($query);
		
		return $result[0]->recordID;
	}
	
}
