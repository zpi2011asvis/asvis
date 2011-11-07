<?php

namespace asvis\lib\orient;

require_once __DIR__.'/../Engine.php';
require_once __DIR__.'/../GraphAlgorithms.php';
require_once __DIR__.'/ObjectsMapper.php';
require_once __DIR__.'/ConnectionsMapper.php';
require_once __DIR__.'/../H.php';
require_once __DIR__.'/../../../config.php';
require_once __DIR__.'/../../vendor/SplClassLoader.php';

$classLoader = new \SplClassLoader('Congow', __DIR__.'/../../vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;
use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Query as Query;
use asvis\lib\orient\ConnectionsMapper as ConnectionsMapper;
use asvis\lib\orient\ObjectsMapper as ObjectsMapper;
use asvis\lib\Engine as Engine;
use asvis\lib\GraphAlgorithms as GraphAlgorithms;
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
		
		$fp = $depth;
		
		$query = "SELECT FROM ASNode WHERE num = {$nodeNum}";
		$fetchplan = "*:{$fp} ASNode.pools:0 ASNode.in:0";
		
		$json = $this->_orient->query($query, null, 1, $fetchplan);
		$result = json_decode($json->getBody())->result;

		if (!count($result)) {
			return null;
		}
		
		$objectMapper = new ObjectsMapper($result[0]);		
		$graph = $objectMapper->parse();

// 		echo $graph->toJSON();
		
		return $graph->toJSON();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::structureTree()
	 */
	public function structureTree($nodeNum, $height) {
		
		//przykładowe dane do testów
		$structure = array(
			1 => array(
				'up' => array(2, 3, 4, 5, 6),
				'down' => array(2, 3, 5),
				'distance' => 0,
				'count' => 8
			),
			2 => array(
				'up' => array(1, 7, 8),
				'down' => array(),
				'distance' => 1,
				'count' => 3
			),
			3 => array(
				'up' => array(9),
				'down' => array(),
				'distance' => 1,
				'count' => 1
			),
			4 => array(
				'up' => array(3, 10),
				'down' => array(),
				'distance' => 1,
				'count' => 2
			),
			5 => array(
				'up' => array(11, 12),
				'down' => array(),
				'distance' => 1,
				'count' => 2
			),
			6 => array(
				'up' => array(),
				'down' => array(),
				'distance' => 1,
				'count' => 0
			),
			7 => array(
				'up' => array(),
				'down' => array(),
				'distance' => 2,
				'count' => 0
			),
			8 => array(
				'up' => array(),
				'down' => array(),
				'distance' => 2,
				'count' => 0
			),
			9 => array(
				'up' => array(),
				'down' => array(),
				'distance' => 2,
				'count' => 0
			),
			10 => array(
				'up' => array(),
				'down' => array(),
				'distance' => 2,
				'count' => 0
			),
			11 => array(
				'up' => array(),
				'down' => array(13),
				'distance' => 2,
				'count' => 1
			),
			12 => array(
				'up' => array(14, 15),
				'down' => array(),
				'distance' => 2,
				'count' => 2
			),
			13 => array(
				'up' => array(),
				'down' => array(),
				'distance' => 3,
				'count' => 0
			),
			14 => array(
				'up' => array(),
				'down' => array(15),
				'distance' => 3,
				'count' => 0
			),
			15 => array(
				'up' => array(),
				'down' => array(),
				'distance' => 3,
				'count' => 0
			),
		);
		
		$graphAlgorithms = new GraphAlgorithms($structure);
		
		return $graphAlgorithms->getTree($height, 'up');
	}
	
}










