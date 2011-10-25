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
		$this->_get = $_GET;
		$this->_post = $_POST;
		
		$engineClass = 'asvis\\lib\\' . Config::get('backend_db_engine');
		
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
