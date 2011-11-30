<?php

// config
use asvis\Config as Config;
require_once __DIR__ . '/../config.php';

if (Config::get('env') === 'dev') {
	// Report all PHP errors (see changelog)
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
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
	</header>
	<p style="margin: 100px 0 20px; text-align:center; font-size: 50px;">OFFLINE</p>
	<p style="text-align:center; font-size: 20px;">Aplikacja ASvis nie jest dostepna &ndash; trwa aktualizacja bazy danych.</p>
</body>
</html>
