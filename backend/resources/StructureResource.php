<?php

namespace asvis\resources;

require_once __DIR__.'/../../config.php';

use asvis\Config as Config;
use asvis\lib\Engine;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;

/**
 * @uri /structure/graph/{number}/{depth}
 */
class StructureGraphResource extends Resource {
	function get($request, $number, $depth) {
		$response = new Response($request);
		
		$number = (int) $number;
		$response->s404Unless($number, 'Nie przekazano prawidłowego numeru AS.');
		
		$depth = (int) $depth;
		$response->s404Unless($depth, 'Nie przekazano prawidłowego parametru głębokości.');
		$response->s404If($depth < 0 || $depth > Config::get('orient_max_fetch_depth'), 'Przekazany parametr wysokości jest spoza zakresu.');

		if($response->code === 200) {
			$forJSON = $this->_engine->structureGraph($number, $depth);
			$response->s404If(is_null($forJSON), 'Nie istnieje AS o podanym numerze.');
		
			$response->json($forJSON);
		}
		
		return $response;
	}
}

/**
 * @uri /structure/trees/{number}/{height}/{dir}
 */
class StructureTreesResource extends Resource {
	function get($request, $number, $height, $dir) {		
		$response = new Response($request);
		
		$number = (int) $number;
		$response->s404Unless($number, 'Nie przekazano prawidłowego numeru AS.');
		
		$height = (int) $height;
		$response->s404Unless($height, 'Nie przekazano prawidłowego parametru głębokości.');
		$response->s404If($height < 0 || $height > Config::get('orient_max_fetch_depth'), 'Przekazany parametr głębokości jest spoza zakresu.');
		
		$response->s404If($dir !== 'in' && $dir !== 'out' && $dir !== 'both', 'Nie przekazano prawidłowego parametru kierunku.');

		if($response->code === 200) {
			$forJSON = $this->_engine->structureTree($number, $height, $dir);
			$response->s404If(is_null($forJSON), 'Nie istnieje AS o podanym numerze.');
		
			$response->json($forJSON);
		}
		
		return $response;
	}
}

/**
 * @uri /structure/paths/{num_start}/{num_end}/{dir}
 */
class StructurePathsResource extends Resource {
	function get($request, $num_start, $num_end, $dir) {
		$dirs = array('up' => 'out', 'down' => 'in', 'both' => 'both');
		$dir = @ $dirs[$dir];

		$response = new Response($request);
		
		$num_start = (int) $num_start;
		$response->s404Unless($num_start, 'Nie przekazano prawidłowego początkowego numeru AS.');
		
		$num_end = (int) $num_end;
		$response->s404Unless($num_end, 'Nie przekazano prawidłowego końcowego numeru AS.');
		
		$response->s404If(!$dir, 'Nie przekazano prawidłowego parametru kierunku.');

		if ($response->code === 200) {
			$forJSON = $this->_engine->structurePath($num_start, $num_end, $dir);
			$response->s404If($forJSON === 0, 'Nie znaleziono żadnej istniejącej ścieżki w badanym zakresie.');
			$response->s404If(is_null($forJSON), 'Nie istnieje AS o podanym numerze początkowym.');
		
			$response->json($forJSON);
		}
			
		return $response;
	}
}
