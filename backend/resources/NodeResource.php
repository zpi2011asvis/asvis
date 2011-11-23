<?php

namespace asvis\resources;
require_once __DIR__.'/../lib/mysql/MySQLEngine.php';
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;
use asvis\lib\Engine as Engine;
use asvis\lib\mysql\MySQLEngine as MySQLEngine;

/**
 * @uri /nodes/find/{number}
 */
class NodesFindResource extends Resource {
	function get($request, $number) {
		$response = new Response($request);
		
		$this->_engine = new MySQLEngine();
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

		$numbers = json_decode($this->getParam('numbers'));
		$response->s404Unless($numbers, 'a');

		$forJSON = $this->_engine->nodesMeta($numbers);
		$response->s404If(is_null($forJSON), 'a');

		$response->json($forJSON);
		return $response;
	}
}
