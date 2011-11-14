<?php

namespace asvis\lib\orient;

require_once 'Structure.php';

use asvis\lib\orient\Structure as Structure;

class Graph extends Structure {
	
	public function forJSON() {
		$toEncode = array();
		
		$toEncode['structure'] = parent::forJSON();
		$toEncode['weight_order']	= $this->_getWeightOrder();
		$toEncode['distance_order']	= $this->_getDistanceOrder();
		
		return $toEncode;
	}
	
	private function _getWeightOrder() {
		$struct = $this->_structure;
		
		uasort($struct, array('asvis\lib\orient\Graph', 'compareWeight'));
		
		return array_keys($struct);
	}
	
	private function _getDistanceOrder() {
		$struct = $this->_structure;
	
		uasort($struct, array('asvis\lib\orient\Graph', 'compareDistance'));
	
		return array_keys($struct);
	}
	
	private function compareWeight($a, $b) {
		return $b->weight - $a->weight;
	}
	
	private function compareDistance($a, $b) {
		return $a->distance - $b->distance;
	}
	
}
