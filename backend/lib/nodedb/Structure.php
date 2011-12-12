<?php

namespace asvis\lib\nodedb;

require_once 'Node.php';

use asvis\lib\nodedb\Node as Node;

/**
 * Storage class for ASNodes
 */
class Structure {
	
	/**
	 * Structure
	 * @var array of ASNode
	 */
	protected $_structure;
	protected $_weight_order;
	protected $_distance_order;
	
	/**
	 * @param array of mixed $structure output of Engine::structureGraph or Engine::structureTree
	 */
	public function __construct($structure = null, $weight_order = null, $distance_order = null) {
		if ($structure == null) {
			$this->_structure = array();
		} else {
			foreach ($structure as $num => $node) {
				$out = array_values($node->out);				
				$in = array_values($node->in);
							
				$this->_structure[$num] =
					new Node(
						$num,
						$out,
						$in,
						$node->distance,
						$node->weight
					);
			}
		}
		
		if ($weight_order == null) {
			$this->_weight_order = array();
		} else {
			$this->_weight_order = $weight_order;
		}
		
		if ($distance_order == null) {
			$this->_distance_order = array();
		} else {
			$this->_distance_order = $distance_order;
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
		$toEncode = array();
		
		$toEncode['structure'] = $this->_structure;
		$toEncode['weight_order']	= $this->_weight_order;
		$toEncode['distance_order']	= $this->_distance_order;
		
		return $toEncode;
	}
	
}
