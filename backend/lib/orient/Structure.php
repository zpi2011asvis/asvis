<?php

namespace asvis\lib\orient;

require_once 'Node.php';

use asvis\lib\orient\Node as Node;

/**
 * Storage class for ASNodes
 */
class Structure {
	
	/**
	 * Structure
	 * @var array of ASNode
	 */
	protected $_structure;
	
	/**
	 * @param array of mixed $structure output of Engine::structureGraph or Engine::structureTree
	 */
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
	
	/**
	 * Return ASNode with given num;
	 * @param int $num
	 * @return ASNode
	 */
	public function get($num) {
		return $this->_structure[$num];
	}
	
	/**
	 * Return structure in JSON-ready form
	 * @return array of ASNode
	 */
	public function forJSON() {
		return $this->_structure;
	}
}
