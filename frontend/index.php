<?php

// config
use asvis\Config as Config;
require_once __DIR__ . '/../config.php';

if (Config::get('env') === 'dev') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

function getSubdirFiles($main_dir) {
	$result = array();

	$dirs = scandir($main_dir); 
	foreach ($dirs as $dir)  { 
		$sub_dir = $main_dir .'/'. $dir;
		if ($dir === '.' || $dir === '..') { 
			continue;
		} 

		if (is_file($sub_dir)) {
			$result[] = $sub_dir;
		}
		else {
			$files = scandir($sub_dir); 
			foreach ($files as $file)  { 
				if ($file === '.' || $file === '..') { 
					continue; 
				}
				else { 
					$result[] = $sub_dir .'/'. $file; 
				} 
			}
		} 
	}    
	return $result; 
}

function includeJS($paths) {
	foreach ($paths as $path) {
		printf('<script src="%s/js/%s"></script>', Config::get('frontend_base_uri'), $path);
	}
}

function includeTemplates() {
	$dir = __DIR__ . '/templates';
	
	$files = getSubdirFiles($dir);
	foreach ($files as $file) {
		$name = str_replace($dir.'/', '', $file);
		$name = substr($name, 0, strrpos($name, '.'));
		echo '<script type="text/ejs" class="template" data-name="'. $name .'">';
		readfile($file);
		echo '</script>';
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
		'../data/sample_1.js',
		'../data/sample_2.js',
		'../data/sample_3.js',
		'vendor/three.js',
		'vendor/signals.js',
		'vendor/crossroads.js',
		'vendor/xui.js',
		'vendor/cjs_exports_webmade.js',
		'vendor/ejs.js',
		'xui_extends.js',
		'app.js',
		'util.js',
		'lib/dispatcher_adapter.js',
		'lib/xhr_adapter_xui.js',
		'lib/local_db.js',
		'lib/flash.js',
		'lib/templates.js',
		'lib/renderer.js',
		'lib/stores/store.js',
		'lib/stores/remote_store.js',
		'lib/resources/resource.js',
		'lib/resources/nodes_resource.js',
		'lib/resources/structures_resource.js',
		'lib/widgets/widget.js',
		'lib/widgets/start_form_widget.js',
		'lib/widgets/graph_widget.js',
	)) ?>
	<? includeTemplates() ?>
	<script>
		this.DEBUG = <?= Config::get('env') === 'dev' ? 'true' : 'false' ?>;
		app.start({
		});
	</script>
</body>
</html>
