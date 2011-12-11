<?php

namespace asvis\lib\nodedb;

require_once __DIR__.'/../Engine.php';
require_once __DIR__.'/../GraphAlgorithms.php';
require_once __DIR__.'/../orient/ObjectsMapper.php';
require_once __DIR__.'/../H.php';
require_once __DIR__.'/../../../config.php';
require_once __DIR__.'/../../vendor/nodedb-driver/Binding.php';
require_once __DIR__.'/../../vendor/nodedb-driver/Curl.php';
require_once __DIR__.'/../../vendor/nodedb-driver/Response.php';

use asvis\Config as Config;
use NodeDBDriver\Binding as Binding;
use NodeDBDriver\Curl as Curl;
use NodeDBDriver\Response as Response;
use asvis\lib\orient\ObjectsMapper as ObjectsMapper;
use asvis\lib\Engine as Engine;
use asvis\lib\GraphAlgorithms as GraphAlgorithms;
use asvis\lib\H as H;

class NodeDBEngine implements Engine {
	
	const MAX_WHEREIN_SIZE = 500;
	const CURL_TIMEOUT = 60; // secons


	/**
	* @var NodeDBDriver\Binding
	*/
	private $_nodedb;

	/**
	 * @var NodeDBDriver\Curl
	 */
	private $_client;
	
	public function __construct() {
		$this->_client = new Curl(false, self::CURL_TIMEOUT); // 1 minute - timeout
		$this->_nodedb = new Binding($this->_client);
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

		return $this->getConnectionsMetaFor($result[0]->{'@rid'});
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::structureGraph()
	 */
	public function structureGraph($nodeNum, $depth) {
		
		$query = "graph/{$nodeNum}/{$depth}";
		
		$json = $this->_nodedb->query($query);	
		$result = json_decode($json->getBody());
		var_dump($result); die;
		if(is_null($result)) {
			return null;
		} else {
			$result = $result->result;
		}

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
		$structure = array();
		$depth_left = 1;
		$depth_right = 1;
		
		$regexp = '/"@rid": "(#\d:\d+)".*,\ $/m';
		
		if ($dir === 'in') {
			$opp_dir = 'out';
			$dir_fetchplan = ' ASNode.out:0';
			$opp_dir_fetchplan = ' ASNode.in:0';
		}
		elseif ($dir === 'out') {
			$opp_dir = 'in';
			$dir_fetchplan = ' ASNode.in:0';
			$opp_dir_fetchplan = ' ASNode.out:0';
		}
		else {
			$opp_dir = 'both';
			$dir_fetchplan = '';
			$opp_dir_fetchplan = '';
		}
		
		while ($depth_left <= Config::get('orient_max_fetch_depth')) {	
			
			$fetchplan = "*:{$depth_left} ASNode.pools:0" . $dir_fetchplan;
			$query_root = "SELECT FROM ASNode WHERE num = {$num_start}";		
			$json_root = $this->_orient->query($query_root, null, 1, $fetchplan)->getBody();
			
			$fetchplan = "*:{$depth_right} ASNode.pools:0" . $opp_dir_fetchplan;
			$query_target = "SELECT FROM ASNode WHERE num = {$num_end}";		
			$json_target = $this->_orient->query($query_target, null, 1, $fetchplan)->getBody();

			preg_match_all($regexp, $json_root, $rids_root);
			preg_match_all($regexp, $json_target, $rids_target);
			$rids_root = $rids_root[1];
			$rids_target = $rids_target[1];

			if ($this->hasCommonRids($rids_root, $rids_target)) {
				$result_root = json_decode($json_root)->result;
				$result_target = json_decode($json_target)->result;

				if (!count($result_root) || !count($result_target)) {
					return null;
				}
		
				$objectMapper = new ObjectsMapper($result_root[0], $num_start);		
				$graph = $objectMapper->parse();
				$start_graph = $graph->forJSON();
		
				$objectMapper = new ObjectsMapper($result_target[0], $num_end);		
				$graph = $objectMapper->parse();
				$end_graph = $graph->forJSON();
			
				$both_nodes = array_intersect_key($start_graph['structure'], $end_graph['structure']);
			
				foreach ($both_nodes as $num => $node) {
					// pobierz struktury jeszcze raz ale z otoczeniem
					// wymagane niestety przez getShortestPath

					$fetchplan = "*:" . ($depth_left + 1) . " ASNode.pools:0";
					$query_root = "SELECT FROM ASNode WHERE num = {$num_start}";
					$json_root = $this->_orient->query($query_root, null, 1, $fetchplan)->getBody();
					
					$fetchplan = "*:" . ($depth_right + 1) . " ASNode.pools:0";
					$query_target = "SELECT FROM ASNode WHERE num = {$num_end}";
					$json_target = $this->_orient->query($query_target, null, 1, $fetchplan)->getBody();

					$result_root = json_decode($json_root)->result;
					$result_target = json_decode($json_target)->result;

					$objectMapper = new ObjectsMapper($result_root[0], $num_start);		
					$graph = $objectMapper->parse();
					$start_graph = $graph->forJSON();
			
					$objectMapper = new ObjectsMapper($result_target[0], $num_end);		
					$graph = $objectMapper->parse();
					$end_graph = $graph->forJSON();

					// znajdz najkrotsze sciezki do wezla koncowego do roota
					// i od wezla poczatkowego do roota

					$graphAlgorithms = new GraphAlgorithms($start_graph);
					$start_structure = $graphAlgorithms->getShortestPath($num, $opp_dir);
					
					$graphAlgorithms = new GraphAlgorithms($end_graph);
					$end_structure = $graphAlgorithms->getShortestPath($num, $dir);

					foreach ($start_structure as $start_path) {
						foreach($end_structure as $end_path) {
							$structure[] = $this->_mergePaths($start_path, $end_path);
						}	
					}
				}

				return array(
					'paths' => $structure,
					'depth_left' => $depth_left,
					'depth_right' => $depth_right
				);
			}
			
			if ($depth_left <= $depth_right) {
				$depth_left++;
			}
			else {
				$depth_right++;
			}
		}

		return 0;
	}
	
	protected function _mergePaths($start_path, $end_path) {
		$result = $start_path;
		
		for ($i = (count($end_path)-2); $i >= 0; $i--) {
			$result[] = $end_path[$i];
		}
		
		return $result;
	}
	
	protected function hasCommonRids($rids_root, $rids_target) {
		return count(array_intersect($rids_root, $rids_target)) > 0;
	}
	

	protected function getConnectionsMetaFor($rid) {
		$conns_up = $this->getConnectionsMetaForDir($rid, 'up');
		$conns_down = $this->getConnectionsMetaForDir($rid, 'down');
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

	protected function getConnectionsMetaForDir($rid, $dir) {
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
