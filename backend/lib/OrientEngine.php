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
	* @var Congow\Orient\Foundation\Binding
	*/
	private $_orient;

	/**
	 * @var Congow\Orient\Http\Client\Curl
	 */
	private $_client;
	
	public function __construct() {
		$this->_client   = new Curl();
		$this->_orient   = new Binding(
			$this->_client,
			Config::get('orient_db_host'),
			'2480',
			Config::get('orient_db_user'),
			Config::get('orient_db_pass'),
			Config::get('orient_db_name')
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::nodesFind()
	 */
	public function nodesFind($num) {
		$result = $this->_orient->query("SELECT FROM ASNode WHERE num_as_string LIKE '{$num}%'")->getBody();
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
		 * this fetch plans will return too much asnodes
		 * so object mapper should also have in mind given depth
		 * and remove redundant nodes
		 */		 
		switch ($depth) {
			// root only
			case 1:
				$fp = 1;
				break;
			// root, children and conns between them (also between children)
			case 2:
				$fp = 8;
				break;
			/*
			// this results I got for test db (tests/reinmar/test_db.sql)
			case 3:
				$depth = 16;
				break;
			case 4:
				$depth = 20;
				break;
			*/
			default: 
				$fp = ($depth + 1) * 4;
				break;
		}
		
		$query = "SELECT FROM ASNode WHERE num = {$nodeNum}";
		$fetchplan = "*:{$fp} ASNode.in:0 ASNode.pools:0";
		
		$json = $this->_orient->query($query, null, 1, $fetchplan);
		$result = json_decode($json->getBody())->result;
		
 		//H::pre($result);
		
		if (!count($result)) {
			return null;
		}
		$objectMapper = new OrientObjectMapper($result[0], $depth);
		
		$asNodes = $objectMapper->getNodes();
		$asConns = $objectMapper->getConns();

		//echo count($asNodes).PHP_EOL;
		//echo count($asConns).PHP_EOL;
		//H::pre($asNodes);
		//H::pre($asConns);
		
		$connectionsMapper = new OrientConnectionsMapper($asNodes, $asConns);
		$connectionsMapper->calculateDistances($nodeNum, $depth);
		$structure = $connectionsMapper->getConnectionsMap();

		//H::pre($structure);
		
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










