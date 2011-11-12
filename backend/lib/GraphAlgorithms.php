<?php

namespace asvis\lib;

class GraphAlgorithms {

	private $_structure;

	public function __construct($graph) {
		$this->_structure = $graph['structure'];
	}
	
	public function getShortestPaths($n1, $n2) {
	}
	
	public function getTree($height, $dir) {
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
				
				foreach($this->_structure[$num]->$dir as $num_up) {
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
		$distance1nums = array();
		$root_num = null;
		
		foreach($this->_structure as $num=>$node) {
			if(!in_array($num, $conns)) {
				$tree[$num] = $node; 
				
				if($node->distance === 0) {
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
				else if($node->distance === $height) {
					$tree[$num]->in = array();
					$tree[$num]->out = array();
				}
			}   
		}
		
		return $tree;
	}
}
