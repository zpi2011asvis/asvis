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
	
	function __construct($parameters) {
		parent::__construct($parameters);
		$this->_get = $_GET;
		$this->_post = $_POST;
		
		Engine::init(new DB());
	}
}
