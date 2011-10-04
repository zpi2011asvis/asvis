<?php

require_once 'vendor/tonic/lib/tonic.php';
require_once 'lib/SplClassLoader.php';

$request = new Request(array( 
	'baseUri' => '/backend'
));

try {
    $resource = $request->loadResource();
    $response = $resource->exec($request);
}
catch (ResponseException $e) {
    switch ($e->getCode()) {
		default:
			$response = $e->response($request);
    }
}
$response->output();
