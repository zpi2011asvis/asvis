<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;
use asvis\lib\Engine as Engine;

require_once 'lib/Engine.php';

/**
 * @uri /nodes/find
 */
class NodesFindResource extends Resource {
	function get($request, $number) {
		$response = new Response($request);
		
		$response->json(Engine::nodesFind($this->_db, $number));
		
		return $response;
	}
}
