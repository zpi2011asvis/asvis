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
	*/
	public function structureGraph($nodeNum, $depth) {		
		$result = $this->_orient->query('SELECT FROM ASNode WHERE num = '.$nodeNum, null, -1, '*:'.$depth);
		$result = json_decode($result->getBody());
		$result = $result->result;
		
		$objectList = $this->mapObjects($result[0]);
		
		$connectionList = $this->mapConnections($result[0],$objectList);
		
		H::pre($connectionList);
		die;
		return $connectionList;
	}
	
	public function structureTree($nodeNum, $depth) {
		
	}
	
	private function mapConnections($object, $objectList, $result = array()) {
		
		
		if($object && is_object($object)) {
			$atRID = '@rid';
			$atClass = '@class';
			
			echo 'mapConnections for : ' . $object->$atRID . '('.$object->$atClass.')<br/>';			
			H::pre($object);
			
			if(isset($result[$object->num])) {
				return $result;
			}
			
// 			H::pre($object);
			
			if($object->$atClass === 'ASNode' && isset($object->out)) {
				$out = $object->out;
// 				H::pre($out);
				
				if(is_array($out)) {
					foreach($out as $conn) {
// 						H::pre($conn);
						if(is_string($conn)) {
							$conn = $objectList[$conn];
						}
						
						$linked = $this->mapConnection($conn,$objectList);
						if($conn->up === true) {
							$result[$object->num]['up'][] = $linked->num;
						} else {								
							$result[$object->num]['down'][] = $linked->num;
						}
						
						$result = $this->mapConnections($linked, $objectList, $result);
						
					}
				}
			}
			
			if($object->$atClass === 'ASNode' && isset($object->in)) {
				$in = $object->in;
// 				H::pre($in);
				
				if(is_array($in)) {
					foreach($in as $conn) {
// 						H::pre($conn);
						if(is_string($conn)) {
							$conn = $objectList[$conn];
						}
						
						$linked = $this->mapConnection($conn,$objectList);
						
						$result = $this->mapConnections($linked, $objectList, $result);
						
					}
				}
			}
		}
		
		return $result;
	}
	
	private function mapConnection($asconn, $objectList) {
		if(is_string($asconn)) {
			$asconn = $objectList[$asconn];
		}
		
		if(is_object($asconn)) {
			if(isset($asconn->out)) {				
				$out = $asconn->out;
				
				if(is_object($out)) {
					return $out;
				} elseif(is_string($out)) {
					return $objectList[$out];
				}
			}
		} 
		
		return null;
	}
	
	private function mapObjects($object, $result = array()) {
		if($object && is_object($object)) {
			$atRID = '@rid';
			$atClass = '@class';
			
			$rid = $object->$atRID;
			$result[$rid] = $object;
				
			if(isset($object->in)) {
				$in = $object->in;
				if(is_array($in)) {
					foreach ($in as $obj) {
						$result = $this->mapObjects($obj, $result);
					}
				} else {
					$result = $this->mapObjects($in, $result);
				}
			}
				
			if(isset($object->out)) {
				$out = $object->out;
				if(is_array($out)) {
					foreach ($out as $obj) {
						$result = $this->mapObjects($obj, $result);
					}
				} else {
					$result = $this->mapObjects($out, $result);
				}
			}
				
		}
		
		return $result;
	}
	
}










