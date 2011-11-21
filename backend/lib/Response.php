<?php

namespace asvis\lib;
use \Response as TonicResponse;

class Response extends TonicResponse {
	protected $_error = false;

	function __construct($request, $uri = NULL) {
		parent::__construct($request, $uri);
	}

	public function json($obj) {
		if (!$this->_error) {
			$this->addHeader('Content-Type', 'application/json');
			$this->body = json_encode($obj);
		}
	}
	
	public function s404Unless($boolean, $msg) {
		return $this->s404If(!$boolean, $msg);
	}
	
	public function s500Unless($boolean, $msg) {
		return $this->s500If(!$boolean, $msg);
	}

	public function s404If($boolean, $msg) {
		if($boolean) {
			$this->code = Response::NOTFOUND;
			$this->body = $msg;
			$this->_error = true;
		}
		
		return $this;
	}
	
	public function s500If($boolean, $msg) {
		if($boolean) {
			$this->code = Response::INTERNALSERVERERROR;
			$this->body = $msg;
			$this->_error = true;
		}
		
		return $this;
	}
}
