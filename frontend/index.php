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
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/styles.css">
</head>
<body id="container">
	<header id="top">
		<h1><a href="/">Asvis</a></h1>
	</header>
	<p id="flash"><span class="message"></span></p>
	<div id="graph">
		<div id="graph_renderer"></div>
	</div>
	<aside id="sidebar">
		<a href="/">/</a><br>
		<a href="/abc">/abc</a><br>
		<a href="/kopytko">/kopytko</a><br>
		<a href="/kopytko/134">/kopytko/134</a><br>
		<form action="/dupa" method="post">
			<input type="text" name="a" value="v">
			<button type="submit">send</button>
		</form>
	</aside>
	
	<? includeJS(array(
		'vendor/three_webgl.js',
		'vendor/signals.js',
		'vendor/crossroads.js',
		'vendor/xui.js',
		'vendor/cjs_exports_webmade.js',
		'xui_extends.js',
		'app.js',
		'lib/dispatcher_adapter.js',
		'lib/xhr_adapter_xui.js',
		'lib/local_db.js',
		'lib/flash.js',
		'lib/stores/store.js',
		'lib/stores/remote_store.js',
		'lib/resources/resource.js',
		'lib/resources/nodes_resource.js'
	)) ?>
	<script>
		this.DEBUG = <?= Config::get('env') === 'dev' ? 'true' : 'false' ?>;
		app.start({
		});
	</script>
</body>
</html>
