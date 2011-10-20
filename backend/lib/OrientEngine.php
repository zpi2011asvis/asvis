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

// 		H::pre($result);
		
		$rid2num = $this->mapRIDs($result[0]);
		
		return $rid2num;
	}
	
	public function structureTree($nodeNum, $depth) {
		
	}
	
}
