<?php

namespace asvis\lib;
use \Resource as TonicResource;
use \DB as DB;

require_once 'DB.php';

class Resource extends TonicResource {
	protected $_db = null;
	
	function __construct($parameters) {
		parent::__construct($parameters);
		
		$this->_db = new DB();
	}
}
