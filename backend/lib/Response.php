<?php

namespace asvis\lib;
use \Response as TonicResponse;

class Response extends TonicResponse {
    function __construct($request, $uri = NULL) {
		parent::__construct($request, $uri);
	}
}
