<?php

namespace asvis\lib\orient;

require_once 'Structure.php';

use asvis\lib\orient\Structure as Structure;

class Graph extends Structure {
	
	/**
	 * Returns structure which can be properly encoded to json.
	 * @return string
	 */
	public function toJSON() {
		$toEncode = array();
		
		foreach ($this->_structure as $num => $node) {
			$toEncode['structure'][$num] = array();
			
			foreach ($node->out as $linkedNum) {				
				$toEncode['structure'][$num][] = $linkedNum;
			}
		}
		
		$strCopy = $this->_structure;
		
		uasort($strCopy, array('asvis\lib\orient\Graph', 'compareCountOut'));
		
		foreach($strCopy as $num => $node) {
			$toEncode['weight_order'][] = $num;
		}
		
		$strCopy = $this->_structure;
		
		uasort($strCopy, array('asvis\lib\orient\Graph', 'compareDepth'));
		
		foreach($strCopy as $num => $node) {
			$toEncode['depth_order'][] = $num;
		}
		
		return $toEncode;
	}
	
	private function compareCountOut($a, $b) {
		return $b->count_out - $a->count_out;
	}
	
	private function compareCountIn($a, $b) {
		return $b->count_in - $a->count_in;
	}
	
	private function compareDepth($a, $b) {
		return -($b->depth - $a->depth);
	}
	
}













