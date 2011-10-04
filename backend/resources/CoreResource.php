<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;

/**
 * @uri /
 */
class CoreResource extends Resource {
	function get($request) {
		$response = new Response($request);
		$response->json(array(1,2,3));
		return $response;
	}
}
