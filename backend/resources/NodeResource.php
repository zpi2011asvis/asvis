<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;
use asvis\lib\Engine as Engine;

/**
 * @uri /nodes/find/{number}
 */
class NodesFindResource extends Resource {
	function get($request, $number) {
		$response = new Response($request);
		
		$response->json($this->_engine->nodesFind((int) $number));
		
		return $response;
	}
}

/**
 * @uri /nodes/meta
 */
class NodesMetaResource extends Resource {
	function post($request) {
		$response = new Response($request);

		$numbers = json_decode($this->getParam('numbers', '[]'));
		$response->json($this->_engine->nodesMeta($numbers));
		
		return $response;
	}
}
