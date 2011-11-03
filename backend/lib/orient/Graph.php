<?php

namespace asvis\lib\orient;

class Graph {
	
	private $_structure;
	
	public function add($node) {
		$this->_structure[$node->num] = $node;
	}
	
	public function remove($nodeNum) {
		unset($this->_structure[$nodeNum]);
	}
	
}