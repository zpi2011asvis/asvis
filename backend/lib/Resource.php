<?php

namespace asvis\lib;

require_once __DIR__.'/nodedb/NodeDBEngine.php';

use \Resource as TonicResource;
use asvis\lib\nodedb\NodeDBEngine as Engine;

class Resource extends TonicResource {
	protected $_request = null;

	/**
	 * @var Engine
	 */
	protected $_engine;
	
	function __construct($parameters) {
		parent::__construct($parameters);
		$this->_request = $_REQUEST;
		
		$this->_engine = new Engine();
	}
	
	protected function getParam($paramName, $default = null) {
		if (array_key_exists($paramName, $this->_request)) {
			return $this->_request[$paramName];
		}
		else {
			return $default;
		}
	}
}
