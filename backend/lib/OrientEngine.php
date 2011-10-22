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
		
		$rid2num = $this->mapRIDs($result[0]);
		
		$result = $this->mapConnections($result[0], $rid2num);
		
// 		H::pre($result);
		
// 		die;
		return $result;
	}
	
	public function structureTree($nodeNum, $depth) {
		
	}
	
	private function mapConnections($object, $rid2num, $result = array()) {
// 		H::pre($object);
		
		if($object && is_object($object)) {
			$atRID = '@rid';
			$atClass = '@class';
			
			$originRID = $object->$atRID;
			
			if($object->$atClass === 'ASNode') {
				if(isset($object->out)) {
					$out = $object->out;
					$connections_out = array();
					
					if(is_array($out)) {
						foreach($out as $conn) {
							$num = $this->mapConnection($conn, $rid2num);
							$result[$rid2num[$originRID]][] = $num;
							
							$result = $this->mapConnections($conn, $rid2num, $result);
						}
					} elseif(is_string($out)) {
						$num = $rid2num[$out];
						$result[$rid2num[$originRID]][] = $num;
						
						$result = $this->mapConnections($conn, $rid2num, $result);
					}					
				}
			}
			
			return $result;
		}
	}
	
	private function mapConnection($object, $rid2num) {		
		if($object && is_object($object)) {
			$atRID = '@rid';
			$atClass = '@class';

			if($object->$atClass === 'ASConn') {
				if(isset($object->out)) {				
					if(is_string($object->out)) {
						return $rid2num[$object->out];
					} elseif(is_object($object->out)) {
						return $rid2num[$object->out->$atRID];
					}	
				}
			}
		}

		return null;
	}
	
	private function mapRIDs($object, $result = array()) {
		if($object && is_object($object)) {
			$atRID = '@rid';
			$atClass = '@class';
			
			if($object->$atClass === 'ASNode') {
				$rid = $object->$atRID;
				$num = $object->num;
				
				$result[$rid] = $num;
			}
			
			if(isset($object->in)) {
				$in = $object->in;			
				if(is_array($in)) {
					foreach ($in as $obj) {
						$result = $this->mapRIDs($obj, $result);
					}
				} else {
					$result = $this->mapRIDs($in, $result);
				}
			}
			
			if(isset($object->out)) {
				$out = $object->out;			
				if(is_array($out)) {
					foreach ($out as $obj) {
						$result = $this->mapRIDs($obj, $result);
					}
				} else {
					$result = $this->mapRIDs($out, $result);
				}
			}
			
		}	
		
		return $result;		
	}
	
}









