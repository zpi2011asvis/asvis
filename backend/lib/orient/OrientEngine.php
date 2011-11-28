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

		if (count($result) < count($numbers)) {
			return array();
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
				
		// +1 because we want maximum distance to be equal with $depth
		$fp = $depth + 1;
		
		$query = "SELECT FROM ASNode WHERE num = {$nodeNum}";
		$fetchplan = "*:{$fp} ASNode.pools:0";
		
		$json = $this->_orient->query($query, null, 1, $fetchplan);	
		$result = json_decode($json->getBody())->result;

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
	public function structureTree($nodeNum, $height, $dir) {
		
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
		
		return $graphAlgorithms->getTree($height, $dir);
	}
	
	public function structurePath($num_start, $num_end, $dir) {

		$fp = 1;
		$structure = array();
		
		while(empty($structure) && $fp <= Config::get('orient_max_fetch_depth')) {
			
			//from $num_start
			$query = "SELECT FROM ASNode WHERE num = {$num_start}";
			$fetchplan = "*:{$fp} ASNode.pools:0";
		
			$json = $this->_orient->query($query, null, 1, $fetchplan);		
			$result = json_decode($json->getBody())->result;

			if (!count($result)) {
				return null;
			}
		
			$objectMapper = new ObjectsMapper($result[0], $num_start);		
			$graph = $objectMapper->parse();
			
			$start_graph = $graph->forJSON();
			
			if(array_key_exists($num_end, $start_graph['structure'])) {
				$graphAlgorithms = new GraphAlgorithms($graph->forJSON());
		
				$structure = $graphAlgorithms->getShortestPath($num_end, $dir);
			}
			else {
				//from $num_end
				$query = "SELECT FROM ASNode WHERE num = {$num_end}";
				$fetchplan = "*:{$fp} ASNode.pools:0";
				
				if (!count($result)) {
					return null;
				}
		
				$objectMapper = new ObjectsMapper($result[0], $num_end);		
				$graph = $objectMapper->parse();
			
				$end_graph = $graph->forJSON();
				
				$both_nodes = array_intersect_key($start_graph['structure'], $end_graph['structure']);
				
				foreach($both_nodes as $node) {
					$graphAlgorithms = new GraphAlgorithms($start_graph);
					$start_structure = $graphAlgorithms->getShortestPath($node, $dir);
					
					$graphAlgorithms = new GraphAlgorithms($end_graph);
					$end_structure = $graphAlgorithms->getShortestPath($node, $dir);
					
					$structure[] = array(
						'structure'=>$start_structure['structure']+$end_structure['structure'], 
						'weight_order'=>$start_structure['weight_order']+$end_structure['weight_order'], 
						'distance_order'=>$start_structure['distance_order']+$end_structure['distance_order']
					);
				}
			}
			
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
		
		$response = $this->_orient->query($query, null, -1, $fetchplan);
		$json = $response->getBody();
		$result = json_decode($json)->result;

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
