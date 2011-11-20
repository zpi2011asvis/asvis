<?php

namespace asvis\resources;
use asvis\lib\Response as Response;
use asvis\lib\Resource as Resource;
use asvis\lib\Engine as Engine;

/**
 * @uri /connections/meta
 */
class ConnectionsMetaResource extends Resource {
	function post($request) {
		$response = new Response($request);

		$for_node = (int) $this->getParam('for_node');
		$response->s404Unless($for_node, 'a');

		$forJSON = $this->_engine->connectionsMeta($for_node);
		$response->s404If(is_null($forJSON), 'a');

		$response->json($forJSON);
		return $response;
	}
}
