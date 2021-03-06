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
		
		if ($response->code === 200) {
			$mysqlEngine = new MySQLEngine();
			$forJSON = $mysqlEngine->nodesFind($number);
			$response->s500If(is_null($forJSON), 'Błąd pobierania danych z bazy.');
		
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

		if ($response->code === 200) {
			$mysqlEngine = new MySQLEngine();
			$forJSON = $mysqlEngine->nodesMeta($numbers);
			$response->s404If(empty($forJSON), 'Nie znaleziono informacji na temat wszystkich AS o przekazanych numerach.');
			$response->s500If(is_null($forJSON), 'Błąd pobierania danych z bazy.');
		
			$response->json($forJSON);
		}
		
		return $response;
	}
}
