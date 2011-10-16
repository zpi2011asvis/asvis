<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;

/**
 * @uri /structure/graph
 */
class StructureGraphResource extends Resource {
	function get($request) {
		$response = new Response($request);
		$response->json(array(
			"345"=>array("connections_up"=>array(3245,2345,2356),"connections_down"=>array(34765,1235,5325)),
			"4234"=>array("connections_up"=>array(3245,2345,2356),"connections_down"=>array())
		));
		return $response;
	}
}
