<?php

namespace asvis\lib\orient;

require_once 'Graph.php';

use asvis\lib\orient\Graph as Graph;

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
			return new Graph($this->_structure);
		}
		
		$this->_parseNode($this->_json);
		$this->_resolveNodes();		
		
		$this->_isParsed = true;
		return new Graph($this->_structure);
	}
	
	
	private function _parseNode($node, $depth = 0) {
		if (!is_object($node)) {
			return;
		}
		
		if (isset($node->num)) {
			$node->depth = $depth;
			$this->_structure[$node->{'@rid'}] = $node;
		}
		
		if(isset($node->in)) {
			foreach ($node->in as $object) {
				$this->_parseNode($object, $depth + 1);
			}
		}
		
		if(isset($node->out)) {
			foreach ($node->out as $object) {
				$this->_parseNode($object, $depth + 1);
			}
		}
	}
	
	private function _resolveNodes() {
		foreach ($this->_structure as $rid => $node) {
			$this->_resolveNode($rid);
		}
		
		$toRemove = array();
		
		foreach ($this->_structure as $rid => $node) {
			$this->_structure[$node->num] = $node;
			$toRemove[] = $rid;
		}
		
		foreach ($toRemove as $rid) {
			unset($this->_structure[$rid]);
		}
	}
	
	private function _resolveNode($nodeRID) {
		$this->_resolveIns($nodeRID);	
		$this->_resolveOuts($nodeRID);		
	}
	
	private function _resolveIns($nodeRID) {
		if (!isset($this->_structure[$nodeRID]->in)) {
			$this->_structure[$nodeRID]->in = array();
			return;
		}
		
		$count = count($this->_structure[$nodeRID]->in);
		
		for ($i = 0; $i < $count; $i++) {
			
			$linked = $this->_structure[$nodeRID]->in[$i];
			
			if (is_string($linked)) {
				if (!isset($this->_structure[$linked])) {
					unset($this->_structure[$nodeRID]->in[$i]);
				} else {					
					$this->_structure[$nodeRID]->in[$i] = 
						$this->_structure[$linked]->num;
				}
			} else {
				if (!isset($this->_structure[$linked->{'@rid'}])) {
					unset($this->_structure[$nodeRID]->in[$i]);
				} else {
					$this->_structure[$nodeRID]->in[$i] = $linked->num;
				}
			}
			
			
			
		}
		
		
		
	}
	
	private function _resolveOuts($nodeRID) {	
		if (!isset($this->_structure[$nodeRID]->out)) {
			$this->_structure[$nodeRID]->out = array();
			return;
		}
		
		$count = count($this->_structure[$nodeRID]->out);
		
		for ($i = 0; $i < $count; $i++) {
			
			$linked = $this->_structure[$nodeRID]->out[$i];
			
			if (is_string($linked)) {
				if (!isset($this->_structure[$linked])) {
					unset($this->_structure[$nodeRID]->out[$i]);
				} else {					
					$this->_structure[$nodeRID]->out[$i] = 
						$this->_structure[$linked]->num;
				}
			} else {
				if (!isset($this->_structure[$linked->{'@rid'}])) {
					unset($this->_structure[$nodeRID]->out[$i]);
				} else {
					$this->_structure[$nodeRID]->out[$i] = $linked->num;
				}
			}
			
		}
	}
	
}















