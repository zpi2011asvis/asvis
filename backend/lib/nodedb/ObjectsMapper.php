<?php

namespace asvis\lib\nodedb;

require_once 'Structure.php';

use asvis\lib\nodedb\Structure as Structure;

/**
 * Mapper class, responsible for transforming JSON to Objects.
 */
class ObjectsMapper {
	
	private $_json;
	private $_structure;
	private $_isParsed;
	
	private $_rootNum;
	
	/**
	 * @param string $json
	 * @param int $rootNum
	 */
	public function __construct($json, $rootNum) {
		$this->_json = $json;
		$this->_rootNum = $rootNum;
		$this->_isParsed = false;
		$this->_structure = null;
	}
	
	/**
	 * Parses JSON
	 * @return Graph instance of asvis\Graph
	 */
	public function parse() {
		if ($this->_isParsed) {
			return new Structure($this->_structure);
		}
		
		$this->_parseNode($this->_json->structure);
		$this->_isParsed = true;
		
		return new Structure($this->_structure, $this->_json->weight_order, $this->_json->distance_order);
	}
	
	
	private function _parseNode($structure) {
		foreach ($structure as $num => $object) {
			if (is_object($object)) {
					$this->_structure[$num] = $object;
			}
		}
	}
	
}
