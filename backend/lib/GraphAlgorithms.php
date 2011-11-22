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
		$structure = null;
		
		if(array_key_exists($num_end, $this->_structure)) {
			$distance = $this->_structure[$num_end]->distance;
			$paths = $this->_findPaths(array($num_end), $distance, $dir);
			
			
			$structure = array();
			
			foreach($paths as $path) {
				if(count($path) === ($distance+1)) {
					$structure[] = $this->_rebuildStructure($path);
				}
			}
		}	
		
		return $structure;
	}
	
	private function _findPaths($nodes, $distance, $dir) {
		$paths = array();
		
		if($distance > 0) {
			foreach($nodes as $num) {
				if($this->_structure[$num]->distance === $distance) {
					
					if($dir === 'both') {
						$conns = array_merge($this->_structure[$num]->in, $this->_structure[$num]->out);
						$conns = array_unique($conns);
					}
					else {
						$conns = $this->_structure[$num]->$dir;
					}
					
					foreach($this->_findPaths($conns, ($distance-1), $dir) as $path) {
						$path[] = $num;
					 	$paths[] = $path; 
					}
				}
			}
		}
		else {
			$path = array();
			foreach($nodes as $num) {
				if($this->_structure[$num]->distance === 0) {
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
		
		return $this->_rebuildStructure($leafs+$conns, true);
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
				}

				$nodes += $conns_up;
				$nodes += $this->_findConnected($conns_up, $dir);
			}
		}
		
		return $nodes;
	}
	
	private function _rebuildStructure($conns, $difference = false) {
		$structure = array();
		$weight_order = array();
		$distance_order = array();
		
		foreach($this->_structure as $num=>$node) {
			$difference ? $add = !in_array($num, $conns) : $add = in_array($num, $conns);

			if($add) {
				$structure[$num] = $node; 
				
				$in_array = array();
				$out_array = array();
				
				foreach($this->_structure[$num]->in as $in) {
					$difference ? $add = !in_array($in, $conns) : $add = in_array($in, $conns);
					
					if($add) {
						$in_array[] = $in;
					}
				}
				
				foreach($this->_structure[$num]->out as $out) {
					$difference ? $add = !in_array($out, $conns) : $add = in_array($out, $conns);
					
					if($add) {
						$out_array[] = $out;
					}
				}
					
				$structure[$num]->in = $in_array;
				$structure[$num]->out = $out_array;
			}
		}   
		
		foreach($this->_weight_order as $num) {
			$difference ? $add = !in_array($num, $conns) : $add = in_array($num, $conns);
			
			if($add) {
				$weight_order[] = $num;
			}
		}
		
		foreach($this->_distance_order as $num) {
			$difference ? $add = !in_array($num, $conns) : $add = in_array($num, $conns);

			if($add) {
				$distance_order[] = $num;
			}
		}
		
		return array('structure'=>$structure, 'weight_order'=>$weight_order, 'distance_order'=>$distance_order);
	}
}
