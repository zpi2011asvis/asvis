<?php

namespace asvis\lib;
require_once 'DB.php';
require_once 'Engine.php';
use \Resource as TonicResource;
use asvis\lib\DB as DB;
use asvis\lib\Engine as Engine;

class Resource extends TonicResource {
	
	function __construct($parameters) {
		parent::__construct($parameters);
		
		Engine::init(new DB());
	}
}
