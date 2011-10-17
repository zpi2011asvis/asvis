<?php

require_once '../../config.php';
require_once '../../backend/vendor/orient-php-raw/OrientDB/OrientDB.php';
use asvis\Config as Config;

class DB {
	protected $_driver = null;

	function __construct() {
		$this->_driver = new OrientDB(Config::get('orient_db_host'), 2424);
		$this->_db->DBOpen(
			Config::get('orient_db_name'),
			Config::get('orient_db_user'),
			Config::get('orient_db_pass')
		);
	}
	
	public function query($sql) {
		$result = $this->_driver->command(OrientDB::COMMAND_QUERY, $sql);
		
		return $result;
	}
}
