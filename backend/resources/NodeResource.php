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
		
		$number = (int) $number;
		$response->s404Unless($number, 'Nie przekazano prawidłowego numeru AS.');
		
		if($response->code === 200) {
			$this->_engine = new MySQLEngine();
			$forJSON = $this->_engine->nodesFind($number);
			$response->s500If(is_null($forJSON), 'Błąd pobierania danych z bazy.');
			$response->s404If(empty($forJSON), 'Nie istnieje AS o podanym numerze.');
		
			$response->json($forJSON);
		}
		
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
		$response->s404If(empty($numbers), 'Nie przekazano żadnych numerów AS.');

		if($response->code === 200) {
			$forJSON = $this->_engine->nodesMeta($numbers);
			$response->s404If(is_null($forJSON), 'Nie znaleziono informacji na temat wszystkich AS o przekazanych numerach.');
		
			$response->json($forJSON);
		}
		
		return $response;
	}
}
