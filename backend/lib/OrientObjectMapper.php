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
			$this->mapObject($this->_origin);
			$this->_isParsed = true;
		}
	}
	
	private function mapObject($object) {
		if(!is_object($object)) {
			return;
		}
		
		$atClass = '@class';
		
		if($object->$atClass === 'ASNode') {
			$this->mapNode($object);
		}
		
		if($object->$atClass === 'ASConn') {
			$this->mapConn($object);
		}
		
		if($object->$atClass === 'ASPool') {
			$this->mapPool($object);
		}
		
	}
	
	private function mapNode($node) {
		if (!isset($node->name) ) {
			return;
		}
		
		$atRID = '@rid';
		$this->_asnodes[$node->$atRID] = $node;
		
		if ( isset($node->in) ) {
			$in = $node->in;
		
			foreach ($in as $conn) {
				$this->mapObject($conn);
			}
		}
		
		if ( isset($node->out) ) {
			$out = $node->out;
		
			foreach ($out as $conn) {
				$this->mapObject($conn);
			}
		}
	}

	private function mapConn($conn) {
		if (!isset($conn->up) ) {
			return;
		}
		
		$atRID = '@rid';
		$this->_asconns[$conn->$atRID] = $conn;
		
		if ( isset($conn->in) ) {
			$in = $conn->in;
			
			$this->mapObject($in);
		}
		
		if ( isset($conn->out) ) {
			$out = $conn->out;
			
			$this->mapObject($out);
		}
	}

	private function mapPool($pool) {
	// TODO
	}
}















