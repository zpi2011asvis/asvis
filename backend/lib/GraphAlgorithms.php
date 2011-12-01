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
		$this->_removeConnected($leafs, $dir);
		
		return $this->_rebuildTree();
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
	
	private function _removeConnected($conns, $dir) {
		
		foreach($conns as $num) {
			
			if(isset($this->_structure[$num])) {
				$next_conns = array();	
						
				if($this->_structure[$num]->distance > 0) {
				
					if($dir === 'both') {
						$next_conns = array_merge($this->_structure[$num]->in, $this->_structure[$num]->out);
					}
					else {
						$next_conns = $this->_structure[$num]->$dir;
					}
					
					unset($this->_structure[$num]);
				} 
				
				$this->_removeConnected($next_conns, $dir);
			}
		}
	}
	
	private function _rebuildTree() {
		$structure = array();
		$weight_order = array();
		$distance_order = array();
		
		foreach($this->_structure as $num=>$node) {
			$in_array = array();
			$out_array = array();			
											
			foreach($node->in as $in) {
				if(isset($this->_structure[$in])) {
					$in_array[] = $in;
				}
			}
				
			foreach($node->out as $out) {	
				if(isset($this->_structure[$out])) {
					$out_array = $out;
				}
			}
			
			$node->in = $in_array;
			$node->out = $out_array;
			$structure[$num] = $node;
		}   
		
		foreach($this->_weight_order as $num) {
			if(isset($this->_structure[$num])) {
				$weight_order[] = $num;
			}
		}
		
		foreach($this->_distance_order as $num) {
			if(isset($this->_structure[$num])) {
				$distance_order[] = $num;
			}
		}
		
		if(count($distance_order) <= 1) {
			$structure = array();
			$weight_order = array();
			$distance_order = array();			
		}
		
		return array('structure'=>$structure, 'weight_order'=>$weight_order, 'distance_order'=>$distance_order);
	}	
}
