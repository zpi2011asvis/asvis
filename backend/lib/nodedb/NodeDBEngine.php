<?php

namespace asvis\lib\nodedb;

require_once __DIR__.'/../Engine.php';
require_once __DIR__.'/../GraphAlgorithms.php';
require_once __DIR__.'/ObjectsMapper.php';
require_once __DIR__.'/../H.php';
require_once __DIR__.'/../../../config.php';
require_once __DIR__.'/../../vendor/nodedb-driver/Binding.php';
require_once __DIR__.'/../../vendor/nodedb-driver/Curl.php';
require_once __DIR__.'/../../vendor/nodedb-driver/Response.php';

use asvis\Config as Config;
use NodeDBDriver\Binding as Binding;
use NodeDBDriver\Curl as Curl;
use NodeDBDriver\Response as Response;
use asvis\lib\nodedb\ObjectsMapper as ObjectsMapper;
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

	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::nodesMeta()
	 */
	public function nodesMeta($numbers) {

	}


	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::connectionsMeta()
	 */
	public function connectionsMeta($for_node) {
		
		$query = "connections/meta/{$for_node}";
		
		$json = $this->_nodedb->query($query);	
		$result = json_decode($json->getBody());
		
		if (is_null($result) || !count($result)) {
			return null;
		}

		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::structureGraph()
	 */
	public function structureGraph($nodeNum, $depth) {
		
		$query = "graph/{$nodeNum}/{$depth}";
		
		$json = $this->_nodedb->query($query);	
		$result = json_decode($json->getBody());
		
		if (is_null($result) || !count($result)) {
			return null;
		}

		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see asvis\lib.Engine::structureTree()
	 */
	public function structureTree($nodeNum, $height, $dir) {
		
		// +1 because we want maximum distance to be equal with $height+1
		$fp = $height + 1;
		
		$query = "graph/{$nodeNum}/{$fp}";
		
		$json = $this->_nodedb->query($query);	
		$result = json_decode($json->getBody());

		if (is_null($result) || !count($result)) {
			return null;
		}

		$objectMapper = new ObjectsMapper($result, $nodeNum);		
		$graph = $objectMapper->parse();

		$graphAlgorithms = new GraphAlgorithms($graph->forJSON());

		return $graphAlgorithms->getTree($height, $dir);
	}
	
	public function structurePath($num_start, $num_end, $dir) {
		$structure = array();
		$depth_left = 1;
		$depth_right = 1;
		
		if ($dir === 'in') {
			$opp_dir = 'out';
		}
		elseif ($dir === 'out') {
			$opp_dir = 'in';
		}
		else {
			$opp_dir = 'both';
		}
		
		while ($depth_left <= Config::get('orient_max_fetch_depth')) {	
			
			$query_root = "graph/{$num_start}/{$depth_left}";
			$json_root = $this->_nodedb->query($query_root);	
			$result_root = json_decode($json_root->getBody());
			$objectMapper = new ObjectsMapper($result_root, $num_start);		
			$graph_root = $objectMapper->parse()->forJSON();
		
			$query_target = "graph/{$num_end}/{$depth_right}";
			$json_target = $this->_nodedb->query($query_target);	
			$result_target = json_decode($json_target->getBody());
			$objectMapper = new ObjectsMapper($result_target, $num_end);		
			$graph_target = $objectMapper->parse()->forJSON();
			
			$both_nodes = array_intersect_key($graph_root['structure'], $graph_target['structure']);

			foreach ($both_nodes as $num => $node) {

				// znajdz najkrotsze sciezki do wezla koncowego do roota
				// i od wezla poczatkowego do roota

				$graphAlgorithms = new GraphAlgorithms($graph_root);
				$structure_root = $graphAlgorithms->getShortestPath($num, $opp_dir);
				
				$graphAlgorithms = new GraphAlgorithms($graph_target);
				$structure_target = $graphAlgorithms->getShortestPath($num, $dir);

				foreach ($structure_root as $path_root) {
					foreach($structure_target as $path_target) {
						$structure[] = $this->_mergePaths($path_root, $path_target);		
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
	
}
