<?php

echo '<pre>';

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

$SELECT_ASES = 'SELECT DISTINCT a.asnum, asname FROM aspool a JOIN ases b ON a.asnum = b.asnum ORDER BY a.asnum LIMIT 100';
$SELECT_ASUP = 'SELECT asnum, asnumup FROM asup WHERE asnumup <> -1 ORDER BY asnum';
$SELECT_ASDN = 'SELECT asnum, asnumdown FROM asdown WHERE asnumdown <> -1 ORDER BY asnum ';

$rids = array();

$result = mysql_query($SELECT_ASES);
while ( ($as = mysql_fetch_assoc($result)) ) {
	$nodes[] = $as;
	$asnum = $as['asnum'];
	$rid = insertASNode($orient, $asnum, $as['asname']);
	$rids[$asnum] = $rid;
}

$resultUp = mysql_query($SELECT_ASUP);
while ( ($asup = mysql_fetch_assoc($resultUp)) ) {
	if ( isset($rids[$asup['asnum']]) && isset ($rids[$asup['asnumup']]) ) {		
		$rid1 = $rids[$asup['asnum']];
		$rid2 = $rids[$asup['asnumup']];
		
		insertASConn($orient, $rid1, $rid2, 'true');
	}
}


$resultDn = mysql_query($SELECT_ASDN);
while ( ($asdn = mysql_fetch_assoc($resultDn)) ) {
	if ( isset($rids[$asdn['asnum']]) && isset ($rids[$asdn['asnumup']]) ) {
		$rid1 = $rids[$asdn['asnum']];
		$rid2 = $rids[$asdn['asnumup']];

		insertASConn($orient, $rid1, $rid2, 'true');
	}
}

mysql_close($mysqlConnection);

echo '</pre>';











