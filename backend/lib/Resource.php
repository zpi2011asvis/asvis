<?php

namespace asvis\lib;
require_once 'Engine.php';
require_once \asvis\Config::get('backend_db_engine') . '.php';
use \Resource as TonicResource;
use asvis\lib\Engine as Engine;
use asvis\Config as Config;

class Resource extends TonicResource {
	protected $_get = null;
	protected $_post = null;

	/**
	 * @var Engine
	 */
	protected $_engine;
	
	function __construct($parameters) {
		parent::__construct($parameters);
		$this->_request = $_REQUEST;
		
		$engineClass = 'asvis\\lib\\' . Config::get('backend_db_engine');
		
		$this->_engine = new $engineClass();
	}
	
	protected function getParam($paramName, $default = null) {
		if (array_key_exists($paramName, $this->_request)) {
			return $this->_post[$paramName];
		}
		else {
			return $default;
		}
	}
}
