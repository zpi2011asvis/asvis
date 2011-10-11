<pre>

<?php

// Report all PHP errors (see changelog)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../backend/vendor/SplClassLoader.php';

$classLoader = new SplClassLoader('Congow', '../../backend/vendor/orient-php/src/');
$classLoader->register();

use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Query as Query;

function insertVertex($orient, $num) {
	return $orient->command("INSERT INTO Vertex (num, name) VALUES ({$num}, 'AS{$num}')");
}

$orient = new Binding(new Curl(), '127.0.0.1', '2480', 'admin', 'admin', 'test2');
const VERTICES = 1000;

var_dump($orient->query("SELECT FROM Vertex"));

$query = new Query();
echo 'DELETE all: ' . $orient->command($query->delete('Vertex')->getRaw())->getBody() ."\n";

for ($i = 0; $i < VERTICES; ++$i) {
	insertVertex($orient, $i);
}

var_dump($orient->query("SELECT FROM Vertex"));

?>

</pre>
