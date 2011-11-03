<?php

require_once '../../backend/vendor/SplClassLoader.php';
require_once '../../backend/lib/asys/Node.php';
use asvis\lib\asys\Node as Node;

$node = new Node('#5:1', 1, 'AS1', array(), array(), 0, 0);

$json = json_encode($node);

$node->rid = '34';

echo '<pre>' . $node->rid . '<pre>';

echo '<pre>' . var_dump($json) . '<pre>';