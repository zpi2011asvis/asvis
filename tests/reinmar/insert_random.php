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

function insertASNode($orient, $num) {
	return $orient->command("INSERT INTO ASNode (num, name) VALUES ({$num}, 'AS{$num}')");
}
function insertASConn($orient, $vertices, $v1_num, $v2_num, $up) {
	$rid1 = $vertices[$v1_num];
	$rid2 = $vertices[$v2_num];
	$conn = $orient->command("INSERT INTO ASConn (in, out, up) VALUES ({$rid1}, ${rid2}, {$up})")->getBody();
	$conn_rid = explode('{', $conn);
	$conn_rid = substr($conn_rid[0], 6);
	$orient->command("UPDATE ASNode ADD in = {$conn_rid} WHERE @rid = {$rid1}");
	$orient->command("UPDATE ASNode ADD out = {$conn_rid} WHERE @rid = {$rid2}");
}

//---------------------------
$orient = new Binding(new Curl(), '127.0.0.1', '2480', 'admin', 'admin', 'asvis');
const VERTICES = 10;
const ASConnS = 20;
$vertices = array();

//---------------------------
$query = new Query();
echo 'DELETE all: ' .
	$orient->command($query->delete('ASNode')->getRaw())->getBody() . ', ' .
	$orient->command($query->delete('ASConn')->getRaw())->getBody() . "\n";

//---------------------------
echo 'INSERT ' . VERTICES . ' ASNodes' . "\n";
for ($i = 0; $i < VERTICES; ++$i) {
	$str = insertASNode($orient, $i)->getBody();
	$rid = explode('{', $str);
	$rid = substr($rid[0], 6);
	$vertices[$i] = $rid;
}

//---------------------------
echo 'INSERT ' . ASConnS . ' ASConns' . "\n";
for ($i = 0; $i < ASConnS; ++$i) {
	insertASConn(
		$orient, $vertices,
		rand(0, VERTICES - 1),
		rand(0, VERTICES - 1),
		rand(0, 10) > 5 ? 'true' : 'false'
	);
}


//---------------------------
//var_dump($orient->query("SELECT FROM ASNode"));

?>

</pre>
