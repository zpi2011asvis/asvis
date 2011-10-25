<?php

namespace asvis\lib;

require_once 'Engine.php';
require_once 'OrientObjectMapper.php';
require_once 'OrientConnectionsMapper.php';
require_once 'H.php';
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../vendor/SplClassLoader.php';

$classLoader = new \SplClassLoader('Congow', __DIR__.'/../vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;
use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Query as Query;
use asvis\lib\OrientConnectionsMapper as OrientConnectionsMapper;
use asvis\lib\OrientObjectMapper as OrientObjectMapper;
use asvis\lib\Engine as Engine;
use asvis\lib\H as H;

class OrientEngine implements Engine {
	
	/**
	* @var Binding
	*/
	private $_orient;

	/**
	 * @var Curl
	 */
	private $_client;
	
	public function __construct() {
		$this->_client   = new Curl();
		$this->_orient   = new Binding($this->_client, '127.0.0.1', '2480', 'admin', 'admin', 'asvis');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::nodesFind()
	 */
	public function nodesFind($num) {
		$result = $this->_orient->query('SELECT FROM ASNode WHERE num_as_string LIKE "'.$num.'%"')->getBody();
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
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::nodesMeta()
	 */
	public function nodesMeta($nodes) {

	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::structureGraph()
	 */
	public function structureGraph($nodeNum, $depth) {	

		/*
		 * Wartości trzeba dopasować,
		 * dla 2 jest 6 bo potrzebne sa jeszcze
		 * ASConny wychodzące z ASNodów - liści
		 */
		switch ($depth) {
			case 1: $depth = 2; break;
			case 2: $depth = 6; break;
			case 3: $depth = 8; break;
			case 4: $depth = 10; break; // ?
			default: break;
		}
		
		$json = $this->_orient->query('SELECT FROM ASNode WHERE num = '.$nodeNum, null, -1, '*:'.$depth.'%20pools:0');
		$result = json_decode($json->getBody());
		$result = $result->result;
		
		
		H::pre($result);
		die;
		
		$this->_asNodes = array();
		$this->_asConns = array();
		$this->structure = array();
		
		if ( !isset($result[0]) ) {
			return array();
		}
		
		$objectMapper = new OrientObjectMapper($result[0]);
		
		$asNodes = $objectMapper->getNodes();
		$asConns = $objectMapper->getConns();
		
		$connectionsMapper = new OrientConnectionsMapper($asNodes, $asConns);
		$structure = $connectionsMapper->getConnectionsMap();
		
		return array(
			'structure' => $structure,
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::structureTree()
	 */
	public function structureTree($nodeNum, $depth) {
		
	}
	
}










