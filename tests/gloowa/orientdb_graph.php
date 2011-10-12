<pre>

<?php

// Report all PHP errors (see changelog)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once '../../backend/vendor/SplClassLoader.php';

$classLoader = new SplClassLoader('Congow', '../../backend/vendor/orient-php/src');
$classLoader->register();

use Congow\Orient\Graph as Graph;
use Congow\Orient\Graph\Vertex as Vertex;
use Congow\Orient\Algorithm as Algorithm;
use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Algorithm\Dijkstra as Dijkstra;

$driver   = new Curl();
$orient   = new Binding($driver, '127.0.0.1', '2480', 'admin', 'admin', 'tinkerpop');

// $response = $orient->query("SELECT FROM OUser");
// $output   = json_decode($response->getBody());
// var_dump($output);

$graph = new Graph();

$rootNode = new Vertex('0');

$nodes = array();
$nodes[0] = $rootNode;
$graph->add($nodes[0]);

for($i=1; $i<10; $i++) {
	$nodes[$i] = new Vertex('.'.$i);
	$nodes[$i-1]->connect($nodes[$i]);

	$graph->add($nodes[$i]);
}

// var_dump($graph);

$dijkstra = new Dijkstra($graph);
$dijkstra->setStartingVertex($rootNode);
$dijkstra->setEndingVertex($nodes[5]);
var_dump($dijkstra->getLiteralShortestPath());

$orient->postDocument($graph);

/* JAVA :

OGraphDatabase database = new OGraphDatabase("local:C:/temp/graph/graph");
database.open("admin", "admin");

ODocument rootNode = database.createVertex().field("id", 0);
ODocument currentNode = rootNode;

for (int i = 1; i < 1000; ++i) {
  ODocument newNode = database.createVertex().field("id", i);
  database.createEdge( currentNode, newNode);
  currentNode = newNode;
}
database.setRoot("graph", rootNode);

database.close();
 */




?>

</pre>
