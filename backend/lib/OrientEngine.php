<?php

namespace asvis\lib;

require_once 'Engine.php';
require_once 'H.php';
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../vendor/SplClassLoader.php';

$classLoader = new \SplClassLoader('Congow', __DIR__.'/../vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;
use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Query as Query;
use asvis\lib\Engine as Engine;
use asvis\lib\H as H;

class OrientEngine implements Engine {
	
	/**
	* @var Binding
	*/
	private $_orient;
	
	private $_client;
	
	private $asNodes;
	private $asConns;
	
	private $structure;
	
	public function __construct() {
		$this->_client   = new Curl();
		$this->_orient   = new Binding($this->_client, '127.0.0.1', '2480', 'admin', 'admin', 'asvis');
	}
	
	/*
	 *	{
	 *		"34567": {"name":"AS34567"}
	 *		"34579": {"name":"AS34579"}
	 *		"345": {"name":"AS345"}
	 *	}
	 */
	public function nodesFind($num) {
		$result = $this->_orient->query('SELECT FROM ASNode WHERE num.asString() LIKE "'.$num.'%"')->getBody();
		$result = json_decode($result);
		$result = $result->result;
		
		$nodes = array();
		
		foreach ($result as $asNode) {			
			$nodes[$asNode->num] = array(
				'name' => $asNode->name
			);
		}
		
		return $nodes;
	}
	
	public function nodesMeta($nodes) {

	}
	
	/*
		{
			"345": {"connections_up":[3245,2345,2356], "connections_down":[34765,1235,5325]},
			"4234": {"connections_up":[3245,2345,2356], "connections_down":[]}
		}
		
		
		DEPTH :
		1 = origin
		2 = origin + conns
		3 = origin + conns
		4 = 2nd lvl nodes
		5 = 2nd lvl nodes
		6 = 2nd lvl nodes + conns
		7 = 2nd lvl nodes + conns
		8 = 3rd lvl nodes + conns
	*/
	public function structureGraph($nodeNum, $depth) {	

		switch ($depth) {
			case 1: $depth = 1; break;
			case 2: $depth = 4; break;
			case 3: $depth = 8; break;
			case 4: $depth = 10; break; // ?
			case 5: $depth = 12; break; // ?
			case 6: $depth = 14; break; // ?
			default : break;
		}
		
		$json = $this->_orient->query('SELECT FROM ASNode WHERE num = '.$nodeNum, null, -1, '*:'.$depth.'%20pools:0');
		$result = json_decode($json->getBody());
		$result = $result->result;
		
		$this->asNodes = array();
		$this->asConns = array();
		$this->structure = array();
		
		if ( !isset($result[0]) ) {
			return array();
		}
		$this->mapObject($result[0]);
		
		$this->debug_checkFixBrokenConns(false);
// 		$this->debug_clearINOUT();
			
// 		H::pre($this->asNodes);
// 		H::pre($this->asConns);		
// 		die;
		
		$this->mapConnectionsGraph();
		$this->countConnections();
		
		return $this->structure;
	}
	
	public function structureTree($nodeNum, $depth) {
		
	}
	
	private function mapObject($object) {
		if(!is_object($object)) {
			return;
		}
		
		$atClass = '@class';
		
		if($object->$atClass === 'ASNode') {
			$this->mapNode($object);
		}
		
		if($object->$atClass === 'ASConn') {
			$this->mapConn($object);
		}
	}
	
	private function mapNode($asnode) {
		if(!is_object($asnode)) {
			return;
		}
		
		$atRID = '@rid';
				
		$this->asNodes[$asnode->$atRID] = $asnode;
		
		if(isset($asnode->in)) {	
			$in = $asnode->in;
			
			foreach ($in as $object) {
				$this->mapObject($object);
			}				
		}
		
		if(isset($asnode->out)) {	
			$out = $asnode->out;
			
			foreach ($out as $object) {
				$this->mapObject($object);
			}				
		}
	}
	
	private function mapConn($asconn) {
		if(!is_object($asconn)) {
			return;
		}
		
		$atRID = '@rid';
		
		$this->asConns[$asconn->$atRID] = $asconn;
		
		if(isset($asconn->in)) {
			$this->mapObject($asconn->in);
		}
		
		if(isset($asconn->out)) {
			$this->mapObject($asconn->out);
		}
		
	}
	
	private function mapConnectionsGraph() {
		foreach ($this->asNodes as $node) {
			$this->initStructureRecord($node->num);
		}
		
		foreach ($this->asConns as $conn) {
			$nodeFrom	= $this->getNodeFrom($conn);			
			$nodeTo		= $this->getNodeTo($conn);
			
			$dir = $conn->up ? 'up' : 'down';
			
			$this->structure[$nodeFrom->num][$dir][] = $nodeTo->num;			
		}		
	}
	
	private function getNodeFrom($asconn) {
		$nodeFrom = null;
			
		if(is_object($asconn->in)) {
			$nodeFrom = $asconn->in;
		}
			
		if(is_string($asconn->in)) {
			$nodeFrom = $this->asNodes[$asconn->in];
		}
		
		return $nodeFrom;
	}
	
	private function getNodeTo($asconn) {
		$nodeTo = null;
				
		if(is_object($asconn->out)) {
			$nodeTo = $asconn->out;
		}
		
		if(is_string($asconn->out)) {
			$nodeTo = $this->asNodes[$asconn->out];
		}
	
		return $nodeTo;
	}
	
	private function initStructureRecord($nodeNum) {
		$this->structure[$nodeNum] = array(
			'up' => array(),
			'down' => array(),
			'count' => 0,
		);
	}
	
	private function countConnections() {
		foreach ($this->structure as $num => $node) {
			$count = count($node['up']) + count($node['down']);
			$this->structure[$num]['count'] = $count;
		}
	}
	
	/*
	 * Niektóre fetchplany zwracją ASConny bez pól in/out (WTF?!)
	 * to się chyba dzieje w sytuacji:
	 * detpth-1    depth  
	 * ASNode      ASConn <- depth ograniczył wczytanie ASNode'a więc ASConn ma pusty link.
	 */
	private function debug_checkFixBrokenConns($verbose = false) {
		$atRID = '@rid';
		$brokenConns = array();
		
		foreach ($this->asConns as $conn) {
			if( !(isset($conn->in) && isset($conn->out)) ) {
				$brokenConns[] = $conn;
			}
		}
		
		if($verbose) {
			echo 'Found '.count($brokenConns).' broken connections (in '.count($this->asConns).' total)';
		}
		
		foreach ($brokenConns as $conn) {
			unset($this->asConns[$conn->$atRID]);
		}
		
	}
	
	private function debug_clearINOUT() {
		foreach ($this->asNodes as $node) {
			unset($node->in);
			unset($node->out);
		}
		
// 		foreach ($this->asConns as $conn) {
// 			unset($conn->in);
// 			unset($conn->out);
// 		}
	}
	
}










