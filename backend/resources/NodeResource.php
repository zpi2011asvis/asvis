<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;

/**
 * @uri /nodes/find
 */
class NodesFindResource extends Resource {
	function post($request) {
		$response = new Response($request);
		$response->json(array(1,2,3,4,5,6));
		return $response;
	}
}

