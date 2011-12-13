<?php

// config
use asvis\Config as Config;
require_once __DIR__ . '/../config.php';

if (Config::get('env') === 'dev') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}
elseif (Config::get('env') === 'prod') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 0);
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
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/init.css">
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/components.css">
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/layout.css">
</head>
<body id="container">
	<header id="top">
		<h1><a href="/"><strong>AS</strong>vis</a></h1>

		<nav id="menu">
			<ul>
				<li><a href="/">pokaż AS</a></li>
				<li><a href="/find/trees">wyszukaj drzewa</a></li>
				<li><a href="/find/paths">wyszukaj ścieżki</a></li>
			</ul>
		</nav>
	</header>
	<p id="flash"><span class="message"></span></p>
	<div id="graph">
		<div id="graph_renderer"></div>
	</div>
	<aside id="sidebar">
	</aside>

	<? includeJS(array(
		'compatibility.js',
		'vendor/three.js',
		'vendor/signals.js',
		'vendor/crossroads.js',
		'vendor/xui.js',
		'vendor/cjs_exports_webmade.js',
		'vendor/ejs.js',
		'xui_extends.js',
		'app.js',
		'routes.js',
		'util.js',
		'lib/dispatcher_adapter.js',
		'lib/xhr_adapter_xui.js',
		'lib/local_db.js',
		'lib/flash.js',
		'lib/templates.js',
		'lib/fba.js',
		'lib/vizir.js',
		'lib/camera_man.js',
		'lib/gods_finger.js',
		'lib/renderer.js',
		'lib/stores/store.js',
		'lib/stores/memory_store.js',
		'lib/stores/remote_store.js',
		'lib/resources/resource.js',
		'lib/resources/nodes_resource.js',
		'lib/resources/connections_resource.js',
		'lib/resources/structures_resource.js',
		'lib/widgets/widget.js',
		'lib/widgets/start_form_widget.js',
		'lib/widgets/find_paths_form_widget.js',
		'lib/widgets/find_trees_form_widget.js',
		'lib/widgets/graph_controls_widget.js',
		'lib/widgets/node_info_widget.js',
		'lib/widgets/graph_widget.js',
		'lib/widgets/autocompleter_widget.js',
		'lib/widgets/infobar_widget.js',
	)) ?>
	<? includeTemplates() ?>
	<script>
		// light version
		this.DEBUG = <?= Config::get('env') === 'dev' ? 'true' : 'false' ?>;
		// heavy version (highly verbose)
		this.DEBUG2 = false;
		if (this.browser_compatible) {
			app.start({
				root: '<?= Config::get('frontend_base_uri') ?>/'
			});
		}
	</script>
</body>
</html>
