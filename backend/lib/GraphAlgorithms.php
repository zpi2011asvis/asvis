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
	
	public function getShortestPaths($n1, $n2) {
	}
	
	public function getTree($height, $dir = null) {
		$leafs = $this->_findLeafs($height+1);
		$conns = $this->_findConnected($leafs, $dir);
		
		return $this->_rebuildTree($leafs+$conns, $height);
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
					$nums_up = array_merge($this->_structure[$num]->in, $this->_structure[$num]->$out);
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
	
	private function _rebuildTree($conns, $height) {
		$tree = array();
		$distance_order = array();
		
		foreach($this->_structure as $num=>$node) {
			if(!in_array($num, $conns)) {
				$tree[$num] = $node; 
				
				if($node->distance === 0 || $node->distance === $height) {
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
