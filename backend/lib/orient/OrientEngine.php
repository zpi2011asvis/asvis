<?php

namespace asvis\lib\orient;

require_once __DIR__.'/../Engine.php';
require_once __DIR__.'/../GraphAlgorithms.php';
require_once __DIR__.'/ObjectsMapper.php';
require_once __DIR__.'/../H.php';
require_once __DIR__.'/../../../config.php';
require_once __DIR__.'/../../vendor/SplClassLoader.php';

$classLoader = new \SplClassLoader('Congow', __DIR__.'/../../vendor/orient-php/src/');
$classLoader->register();

use asvis\Config as Config;
use Congow\Orient\Foundation\Binding as Binding;
use Congow\Orient\Http\Client\Curl as Curl;
use Congow\Orient\Query as Query;
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
		$this->_client   = new Curl(true, 100); // 100s - timeout
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
		
		$nodeNum = (int)$nodeNum;
		$depth   = (int)$depth;
		
		if($nodeNum < 0 || $depth < 0 || $depth > Config::get('orient_max_fetch_depth')) {
			return null;
		}
		
		// +1 because we want maximum distance to be equal with $depth
		$fp = $depth + 1;
		
		$query = "SELECT FROM ASNode WHERE num = {$nodeNum}";
		$fetchplan = "*:{$fp} ASNode.pools:0";
		
// 		$m = microtime(true);
		$json = $this->_orient->query($query, null, 1, $fetchplan);	
// 		echo (microtime(true) - $m) ." - Orient query time\n";
// 		$m = microtime(true);
		$result = json_decode($json->getBody())->result;
// 		echo (microtime(true) - $m) ." - json decode time\n";

		if (!count($result)) {
			return null;
		}
		
		$objectMapper = new ObjectsMapper($result[0], $nodeNum);		
		$graph = $objectMapper->parse();

		return $graph->forJSON();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::structureTree()
	 */
	public function structureTree($nodeNum, $height) {
	
		$nodeNum = (int) $nodeNum;
		$height = (int) $height;
		
		if($nodeNum < 0 || $height < 0 || $height > Config::get('orient_max_fetch_depth')) {
			return null;
		}
		
		// +2 because we want maximum distance to be equal with $height+1
		$fp = $height + 2;
		
		$query = "SELECT FROM ASNode WHERE num = {$nodeNum}";
		$fetchplan = "*:{$fp} ASNode.pools:0";
		
		$json = $this->_orient->query($query, null, 1, $fetchplan);		
		$result = json_decode($json->getBody())->result;

		if (!count($result)) {
			return null;
		}
		
		$objectMapper = new ObjectsMapper($result[0], $nodeNum);		
		$graph = $objectMapper->parse();
		
		$graphAlgorithms = new GraphAlgorithms($graph->forJSON());
		
		return $graphAlgorithms->getTree($height);
	}
	
	public function structurePath($num_start, $num_end) {
	
		$num_start = (int) $num_start;
		$num_end = (int) $num_end;
		
		if($num_start < 0 || $num_end < 0) {
			return null;
		}
		
		// nie wiem jeszcze jak powinien wygladac fetchplan dla tego zapytania
		$fp = Config::get('orient_max_fetch_depth');
		
		$query = "SELECT FROM ASNode WHERE num = {$num_start}";
		$fetchplan = "*:{$fp} ASNode.pools:0";
		
		$json = $this->_orient->query($query, null, 1, $fetchplan);		
		$result = json_decode($json->getBody())->result;

		if (!count($result)) {
			return null;
		}
		
		$objectMapper = new ObjectsMapper($result[0], $num_start);		
		$graph = $objectMapper->parse();
		
		$graphAlgorithms = new GraphAlgorithms($graph->forJSON());
		
		return $graphAlgorithms->getShortestPath($num_start, $num_end);
	}
}
