<?php

namespace asvis\lib;
require_once 'Engine.php';
require_once 'OrientEngine.php';
use \Resource as TonicResource;
use asvis\lib\Engine as Engine;
use asvis\lib\OrientEngine as OrientEngine;

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
		
		$this->_engine = new OrientEngine();
	}
	
	protected function getGet($paramName) {
		return $this->_get[$paramName];
	}
	
	protected function getPost($paramName) {
		return $this->_post[$paramName];
	}
}
