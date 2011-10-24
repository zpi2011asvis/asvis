<?php

namespace asvis\lib;

require_once 'Engine.php';
require_once 'H.php';
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../vendor/SplClassLoader.php';

$classLoader = new \SplClassLoader('Congow', __DIR__.'/../vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;
use asvis\lib\Engine as Engine;
use asvis\lib\H as H;

class MySQLEngine implements Engine {
	
	/**
 	 * @return resource a MySQL link identifier
	 */
	private $_connection;
	
	public function __construct() {		
		$dbhost = Config::get('mysql_db_host');
		$dbuser = Config::get('mysql_db_user');
		$dbpass = Config::get('mysql_db_pass');
				
		$dbname = Config::get('mysql_db_name');
				
		$this->_connection = mysql_connect($dbhost, $dbuser, $dbpass);
		mysql_select_db($dbname, $this->_connection);
	}
	
	public function nodesFind($num) {
		$query = 'SELECT asnum, asname FROM ases WHERE asnum LIKE "'.$num.'%"';
		
		$result = mysql_query($query, $this->_connection);
		
		if (!$result) {
			echo mysql_error($this->_connection);
		}
		
		while ( ($as = mysql_fetch_assoc($result)) ) {
			H::pre($as);
		}
		
		
		die;
	}
	
	public function nodesMeta($nodes) {
		
	}
	
	public function structureGraph($nodeNum, $depth) {
		$str = $this->mapNode($nodeNum, 1, $depth);
		
// 		H::pre($str);
// 		die;
		
		return $str;
	}
	
	public function structureTree($nodeNum, $depth) {
		
	}
	
	private function mapNode($nodeNum, $currentDepth, $depth, $structure = array()) {
		if($currentDepth > $depth) {
			return $structure;
		}
		
		if(isset($structure[$nodeNum])) {
			return $structure;
		}

		if($currentDepth < $depth) {
			$structure[$nodeNum] = $this->getOutgoingConnections($nodeNum);
		} else {
			$structure[$nodeNum] = array(
				'up'	=> array(),
				'down'	=> array(),
				'count'	=> 0,
			);
		}

		foreach ($structure[$nodeNum]['up'] as $node) {
			$structure = $this->mapNode($node, $currentDepth+1, $depth, $structure);
		}
		
		foreach ($structure[$nodeNum]['down'] as $node) {
			$structure = $this->mapNode($node, $currentDepth+1, $depth, $structure);
		}
				
		return $structure;
	}
	
	private function getOutgoingConnections($nodeNum) {
		$query = 'SELECT * FROM asup WHERE asnum = '.$nodeNum.' AND asnumup <> -1';
		$result = mysql_query($query, $this->_connection);
		
		$ret['up']		= array();
		$ret['down']	= array();
		$ret['count']	= 0;
		
		if (!$result) {
			echo mysql_error($this->_connection);
		}
		
		while ( ($as = mysql_fetch_assoc($result)) ) {
			$ret['up'][] = $as['ASNumUp'];
			$ret['count']++;
		}
		
		$query = 'SELECT * FROM asdown WHERE asnum = '.$nodeNum.' AND asnumdown <> -1';
		$result = mysql_query($query, $this->_connection);
		
		if (!$result) {
			echo mysql_error($this->_connection);
		}
		
		while ( ($as = mysql_fetch_assoc($result)) ) {
			$ret['down'][] = $as['ASNumDown'];
			$ret['count']++;
		}
		
		return $ret;
	}
	
}










