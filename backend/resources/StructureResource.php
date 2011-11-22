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
		
		$response->s404If(is_null($forJSON), 'a');
		$response->json($forJSON);
		return $response;
	}
}

/**
 * @uri /structure/tree/{number}/{height}/{dir}
 */
class StructureTreeResource extends Resource {
	function get($request, $number, $height, $dir) {		
		$forJSON = $this->_engine->structureTree((int) $number, (int) $height, $dir);
		
		$response = new Response($request);
		
		$response->s404If(is_null($forJSON), 'a');
		$response->json($forJSON);
		return $response;
	}
}

/**
 * @uri /structure/path/{num_start}/{num_end}/{dir}
 */
class StructurePathResource extends Resource {
	function get($request, $num_start, $num_end, $dir) {		
		$forJSON = $this->_engine->structurePath((int) $num_start, (int) $num_end, $dir);
		
		$response = new Response($request);
		
		$response->s404If(is_null($forJSON), 'a');
		$response->json($forJSON);
		return $response;
	}
}
