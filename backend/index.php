<?php

namespace asvis;
// tonic
use \Request as Request;
use \ResponseException as ResponseException;
// config
use asvis\Config as Config;

require_once '../config.php';
require_once 'vendor/tonic/lib/tonic.php';
require_once 'lib/Resource.php';
require_once 'lib/Response.php';
require_once 'resources/include.php';

if (Config::get('env') === 'dev') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

$request = new Request(array( 
	'baseUri' => '/backend',
	/*'mount' => array(
		'asvis\resources' => '/additional_prefix'
	)*/
));

try {
    $resource = $request->loadResource();
    $response = $resource->exec($request);
}
catch (ResponseException $e) {
	$response = $e->response($request);
}
$response->output();
