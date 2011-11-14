<?php

namespace asvis\lib;

class GraphAlgorithms {

	private $_structure = null;
	private $_weigt_order = null;
	private $_distance_order = null;

	public function __construct($graph) {
		$this->_structure = $graph['structure'];
		$this->_weight_order = $graph['weight_order'];
		$this->_distance_order = $graph['distance_order'];
	}
	
	public function getShortestPath($num_start, $num_stop, $dir=null) {
		$path = array();
		
		$nodes = array(); 
		$conn = array();
		if(array_key_exists($num_start, $this->_structure) && array_key_exists($num_stop, $this->_structure)) {
			$nodes[$num_start] = $this->_createNode($num_start, $dir, 1);
			$nodes[$num_stop] = $this->_createNode($num_stop, $dir);
		
			foreach($this->_structure as $num=>$node) {
				if($num !== $num_start && $num !== $num_stop) {
					$nodes[$num] = $this->_createNode($num, $dir);
				}
			}

			$nodes = $this->_breadthFirstSearch($nodes);
		
			$path =	$this->_resolvePath($num_start, $num_stop, $nodes);
		}	
		
		return $this->_rebuildStructure($path);
	}
	
	private function _createNode($num, $dir, $color=0) {
		if(!isset($dir)) {
			$conn = array_merge($this->_structure[$num]->in, $this->_structure[$num]->out);
		}
		else {
			$conn = $this->_structure[$num]->$dir;
		}
		
		$node = array(
			'color' => $color,
			'distance' => 0,
			'parent' => null,
			'conn' => $conn,
		);
		
		return $node;
	}
	
	private function _breadthFirstSearch($nodes) {
		foreach($nodes as $num=>$node) {
			foreach($node['conn'] as $num_conn) {
				if($nodes[$num_conn]['color'] === 0) {
					$nodes[$num_conn]['color'] = 1;
					$nodes[$num_conn]['distance']++;
					$nodes[$num_conn]['parent'] = $num;
				}
			}
		
			$nodes['num']['color'] = 2;
		}
		
		return $nodes;
	}
	
	private function _resolvePath($num_start, $num_stop, $nodes) {
		$path = array($num_stop);
		
		$parent = $num_stop;
		
		while($parent !== $num_start) {
			$parent = $nodes[$parent]['parent'];
			$path[] = $parent;
		}
		
		return $path;
	}
	
	public function getTree($height, $dir = null) {
		$leafs = $this->_findLeafs($height+1);
		$conns = $this->_findConnected($leafs, $dir);
		
		return $this->_rebuildStructure($leafs+$conns);
	}
	
	private function _findLeafs($distance) {
		$leafs = array();
		
		foreach($this->_structure as $num=>$conn) {
			if($conn->distance === $distance) {
				$leafs[] = $num;
			}   
		}
		
		return $leafs;
	}
	
	private function _findConnected($conns, $dir) {
		$nodes = array();
		
		foreach($conns as $num) {
			$distance = $this->_structure[$num]->distance;
			
			if($distance > 1) {
				$conns_up = array();
				$nums_up = array();
				
				if(!isset($dir)) {
					$nums_up = array_merge($this->_structure[$num]->in, $this->_structure[$num]->out);
				}
				else {
					$nums_up = $this->_structure[$num]->$dir;
				}
				
				foreach($nums_up as $num_up) {
					if($this->_structure[$num_up]->distance < $distance) {
						$conns_up[] = $num_up;
					}
				}

				$nodes += $conns_up;
				$nodes += $this->_findConnected($conns_up, $dir);
			}
		}
		
		return $nodes;
	}
	
	private function _rebuildStructure($conns) {
		$tree = array();
		
		foreach($this->_structure as $num=>$node) {
			if(!in_array($num, $conns)) {
				$tree[$num] = $node; 
				
				$in_array = array();
				$out_array = array();
				
				foreach($this->_structure[$num]->in as $in) {
					if(!in_array($in, $conns)) {
						$in_array[] = $in;
					}
				}
				
				foreach($this->_structure[$num]->out as $out) {
					if(!in_array($out, $conns)) {
						$out_array[] = $out;
					}
				}
					
				$tree[$num]->in = $in_array;
				$tree[$num]->out = $out_array;
			}
		}   
		
		foreach($this->_weight_order as $num) {
			if(!in_array($num, $conns)) {
				$weight_order[] = $num;
			}
		}
		
		foreach($this->_distance_order as $num) {
			if(!in_array($num, $conns)) {
				$distance_order[] = $num;
			}
		}
		
		return array('structure'=>$tree, 'weight_order'=>$weight_order, 'distance_order'=>$distance_order);
	}
}
