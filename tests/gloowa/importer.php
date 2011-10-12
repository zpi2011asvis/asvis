<?php

echo getcwd()."\n";

require_once '../../config.php';
require_once '../../backend/vendor/SplClassLoader.php';

$classLoader = new SplClassLoader('Congow', '../../backend/vendor/orient-php/src/');
$classLoader->register();

use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Query as Query;

use asvis\Config as Config;

$driver   = new Curl();
$orient   = new Binding($driver, '127.0.0.1', '2480', 'admin', 'admin', 'asvis');

//---------------------------
$query = new Query();
echo 'DELETE all: ' .
$orient->command($query->delete('ASNode')->getRaw())->getBody() . ', ' .
$orient->command($query->delete('ASConn')->getRaw())->getBody() . "\n";

//---------------------------

function insertASNode($orient, $num, $name) {
	$str = $orient->command("INSERT INTO ASNode (num, name) VALUES ({$num}, '{$name}')")->getBody();
	$rid = explode('{', $str);
	$rid = substr($rid[0], 6);
	return $rid;
}
function insertASConn($orient, $rid1, $rid2, $up) {
	$conn = $orient->command("INSERT INTO ASConn (in, out, up) VALUES ({$rid1}, ${rid2}, {$up})")->getBody();
	$conn_rid = explode('{', $conn);
	$conn_rid = substr($conn_rid[0], 6);
	$orient->command("UPDATE ASNode ADD in = {$conn_rid} WHERE @rid = {$rid1}");
	$orient->command("UPDATE ASNode ADD out = {$conn_rid} WHERE @rid = {$rid2}");
}

$MYSQL_HOST = Config::get('mysql_db_host');
$MYSQL_NAME = Config::get('mysql_db_name');
$MYSQL_USER = Config::get('mysql_db_user');
$MYSQL_PASS = Config::get('mysql_db_pass');


$mysqlConnection = mysql_connect($MYSQL_HOST,$MYSQL_USER,$MYSQL_PASS);

if(!$mysqlConnection) {
	die('Nie można połączyć z bazą MySQL '.$MYSQL_HOST.' jako uzytkownik '.$MYSQL_USER );
}

$isDBSelected = mysql_select_db($MYSQL_NAME);

if(!$isDBSelected) {
	die('Nie można wybrać bazy '.$MYSQL_NAME);
}

$rids = array();

$ases = mysql_query('SELECT DISTINCT p.asnum, asname FROM aspool p JOIN ases a ON p.asnum = a.asnum ORDER BY p.asnum');
while ( ($as = mysql_fetch_assoc($ases)) ) {
	$asnum  = $as['asnum'];
	$asname = $as['asname'];
	
	$rid = insertASNode($orient, $asnum, $asname);
	$rids[$asnum] = $rid;
	echo $rid."\n";
}

echo 'ASNode insert: ' . count($rids) . "\n";

// die();


$conups = mysql_query('SELECT asnum, asnumup FROM asup WHERE asnumup <> -1 ORDER BY asnum');
while ( ($conup = mysql_fetch_assoc($conups)) ) {
	
	$rid1 = $rids[$conup['asnum']];
	$rid2 = $rids[$conup['asnumup']];
	
	echo "connect: ".$rid1." to ".$rid2."\n";
	
	insertASConn($orient, $rid1, $rid2, 'true');
	
}

$condns = mysql_query('SELECT asnum, asnumdown FROM asdown WHERE asnumup <> -1 ORDER BY asnum');
while ( ($condn = mysql_fetch_assoc($condns)) ) {

	$rid1 = $rids[$condn['asnum']];
	$rid2 = $rids[$condn['asnumdown']];

	insertASConn($orient, $rid1, $rid2, 'false');

}

mysql_close($mysqlConnection);

echo '</pre>';











