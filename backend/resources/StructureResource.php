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
		$response->json(array('dupa','kupa'));
		return $response;
	}
}
