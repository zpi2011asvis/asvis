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
	<div id="container">
		<a href="/">/</a><br>
		<a href="/abc">/abc</a><br>
		<a href="/kopytko">/kopytko</a><br>
		<a href="/kopytko/134">/kopytko/134</a><br>
		<form action="/dupa" method="post">
			<input type="text" name="a" value="v">
			<button type="submit">send</button>
		</form>
	</div>
	
	<? includeJS(array(
		'vendor/signals.js',
		'vendor/crossroads.js',
		'vendor/xui.js',
		'vendor/cjs_exports_webmade.js',
		'xui_extends.js',
		'app.js',
		'lib/dispatcher_adapter.js',
		'lib/local_db.js',
		'lib/stores/store.js',
		'lib/resources/resource.js',
		'lib/resources/nodes_resource.js'
	)) ?>
	<script>
		app.start({
		});
	</script>
</body>
</html>
