<pre>
<?php

require_once '../../config.php';
require_once '../../backend/vendor/SplClassLoader.php';
require_once '../../backend/vendor/orient-php-raw/OrientDB/OrientDB.php';

$classLoader = new SplClassLoader('Congow', '../../backend/vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;

function insertASNode($db, $clusterID, $num, $name) {
	// nie działa z użyciem OrientDBRecord (moze to i lepiej)
// 	$record = new OrientDBRecord();
// 	$record->data->num = $num;
// 	$record->data->name = $name;
// 	$record->data->in = '';
// 	$record->data->out = '';
	
// 	// Ustawienie ASNode powoduje zwieche na $db->recordCreate($clusterID, $record);
// // 	$record->className = 'ASNode';

	echo 'Creating ASNode in cluster ' . $clusterID . '... ';
	
	try {
		$result = $db->command(OrientDB::COMMAND_QUERY, 'INSERT INTO ASNode (num, name) VALUES ('.$num.',\''.$name.'\');');
	}
	catch (OrientDBException $e) {
		echo $e->getMessage() . PHP_EOL;
	}
	
	$recordPosition = $result->__get('recordPos');

	echo 'Created record: ' . $result . '. Record position: '.$recordPosition . PHP_EOL;
	
	return $recordPosition;
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

// var_dump($ASNodeClusterID);
// var_dump($ASConnClusterID);

// DELETE ALL RECORDS
$result = $db->command(OrientDB::COMMAND_QUERY, 'DELETE FROM ASNode');
echo 'DELETED '.$result.' ASNodes'. PHP_EOL;;
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

$rids = array();

$ases = mysql_query('SELECT asnum, asname FROM ases ORDER BY asnum');
echo PHP_EOL . 'MySQL query finished.'. PHP_EOL;

while ( ($as = mysql_fetch_assoc($ases)) ) {
	$asnum  = $as['asnum'];
	$asname = $as['asname'];

	$rid = insertASNode($db, $ASNodeClusterID, $asnum, $asname);
	$rids[$asnum] = $rid;
// 	echo $rid."\n";
}

?>
</pre>








