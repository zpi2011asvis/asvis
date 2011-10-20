<?php

require_once '../../config.php';
require_once '../../backend/vendor/SplClassLoader.php';

$classLoader = new SplClassLoader('Congow', '../../backend/vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;
use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Query as Query;

$driver   = new Curl();
$orient   = new Binding($driver, '127.0.0.1', '2480', 'admin', 'admin', 'asvis');

// $res = $orient->getDocument('5:7018', null, '*:4');
$res = $orient->query('SELECT FROM 5:7018', null, -1, '*:4');

$t = microtime(true);
$obj = json_decode($res->getBody());
echo (microtime(true) - $t).PHP_EOL;
echo '<pre>';
var_dump($obj);
