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
		
		$response->json($this->_engine->nodesFind($number));
		
		return $response;
	}
}

/**
 * @uri /nodes/meta/{numbers}
 */
class NodesMetaResource extends Resource {
	function get($request, $numbers) {
		$response = new Response($request);
		
		$numbers = explode(',', $numbers);
		
		$response->json($this->_engine->nodesMeta($numbers));
		
		return $response;
	}
}
