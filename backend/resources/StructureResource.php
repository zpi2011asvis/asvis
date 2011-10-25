<?php

namespace asvis\resources;
use asvis\lib\Engine;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;

/**
 * @uri /structure/graph/{number}/{depth}
 */
class StructureGraphResource extends Resource {
	function get($request, $number, $depth) {
		$response = new Response($request);
		$response->json($this->_engine->structureGraph((int) $number, (int) $depth));
		return $response;
	}
}

/**
 * @uri /structure/tree
 */
class StructureTreeResource extends Resource {
	function get($request) {
		$response = new Response($request);
		$response->json(array(
			"345"=>array("connections_up"=>array(3245,2345,2356),"connections_down"=>array(34765,1235,5325)),
			"4234"=>array("connections_up"=>array(3245,2345,2356),"connections_down"=>array())
		));
		return $response;
	}
}
