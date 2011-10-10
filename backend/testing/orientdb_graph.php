<pre>

<?php

require_once '../vendor/SplClassLoader.php';

$classLoader = new SplClassLoader('Congow', '../vendor/orient-php/src/');
$classLoader->register();

use Congow\Orient\Graph;
use Congow\Orient\Graph\Vertex;
use Congow\Orient\Algorithm;
use Congow\Orient\Foundation\Binding;
use Congow\Orient\Http\Client\Curl;

$driver   = new Congow\Orient\Http\Client\Curl();
$orient   = new Congow\Orient\Foundation\Binding($driver, '127.0.0.1', '2480', 'reader', 'reader', 'tinkerpop');
$response = $orient->query("SELECT FROM OGraphVertex");
$output   = json_decode($response->getBody());

var_dump($output);



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