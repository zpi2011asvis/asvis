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
	
	const MAX_WHEREIN_SIZE = 500;
	const CURL_TIMEOUT = 60; // secons


	/**
	* @var Congow\Orient\Foundation\Binding
	*/
	private $_orient;

	/**
	 * @var Congow\Orient\Http\Client\Curl
	 */
	private $_client;
	
	public function __construct() {
		$this->_client   = new Curl(false, self::CURL_TIMEOUT); // 1 minute - timeout
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
	public function nodesMeta($numbers) {
		$nodes = array();

		$query = "SELECT num, name, pools FROM ASNode WHERE num IN [" . implode(',', $numbers) . "]";
		$fetchplan = "*:2 out:0 in:0";
		
		$json = $this->_orient->query($query, null, -1, $fetchplan);	
		$result = json_decode($json->getBody())->result;

		// return null for all if one of numbers is wrong
		if (count($result) < count($numbers)) {
			return null;
		}

		foreach ($result as $node) {
			$num = $node->num;
			$name = $node->name;

			$pools = array();

			foreach ($node->pools as $pool) {
				$network = $pool->network_as_string;
				$netmask = $pool->netmask;

				$pools[] = array('ip' => $network, 'mask' => $netmask);	
			}

			$nodes[$num] = array('name' => $name, 'pools' => $pools); 
		}	

		return $nodes;
	}


	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::connectionsMeta()
	 */
	public function connectionsMeta($for_node) {
		$query = "SELECT FROM ASNode WHERE num = {$for_node}";
		$json = $this->_orient->query($query);	
		$result = json_decode($json->getBody())->result;

		if (!count($result)) {
			return null;
		}

		return $this->getConnectionMetaFor($result[0]->{'@rid'});
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
		
		if ($num_start < 0 || $num_end < 0) {
			return null;
		}

		$fp = 1;
		$structure = null;
		
		while(!isset($structure) && $fp <= Config::get('orient_max_fetch_depth')) {
		
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
		
			$structure = $graphAlgorithms->getShortestPath($num_end);
			
			$fp++;
		}
		
		return $structure;
	}

	protected function getConnectionMetaFor($rid) {
		$conns_up = $this->getConnectionMetaForDir($rid, 'up');
		$conns_down = $this->getConnectionMetaForDir($rid, 'down');
		$conns = array();

		// merge connections
		foreach ($conns_up as $with => $conn) {
			if (array_key_exists($with, $conns_down)) {
				$conn['dir'] = 'both';
			}
			$conns[] = $conn;
		}
		foreach ($conns_down as $with => $conn) {
			if (!array_key_exists($with, $conns_up)) {
				$conns[] = $conn;
			}
		}
		
		usort($conns, array('asvis\lib\orient\OrientEngine', 'compareConnections'));

		return $conns;		
	}

	protected function getConnectionMetaForDir($rid, $dir) {
		$conns = array();
		$field = ($dir === 'up' ? 'from' : 'to');
		$field2 = ($dir === 'up' ? 'to' : 'from');

		$query = "SELECT FROM ASConn WHERE {$field} = " . $rid;
		$fetchplan = "*:1 ASConn.{$field}:0 ASNode.in:0 ASNode.out:0 ASNode.pools:0";
		
		$json = $this->_orient->query($query, null, -1, $fetchplan);
		$result = json_decode($json->getBody());
		
		if (!isset($result->result)) {
// 			$result = $result->result;
			
			var_dump($json);
			echo('JSON -- RESULT -- SPACING -----------------'. PHP_EOL);
			var_dump($result);
			die(1);
		} else {
			$result = $result->result;
		}

		foreach ($result as $conn) {
			$status = $conn->status;
			$with = $conn->{$field2};

			$conns[$with->num] = array('with' => $with->num, 'status' => $status, 'dir' => $dir); 
		}

		return $conns;
	}

	private function compareConnections($conn1, $conn2) {
		if (
			$conn1['status'] !== $conn2['status'] &&
			($conn1['status'] === 0 || $conn2['status'] === 0)
		) {
			// sort by status only if pairs: (0,1) (0,2) (1,0) (2,0)
			return $conn1['status'] - $conn2['status'];
		}

		if ($conn1['dir'] !== $conn2['dir']) {
			if ($conn1['dir'] === 'both') return -1;
			if ($conn2['dir'] === 'both') return 1;
			if ($conn1['dir'] === 'up') return -1;
			return 1;
		}

		return $conn1['with'] - $conn2['with'];
	}
}
