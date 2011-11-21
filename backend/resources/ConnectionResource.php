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

		$for_node = (int) $num_for;
		$response->s404Unless($for_node, 'a');

		$forJSON = $this->_engine->connectionsMeta($for_node);
		$response->s404If(is_null($forJSON), 'a');

		$response->json($forJSON);
		return $response;
	}
}
