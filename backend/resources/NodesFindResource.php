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
		$response->json(array(
			"34567"=>array("name"=>"AS34567"),
			"34579"=>array("name"=>"AS34579"),
			"345"=>array("name"=>"AS345")
		));
		return $response;
	}
}
