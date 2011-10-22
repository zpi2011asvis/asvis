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
		
		$json = $this->_orient->query('SELECT FROM ASNode WHERE num = '.$nodeNum, null, -1, '*:'.$depth);
		$result = json_decode($json->getBody());
		$result = $result->result;
// 		H::pre($result);
		
		$this->asNodes = array();
		$this->asConns = array();
		$this->structure = array();
		
		$this->mapObjects($result[0]);
// 		H::pre($this->asNodes);
// 		H::pre($this->asConns);
		
		$this->mapConnectionsGraph();
		$this->countConnections();		
		
		uasort($this->structure, array('self', 'cmpNodes'));
		
		return $this->structure;
	}
	
	public function structureTree($nodeNum, $depth) {
		
	}
	
	private function mapObjects($asnode) {
		$atRID = '@rid';
		
		if(is_object($asnode)) {
			if(!isset($this->asNodes[$asnode->$atRID])) {
				$this->asNodes[$asnode->$atRID] = $asnode;
			}
			
			if(isset($asnode->out)) {
				$connsOut = $asnode->out;
				foreach($connsOut as $conn) {
					if(is_object($conn)) {
						if(!isset($this->asConns[$conn->$atRID])) {
							$this->asConns[$conn->$atRID] = $conn;
						}
						
						if(isset($conn->out)) {
							$linkedNode = $conn->out;
							if(is_object($linkedNode)) {
								if(!isset($this->asNodes[$linkedNode->$atRID])) {
									$this->asNodes[$linkedNode->$atRID] = $linkedNode;
								}
							}
						}
					}
				}
			}
			
			
			if(isset($asnode->in)) {
				$connsIn = $asnode->in;
				foreach($connsIn as $conn) {
					if(is_object($conn)) {
						if(!isset($this->asConns[$conn->$atRID])) {
							$this->asConns[$conn->$atRID] = $conn;
						}
				
						if(isset($conn->in)) {
							$linkedNode = $conn->in;
							if(is_object($linkedNode)) {
								if(!isset($this->asNodes[$linkedNode->$atRID])) {
									$this->asNodes[$linkedNode->$atRID] = $linkedNode;
								}
							}
						}
					}
				}
			}
		}
	}
	
	private function mapConnectionsGraph() {
		foreach ($this->asConns as $asConn) {			
			$nodeFrom = null;
			$nodeTo = null;
			
			$dir = $asConn->up ? 'up' : 'down';
			
			if(!isset($asConn->in) || !isset($asConn->out)) {
				continue;
			}
			
			if( is_object($asConn->in) ) {
				$nodeFrom = $asConn->in;
			} else {
				$nodeFrom = $this->asNodes[$asConn->in];
			}
			
			if( is_object($asConn->out) ) {
				$nodeTo = $asConn->out;
			} else {				
				$nodeTo = $this->asNodes[$asConn->out];
			}
			
			if(!isset($this->structure[$nodeFrom->num])) {
				$this->structure[$nodeFrom->num] = array(
					'up'	=> array(),
					'down'	=> array(),
				);
			}
			
			$this->structure[$nodeFrom->num][$dir][] = $nodeTo->num;
			
		}
		
		//fix missing 0 connections nodes
		foreach($this->asNodes as $node) {
			if(!isset($this->structure[$node->num])) {
				$this->structure[$node->num] = array(
								'up'	=> array(),
								'down'	=> array(),
				);
			}
		}
	}
	
	private function countConnections() {
		foreach ($this->structure as $num => $node) {
			$count = count($node['up']) + count($node['down']);
			$this->structure[$num]['count'] = $count;
		}
	}
	
	private static function cmpNodes($a, $b) {
		$field = 'count';
	    if ($a[$field] == $b[$field]) {
	        return 0;
	    }
	    return ($a[$field] > $b[$field]) ? -1 : 1;
	}
	
}










