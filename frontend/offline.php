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
	<script>console.log('*********************** RELOAD ****************************');</script>
	<meta charset="utf-8">
	<title>ASvis</title>
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/init.css">
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/components.css">
	<link rel="stylesheet" href="<?= Config::get('frontend_base_uri') ?>/css/layout.css">
</head>
<body id="container">
	<header id="top">
		<h1><a href="/"><strong>AS</strong>vis</a> !OFFLINE! </h1>
	</header>
	<script>alert('Aplikacja ASvis nie jest dostepna - trwa aktualizacja bazy danych.');</script>
</body>
</html>
