<?php

namespace asvis\lib;
require_once 'DB.php';
require_once 'Engine.php';
use \Resource as TonicResource;
use asvis\lib\DB as DB;
use asvis\lib\Engine as Engine;

class Resource extends TonicResource {
	protected $_get = null;
	protected $_post = null;

	/**
	 * @var Engine
	 */
	protected $_engine;
	
	function __construct($parameters) {
		parent::__construct($parameters);
		$this->_get = $_GET;
		$this->_post = $_POST;
		
		$engineClass = Config::get('backend_db_engine');
		
		$this->_engine = new $engineClass();
	}
	
	protected function getGet($paramName, $default = null) {
		if (array_key_exists($paramName, $this->_post)) {
			return $this->_get[$paramName];
		}
		else {
			return $default;
		}
	}
	
	protected function getPost($paramName, $default = null) {
		if (array_key_exists($paramName, $this->_post)) {
			return $this->_post[$paramName];
		}
		else {
			return $default;
		}
	}
}
