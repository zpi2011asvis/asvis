<?php

require_once '../../config.php';
use asvis\Config as Config;

class Engine {
	protected $_db = null;

	public static init($db) {
		$this->_db = $db;	
	}

	public static nodesFind($number) {
				
	}
}


