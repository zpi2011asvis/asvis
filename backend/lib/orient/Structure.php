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
				$out = array();				
				foreach ($node->out as $linkedNum) {
					$out[] = $linkedNum;
				}
				
				$in  = array();
				foreach ($node->in as $linkedNum) {
					$in[] = $linkedNum;
				}
							
				$this->_structure[$num] =
					new Node(
						$node->{'@rid'},
						$node->num,
						$node->name,
						$out,
						$in,
						$node->depth
					);
			}
		}
	}
	
	public function get($num) {
		return $this->_structure[$num];
	}
	
	public function forJSON() {
		return $this->_structure;
	}
}
