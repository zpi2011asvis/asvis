<?php

namespace asvis;
require_once '../config.php';
require_once 'vendor/tonic/lib/tonic.php';
require_once 'lib/Resource.php';
require_once 'lib/Response.php';
require_once 'resources/include.php';
use \Request as Request;
use \ResponseException as ResponseException;
use \Exception as Exception;
use asvis\Config as Config;

if (Config::get('env') === 'dev') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

$request = new Request(array( 
	'baseUri' => Config::get('backend_base_uri'),
	/*'mount' => array(
		'asvis\resources' => '/additional_prefix'
	)*/
));

try {
    $resource = $request->loadResource();
    $response = $resource->exec($request);
	$response->output();
}
catch (ResponseException $e) {
	$response = $e->response($request);
	$response->output();
}
catch (Exception $e) {
	header('HTTP/1.1 500 Internal Server Error');

	# TODO lepsza obsługa exceptionów - zależna od środowiska
	echo '<pre>';
	var_dump($e);
	echo '</pre>';
}
