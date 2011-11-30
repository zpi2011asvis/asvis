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
	
	public function getShortestPath($num_end, $dir) {
		$structure = array();
		
		$distance = $this->_structure[$num_end]->distance;
		$paths = $this->_findPaths(array($num_end), $distance, $dir);

		foreach ($paths as $path) {
			if (count($path) === ($distance + 1)) {
				$structure[] = $path;
			}
		}	
		
		return $structure;
	}
	
	private function _findPaths($nodes, $distance, $dir) {
		$paths = array();
		
		if ($distance > 0) {
			foreach($nodes as $num) {
				if ($this->_structure[$num]->distance === $distance) {
					
					if ($dir === 'both') {
						$conns = array_merge($this->_structure[$num]->in, $this->_structure[$num]->out);
						$conns = array_unique($conns);
					}
					else {
						$conns = $this->_structure[$num]->$dir;
					}
					
					foreach ($this->_findPaths($conns, ($distance - 1), $dir) as $path) {
						$path[] = $num;
					 	$paths[] = $path; 
					}
				}
			}
		}
		else {
			$path = array();
			foreach ($nodes as $num) {
				if ($this->_structure[$num]->distance === 0) {
					$path[] = $num;
				}
			}
			$paths[] = $path;
		}
		
		return $paths;
	}
	
	public function getTree($height, $dir) {
		$leafs = $this->_findLeafs($height+1);
		$conns = $this->_findConnected($leafs, $dir);
		
		return $this->_rebuildStructure(array_merge($leafs, $conns), $dir);
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
	
	private function _findConnected($conns, $dir, $checked = array()) {
		$nodes = array();
		
		foreach($conns as $num) {
			$distance = $this->_structure[$num]->distance;
			
			if($distance > 1) {
				$conns_up = array();
				$conns_same = array();
				$nums_up = array();
				
				if($dir === 'both') {
					$nums_up = array_merge($this->_structure[$num]->in, $this->_structure[$num]->out);
				}
				else {
					$nums_up = $this->_structure[$num]->$dir;
				}
				
				foreach($nums_up as $num_up) {
					if($this->_structure[$num_up]->distance < $distance) {
						$conns_up[] = $num_up;
					}
					else if($this->_structure[$num_up]->distance === $distance) {
						if(!in_array($num_up, $checked)) {
							$conns_same[] = $num_up;
							$checked[] = $num;
						}
					}
				}
				//var_dump($conns_same);
				$nodes = array_merge($nodes, $conns_up);
				$nodes = array_merge($nodes, $conns_same);
				
				$nodes = array_merge($nodes, $this->_findConnected($conns_same, $dir, $checked));
				$nodes = array_merge($nodes, $this->_findConnected($conns_up, $dir));
			}
		}
		
		return $nodes;
	}
	
	private function _connsInTree($node, $conns, $dir) {
		$inTree = true;
		
		if ($dir === 'both') {
			$nums_up = array_merge($node->in, $node->out);
		}
		else {
			$nums_up = $node->$dir;
		}
		
		foreach ($nums_up as $num_up) {
			if(in_array($num_up, $conns)) {
				$inTree = false;
			}
		}
	var_dump($inTree);
		return $inTree;
	}
	
	private function _rebuildStructure($conns, $dir) {
		$structure = array();
		$weight_order = array();
		$distance_order = array();
		
		foreach($this->_structure as $num=>$node) {
			if(!in_array($num, $conns)) {

				$structure[$num] = $node; 
								
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
					
				$structure[$num]->in = $in_array;
				$structure[$num]->out = $out_array;
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
		
		return array('structure'=>$structure, 'weight_order'=>$weight_order, 'distance_order'=>$distance_order);
	}
	
}
