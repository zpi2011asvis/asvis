<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;
use asvis\lib\Engine as Engine;

/**
 * @uri /connections/meta/{num_for}
 */
class ConnectionsMetaResource extends Resource {
	function get($request, $num_for) {
		$response = new Response($request);

		$num_for = (int) $num_for;
		$response->s404Unless($num_for, 'Nie przekazano prawidłowego numeru AS.');
		
		if($response->code === 200) {
			$forJSON = $this->_engine->connectionsMeta($num_for);
			$response->s404If(is_null($forJSON), 'Nie istnieje AS o podanym numerze.');

			$response->json($forJSON);
		}
		
		return $response;
	}
}
