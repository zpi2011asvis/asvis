<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;

/**
 * @uri /
 */
class CoreResource extends Resource {
	function get($request) {
		echo 'aaa';
		$response = Response($request);
		return $response;
	}
}
