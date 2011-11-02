<?php

namespace asvis\lib;

class OrientObjectMapper {
	
	private $_origin;
	
	private $_asnodes;
	
	private $_asconns;
	
	private $_isParsed;
	
	public function __construct($origin) {
		$this->_origin = $origin;
		$this->_isParsed = false;
	}
	
	public function getNodes() {
		$this->map();
		return $this->_asnodes;
	}
	
	public function getConns() {
		$this->map();
		return $this->_asconns;
	}
	
	public function map() {
		if(!$this->_isParsed) {
			$this->mapObject($this->_origin, 0);
			$this->_isParsed = true;
		}
	}
	
	private function mapObject($object, $depth) {
		if(!is_object($object)) {
			return;
		}
		
		$atClass = '@class';
		
		$object->depth = $depth;
		
		if($object->$atClass === 'ASNode') {
			$this->mapNode($object, $depth);
		}
		
		if($object->$atClass === 'ASConn') {
			$this->mapConn($object, $depth);
		}
		
		if($object->$atClass === 'ASPool') {
			$this->mapPool($object, $depth);
		}
		
	}
	
	private function mapNode($node, $depth) {
		if (!isset($node->name) ) {
			return;
		}
		
		$atRID = '@rid';
		$this->_asnodes[$node->$atRID] = $node;
		
		if ( isset($node->in) ) {
			$in = $node->in;
		
			foreach ($in as $conn) {
				$this->mapObject($conn, $depth+1);
			}
		}
		
		if ( isset($node->out) ) {
			$out = $node->out;
		
			foreach ($out as $conn) {
				$this->mapObject($conn, $depth+1);
			}
		}
	}

	private function mapConn($conn, $depth) {
		if (!isset($conn->up) ) {
			return;
		}
		
		$atRID = '@rid';
		$this->_asconns[$conn->$atRID] = $conn;
		
		if ( isset($conn->in) ) {
			$in = $conn->in;
			
			$this->mapObject($in, $depth+1);
		}
		
		if ( isset($conn->out) ) {
			$out = $conn->out;
			
			$this->mapObject($out, $depth+1);
		}
	}

	private function mapPool($pool, $depth) {
	// TODO
	}
	
	public function getDepthOrder($nodes) {
		uasort($nodes, array('asvis\lib\OrientObjectMapper', 'cmpByDepth'));

		$result = array();
		foreach ($nodes as $rid => $object) {
			$result[] = $object->num;
		}
		
		return $result;
	}
	
	public function getWeightOrder($structure) {
		uasort($structure, array('asvis\lib\OrientObjectMapper', 'cmpByWeight'));
		
		$result = array();
		foreach ($structure as $num => $object) {
			$result[] = $num;
		}
		
		return $result;
	}
	
	private function cmpByDepth($a, $b) {
		if ($a->depth == $b->depth) {
			return 0;
		}
		 
		return ($a->depth < $b->depth) ? -1 : 1;
	}
	
	private function cmpByWeight($a, $b) {
		if ( ((int)$a['count']) == ((int)$b['count']) ) {
			return 0;
		}
			
		return ( ((int)$a['count']) > ((int)$b['count']) ) ? -1 : 1;
	}
	
}















