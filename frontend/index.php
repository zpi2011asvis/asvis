<?php

// config
use asvis\Config as Config;
require_once __DIR__ . '/../config.php';

if (Config::get('env') === 'dev') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

function includeJS($paths) {
	foreach ($paths as $path) {
		printf('<script src="%s/js/%s"></script>', Config::get('frontend_base_uri'), $path);
	}
}


?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>ASvis</title>
</head>
<body>
	
	<? includeJS(array(
		'vendor/signals.js',
		'vendor/crossroads.js',
		'vendor/xui.js',
		'vendor/cjs_exports_webmade.js',
		'app.js',
	)) ?>
	<script>
		app.start({
		});
	</script>
</body>
</html>
