<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;
use asvis\lib\Engine as Engine;

/**
 * @uri /nodes/find
 */
class NodesFindResource extends Resource {
	function get($request) {
		$response = new Response($request);
		$number = $this->_get['number'];
		
		$response->json(Engine::nodesFind($number));
		
		return $response;
	}
}
