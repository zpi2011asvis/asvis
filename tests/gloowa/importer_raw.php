<?php

require_once '../../config.php';
require_once '../../backend/vendor/SplClassLoader.php';
require_once '../../backend/vendor/orient-php-raw/OrientDB/OrientDB.php';

$classLoader = new SplClassLoader('Congow', '../../backend/vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;

function insertASNode($db, $clusterID, $num, $name) {
	try {
		$result = $db->command(OrientDB::COMMAND_QUERY, 'INSERT INTO ASNode (num, name) VALUES ('.$num.',\''.$name.'\');');
	} catch (OrientDBException $e) {
		echo $e->getMessage() . PHP_EOL;
	}

	$recordPosition = $result->__get('recordPos');
	// 	echo 'Created record: ' . $result . '. Record position: '.$recordPosition . PHP_EOL;
	return $recordPosition;
}

function insertASConn($db, $clusterID, $from, $to, $isUp) {
	try {
		$result = $db->command(OrientDB::COMMAND_QUERY, 'INSERT INTO ASConn (in, out, up) VALUES ('.$from.','.$to.','.$isUp.')');
	} catch (OrientDBException $e) {
		echo $e->getMessage() . PHP_EOL;
	}

	$recordPosition = $result->__get('recordPos');
// 	echo 'Created record: ' . $result . '. Record position: '.$recordPosition . PHP_EOL;
	return $recordPosition;
}

function updateASNode($db, $clusterID, $ASNodeRID, $fromList, $toList) {

	try {
		$result = $db->command(OrientDB::COMMAND_QUERY,
		'UPDATE ASNode SET in = '.$fromList.', out = '.$toList.' WHERE @rid = '.$ASNodeRID);
	} catch (OrientDBException $e) {
		echo $e->getMessage() . PHP_EOL;
	}
	
}

$dbName = 'asvis';

echo 'Connecting to server...' . PHP_EOL;
try {
	$db = new OrientDB('localhost', 2424);
}
catch (Exception $e) {
	die('Failed to connect: ' . $e->getMessage());
}

$ASNodeClusterID = null;
$ASConnClusterID = null;
echo 'Opening DB...' . PHP_EOL;
try {
	$clusters = $db->DBOpen($dbName, 'admin', 'admin');

	foreach ($clusters['clusters'] as $cluster) {
		if ($cluster->name === 'asnode') {
			$ASNodeClusterID = $cluster->id;
		}
		if ($cluster->name === 'asconn') {
			$ASConnClusterID = $cluster->id;
		}
	}
}
catch (OrientDBException $e) {
	echo $e->getMessage() . PHP_EOL;
}

// DELETE ALL RECORDS
$result = $db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASNode');
echo PHP_EOL.'DELETED '.$result.' ASNodes'. PHP_EOL;;
$result = $db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASConn');
echo 'DELETED '.$result.' ASConns'. PHP_EOL;;

echo PHP_EOL . 'OrientDB now ready for import.'. PHP_EOL;

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

$asrids = array();

$ases = mysql_query('SELECT asnum, asname FROM ases ORDER BY asnum');
echo PHP_EOL . 'MySQL query finished.'. PHP_EOL;

echo PHP_EOL . 'Beginning OrientDB ASNode INSERT(s).'. PHP_EOL;
$timeBegin = microtime(true);

while ( ($as = mysql_fetch_assoc($ases)) ) {
	$asnum  = $as['asnum'];
	$asname = $as['asname'];

	$rid = insertASNode($db, $ASNodeClusterID, $asnum, $asname);
	$asrids[$asnum] = array(
		'rid'	=> '#'.$ASNodeClusterID.':'.$rid,
		'in'	=> array(),
		'out'	=> array(),
	);
	// 	echo $rid."\n";
}

$timeEnd = microtime(true);
echo PHP_EOL . 'ASNode INSERT(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;

echo PHP_EOL . 'Querying MySQL asup... ';

$asups = mysql_query('SELECT asnum, asnumup FROM asup WHERE asnumup <> -1');

echo 'finished' . PHP_EOL;

echo PHP_EOL . 'Querying MySQL asdown... ';

$asdowns = mysql_query('SELECT asnum, asnumdown FROM asdown WHERE asnumdown <> -1');

echo 'finished' . PHP_EOL;

echo PHP_EOL . 'Beginning OrientDB ASConn INSERT(s).'. PHP_EOL;
$timeBegin = microtime(true);

while ( ($asup = mysql_fetch_assoc($asups)) ) {
	$asnum		= $asup['asnum'];
	$asnumup	= $asup['asnumup'];
	
	if(!isset($asrids[$asnum])) {
		echo 'Aborting inserting ASConn from inexisting ASNode (no asnum = ' .$asnum. ')' . PHP_EOL;
		continue;
	}
	if(!isset($asrids[$asnumup])) {
		echo 'Aborting inserting ASConn to inexisting ASNode (no asnumup = ' .$asnumup. ')' . PHP_EOL;
		continue;
	}
	
	$from = $asrids[$asnum]['rid'];
	$to = $asrids[$asnumup]['rid'];

	$rid = insertASConn($db, $ASConnClusterID, $from, $to, 'true');
	
	$asrids[$from]['out'][] = '#'.$ASConnClusterID.':'.$rid;
	$asrids[$to]['in'][] = '#'.$ASConnClusterID.':'.$rid;
}

while ( ($asdown = mysql_fetch_assoc($asdowns)) ) {
	$asnum		= $asdown['asnum'];
	$asnumdown	= $asdown['asnumdown'];

	if(!isset($asrids[$asnum])) {
		echo 'Aborting inserting ASConn from inexisting ASNode (no asnum = ' .$asnum. ')' . PHP_EOL;
		continue;
	}
	if(!isset($asrids[$asnumdown])) {
		echo 'Aborting inserting ASConn to inexisting ASNode (no asnumdown = ' .$asnumdown. ')' . PHP_EOL;
		continue;
	}

	$from = $asrids[$asnum]['rid'];
	$to = $asrids[$asnumdown]['rid'];

	$rid = insertASConn($db, $ASConnClusterID, $from, $to, 'false');

	$asrids[$from]['out'][] = '#'.$ASConnClusterID.':'.$rid;
	$asrids[$to]['in'][] = '#'.$ASConnClusterID.':'.$rid;
}

$timeEnd = microtime(true);
echo PHP_EOL . 'ASConn INSERT(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;

echo PHP_EOL . 'Begginning ASNode UPDATE(s).' . PHP_EOL;
$timeBegin = microtime(true);

// var_dump($asrids);

die();

foreach ($asrids as $asnum => $asdata) {
// 	var_dump($asdata);
// 	var_dump($asdata['in']);
	echo PHP_EOL . '['.(implode(',', $asdata['in'])).']' . PHP_EOL;
	
	$ASNodeRID = $asdata['rid'];
	$fromList	= '['.(implode(',', $asdata['in'])).']';
	$toList		= '['.(implode(',', $asdata['out'])).']';
	
	echo 'FROM : ' . $fromList . PHP_EOL;
	echo 'TO   : ' . $toList . PHP_EOL;
	
// 	updateASNode($db, $ASNodeClusterID, $ASNodeRID, $fromList, $toList);
}

$timeEnd = microtime(true);
echo PHP_EOL . 'ASNode UPDATE(s) finished in '. ($timeEnd - $timeBegin) . 's.' . PHP_EOL;





















