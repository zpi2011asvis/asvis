<?php

namespace asvis\lib\orient;

require_once 'Node.php';

use asvis\lib\orient\Node as Node;

class Structure {
	protected $_structure;
	
	public function __construct($structure = null) {
		if ($structure == null) {
			$this->_structure = array();
		} else {
			foreach ($structure as $num => $node) {
				$out = array_values($node->out);				
				$in = array_values($node->in);
							
				$this->_structure[$num] =
					new Node(
						$node->{'@rid'},
						$node->num,
						$node->name,
						$out,
						$in,
						$node->distance
					);
			}
		}
	}
	
	public function groupAdd($nodes) {
		foreach ($nodes as $num => $node) {
			$this->_structure[$num] = $node;
		}
	}
	
	public function getAll() {
		return $this->_structure;
	}
	
	public function get($num) {
		return $this->_structure[$num];
	}
	
	public function forJSON() {
		return $this->_structure;
	}
}
