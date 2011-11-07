<?php

namespace asvis\lib\orient;

class Structure {
	protected $_structure;
	
	public function __construct($structure = null) {
		if ($structure == null) {
			$this->_structure = array();
		} else {
			$this->_structure = $structure;
		}
	}
	
	public function add($node) {
		$this->_structure[$node->num] = $node;
	}
	
	public function remove($nodeNum) {
		unset($this->_structure[$nodeNum]);
	}
	
	/**
	 * Returns structure which can be properly encoded to json.
	 * @return string
	 */
	public function toJSON() {
		
		$toEncode = array();
		
		foreach ($this->_structure as $num => $node) {
			$toEncode[$num]['in'] = array();
			$toEncode[$num]['out'] = array();
			
			foreach ($node->in as $linkedNum) {				
				$toEncode[$num]['in'][] = $linkedNum;
			}
			
			foreach ($node->out as $linkedNum) {				
				$toEncode[$num]['out'][] = $linkedNum;
			}
		}
		
		return $toEncode;
	}
}