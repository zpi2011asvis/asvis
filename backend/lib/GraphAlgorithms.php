<?php

namespace asvis\lib;

class GraphAlgorithms {

	private $_structure;

	public function __construct($structure) {
		$this->_structure = $structure;
	}
	
	public function getShortestPaths($n1, $n2) {
	}
	
	public function getTree($height, $dir) {
		$tree = array();
		$leafs = array();
		
		for($i=$height; $i>=0; $i--) {
			$new_leafs = $this->_findLeafs($i, $dir);
			
			if($i < $height) {
				
				foreach($leafs as $number=>$properties) {
					if($this->_isOneConnection($new_leafs, $number, $dir)) {
						$tree[$number] = $properties;
					}
				}

			}
			
			$leafs = $new_leafs;
		}
		
		$leafs_keys = array_keys($leafs);
		$root = $leafs_keys[0];
		
		$tree[$root] = $leafs[$root];
		
		return $tree; //$this->_rebuildTree($tree, $root, $dir);
	}
	
	private function _findLeafs($distance, $dir) {
		$leafs = array();
		
		foreach($this->_structure as $number=>$properties) {
			if($properties['distance'] === $distance) {
				$count = 0;
			
				foreach($properties[$dir] as $connection) {
					if(isset($this->$_structure[$connection])) {
						if($this->$_structure[$connection]['distance'] === $distance) {
							$count++;
						}
					}
				}
			 	
			 	if($count === 0) {
					$leafs[$number] = $properties;
				}
			}   
		}
		
		return $leafs;
	}
	
	private function _isOneConnection($new_leafs, $number, $dir) {
		$connected = 0;
		
		foreach($new_leafs as $new_leaf) {
			if(in_array($number, $new_leaf[$dir])) {
				$connected++;
			}
		}
		
		return ($connected === 1);
	}
	
	private function _rebuildTree($tree, $number, $dir) {
		$new_tree = array();
		
		if(isset($tree[$number])) {
			$new_tree[$number] = array(
				$dir => array(),
				'distance' => $tree[$number]['distance'],
				'count' => 0
			);

			foreach($tree[$number][$dir] as $connection) {				
				$node = $this->_rebuildTree($tree, $connection, $dir);
				if(!empty($node)) {
					$new_tree[$number][$dir][] = $connection;
					$new_tree = $new_tree + $node;
				}
			}
			
			$new_tree[$number]['count'] = count($new_tree[$number][$dir]); 
		}
		
		return $new_tree;
	}
}
