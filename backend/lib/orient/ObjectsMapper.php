<?php

namespace asvis\lib\orient;

class ObjectsMapper {
	
	private $_json;
	private $_structure;
	private $_isParsed;
	
	public function __construct($json) {
		$this->_json = $json;
		$this->_isParsed = false;
		$this->_structure = null;
	}
	
	public function parse() {
		if ($this->_isParsed) {
			return;
		}
		
		$this->_parseNode($this->_json);
		
		
		echo '<pre>';
		var_dump($this->_structure);
	}
	
	private function _parseNode($node) {
		if (!is_object($node)) {
			return;
		}
		
		$this->_structure[$node->{'@rid'}] = $node;
		
		if(isset($node->in)) {
			foreach ($node->in as $object) {
				$this->_parseNode($object);
			}
		}
		
		if(isset($node->out)) {
			foreach ($node->out as $object) {
				$this->_parseNode($object);
			}
		}
	}
	
}















