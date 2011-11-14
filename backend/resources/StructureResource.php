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
		$forJSON = $this->_engine->structureGraph((int) $number, (int) $depth);
		
		$response = new Response($request);
		
		$response->s404Unless(!is_null($forJSON));
		$response->json($forJSON);
		return $response;
	}
}

/**
 * @uri /structure/tree/{number}/{height}
 */
class StructureTreeResource extends Resource {
	function get($request, $number, $height) {
		$dir = $this->getParam('dir', 'null');
		
		$forJSON = $this->_engine->structureTree((int) $number, (int) $height);
		
		$response = new Response($request);
		
		$response->s404Unless(!is_null($forJSON));
		$response->json($forJSON);
		return $response;
	}
}
