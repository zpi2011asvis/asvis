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

	/**
	 * Sets response type to 404 when $boolean is true, giving $msg message
	 * Usage: s404If($foo < 100, 'foo is less then 100!';
	 * 
	 * @param boolean $boolean condition
	 * @param string $msg error message
	 */
	public function s404If($boolean, $msg) {
		if($boolean) {
			$this->code = Response::NOTFOUND;
			$this->body = $msg;
			$this->_error = true;
		}
		
		return $this;
	}
	
	/**
	 * Sets response type to 500 when $boolean is true, giving $msg message
	 * Usage: s500If($foo < 100, 'foo is less then 100!';
	 * 
	 * @param boolean $boolean condition
	 * @param string $msg error message
	 */
	public function s500If($boolean, $msg) {
		if($boolean) {
			$this->code = Response::INTERNALSERVERERROR;
			$this->body = $msg;
			$this->_error = true;
		}
		
		return $this;
	}
}
