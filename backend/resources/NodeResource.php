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
		
		$response->json(Engine::nodesFind($number));
		
		return $response;
	}
}

/**
 * @uri /nodes/meta
 */
class NodesMetaResource extends Resource {
	function post($request) {
		$response = new Response($request);
		$response->json(array(
			"1234"=>array("name"=>"AS1234", "pools"=>array("netmask"=>24)),
			"4234"=>array("name"=>"AS4234", "pools"=>array("netmask"=>12))
		));
		return $response;
	}
}
