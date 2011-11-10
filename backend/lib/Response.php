<?php

namespace asvis\lib;
use \Response as TonicResponse;

class Response extends TonicResponse {
	function __construct($request, $uri = NULL) {
		parent::__construct($request, $uri);
	}

	public function json($obj) {
		$this->addHeader('Content-Type', 'application/json');
		$this->body = json_encode($obj);
	}
	
	public function s404Unless($boolean) {
		if(!$boolean) {
			$this->code = Response::NOTFOUND;
		}
		
		return $boolean;
	}
	
	public function s500Unless($boolean) {
		if(!$boolean) {
			$this->code = Response::INTERNALSERVERERROR;
		}
		
		return $boolean;
	}
}
