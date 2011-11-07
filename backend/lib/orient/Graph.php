<?php

namespace asvis\lib\orient;

require_once 'Structure.php';

use asvis\lib\orient\Structure as Structure;

class Graph extends Structure {
	
	public function toJSON() {
		$toEncode = array();
		
		foreach ($this->_structure as $num => $node) {
			$toEncode['strucrue'][$num]['connections'] = $node->out;
			$toEncode['strucrue'][$num]['weight'] = $node->weight;
			$toEncode['strucrue'][$num]['distance'] = $node->distance;
		}
		
		$toEncode['weight_order']	= $this->_getWeightOrder();
		$toEncode['distance_order']	= $this->_getDistanceOrder();
		
		return json_encode($toEncode);
	}
	
	private function _getWeightOrder() {
		$struct = $this->_structure;
		
		uasort($struct, array('asvis\lib\orient\Graph', 'compareWeight'));
		
		$result = array();

		foreach ($struct as $num => $node) {
			$result[] = $num;
		}
		
		return $result;
	}
	
	private function _getDistanceOrder() {
		$struct = $this->_structure;
	
		uasort($struct, array('asvis\lib\orient\Graph', 'compareDistance'));
	
		$result = array();
	
		foreach ($struct as $num => $node) {
			$result[] = $num;
		}
	
		return $result;
	}
	
	private function compareWeight($a, $b) {
		return $b->weight - $a->weight;
	}
	
	private function compareDistance($a, $b) {
		return -($b->distance - $a->distance);
	}
	
}













