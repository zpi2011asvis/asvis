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
			$toEncode['structure'][$num]['in'] = array();
			$toEncode['structure'][$num]['out'] = array();
			
			foreach ($node->in as $linkedNum) {				
				$toEncode['structure'][$num]['in'][] = $linkedNum;
			}
			
			foreach ($node->out as $linkedNum) {				
				$toEncode['structure'][$num]['out'][] = $linkedNum;
			}
		}
		
		return $toEncode;
	}
	
}