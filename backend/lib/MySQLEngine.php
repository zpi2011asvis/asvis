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
 	 * @var resource a MySQL link identifier
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
		
		$ret = array();		
		while ( ($as = mysql_fetch_assoc($result)) ) {
			$ret[] = $as;
		}
		
		return $ret;
	}
	
	public function nodesMeta($nodes) {
		$ret = array();
		foreach ($nodes as $num) {
			$query = 'SELECT ASNetwork as network, ASNetmask as netmask FROM aspool WHERE asnum = '.$num;
			$result = mysql_query($query, $this->_connection);

			$pools = array();
			while ( ($pool = mysql_fetch_assoc($result)) ) {
				$pools[] = $pool;
			}
			
			$ret[$num]['pools'] = $pools;
		}
		
		return $ret;
	}
	
	public function structureGraph($nodeNum, $depth) {
		$str = $this->mapNode($nodeNum, 1, $depth);
		$str = $this->removeOverhead($str);
		return array(
			'structure' => $str
		);
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

// 		if($currentDepth < $depth) {
			$structure[$nodeNum] = $this->getOutgoingConnections($nodeNum);
			
			foreach ($structure[$nodeNum]['up'] as $node) {
				$structure = $this->mapNode($node, $currentDepth+1, $depth, $structure);
			}
			
			foreach ($structure[$nodeNum]['down'] as $node) {
				$structure = $this->mapNode($node, $currentDepth+1, $depth, $structure);
			}
// 		}

		
				
		return $structure;
	}
	
	private function removeOverhead($structure) {
		$result = array();
		
		foreach ($structure as $num => $node) {
			
			$up = $node['up'];
			
			$result = $this->initNode($num, $result);
			
			foreach($up as $index => $linkedNum) {
				if(isset($structure[$linkedNum])) {
					$result[$num]['up'][] = $linkedNum;
					$result[$num]['count']++;
				}
			}
			
			$down = $node['down'];
				
			foreach($down as $index => $linkedNum) {
				if(isset($structure[$linkedNum])) {
					$result[$num]['down'][] = $linkedNum;
					$result[$num]['count']++;
				}
			}
			
		}
		
		return $result;
	}
	
	private function initNode($nodeNum, $structure) {
		$structure[$nodeNum] = array(
			'up'	=> array(),
			'down'	=> array(),
			'count' => 0,
		);
		
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
			$ret['up'][] = (int)$as['ASNumUp'];
			$ret['count']++;
		}
		
		$query = 'SELECT * FROM asdown WHERE asnum = '.$nodeNum.' AND asnumdown <> -1';
		$result = mysql_query($query, $this->_connection);
		
		if (!$result) {
			echo mysql_error($this->_connection);
		}
		
		while ( ($as = mysql_fetch_assoc($result)) ) {
			$ret['down'][] = (int)$as['ASNumDown'];
			$ret['count']++;
		}
		
		return $ret;
	}
	
}










