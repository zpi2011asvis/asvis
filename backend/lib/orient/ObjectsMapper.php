<?php

namespace asvis\lib\orient;

require_once 'Graph.php';

use asvis\lib\orient\Graph as Graph;

class ObjectsMapper {
	
	private $_json;
	private $_structure;
	private $_isParsed;
	
	private $_rootNum;
	
	public function __construct($json) {
		$this->_json = $json;
		$this->_isParsed = false;
		$this->_structure = null;
	}
	
	public function parse() {
		if ($this->_isParsed) {
			return new Graph($this->_structure);
		}
		
		$_rootNum = null;
		$this->_parseNode($this->_json);
		$this->_resolveNodes();		
		$this->_calculateDistances();
		
		$this->_isParsed = true;
		return new Graph($this->_structure);
	}
	
	
	private function _parseNode($node) {
		if (!is_object($node)) {
			return;
		}
		
		if (isset($node->num)) {
			$this->_structure[$node->{'@rid'}] = $node;
		}		
		
		if(is_null($this->_rootNum)) {
			$this->_rootNum = $node->num;
		}
		
		if (isset($node->in)) {
			foreach ($node->in as $object) {
				$this->_parseNode($object);
			}
		}
		
		if (isset($node->out)) {
			foreach ($node->out as $object) {
				$this->_parseNode($object);
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
	
	private function _calculateDistances() {		
		$heap = array();

		// begin from root node
		$heap[] = array('node' => $this->_structure[$this->_rootNum], 'distance' => 0);
		
		while (count($heap) > 0) {
			$current = array_shift($heap);
			$node = $current['node'];
			$distance = $current['distance'];
		
			if (!isset($node->distance)) {
				$node->distance = $distance;
				$distance += 1;
				foreach ($node->out as $num) {
					$heap[] = array('node' => $this->_structure[$num], 'distance' => $distance);
				}
				foreach ($node->in as $num) {
					$heap[] = array('node' => $this->_structure[$num], 'distance' => $distance);
				}
			}
		}
	}
	
}















