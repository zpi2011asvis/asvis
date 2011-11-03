<?php

namespace asvis\lib\orient;

class OrientObjectMapper {
	
	private $_origin;
	
	private $_asNodes;
	
	private $_asConns;
	
	private $_isParsed;
	
	public function __construct($origin) {
		$this->_origin = $origin;
		$this->_isParsed = false;
	}
	
	public function getNodes() {
		$this->map();
		return $this->_asNodes;
	}
	
	public function getConns() {
		$this->map();
		return $this->_asConns;
	}
	
	public function map() {
		if (!$this->_isParsed) {
			$this->mapObject($this->_origin);
			$this->_isParsed = true;
		}
	}
	
	private function mapObject($object) {
		if (!is_object($object)) {
			return;
		}
		
		$atClass = '@class';
		
		if ($object->$atClass === 'ASNode') {
			$this->mapNode($object);
		}
		elseif ($object->$atClass === 'ASConn') {
			$this->mapConn($object);
		}
	}
	
	private function mapNode($node) {
		// skip nodes that were somewere else in what orientdb returned
		if (!isset($node->num)) {
			return;
		}
		
		$atRID = '@rid';
		$this->_asNodes[$node->$atRID] = $node;
		
		if (isset($node->in)) {
			$in = $node->in;
		
			foreach ($in as $conn) {
				$this->mapObject($conn);
			}
		}
		
		if (isset($node->out)) {
			$out = $node->out;
		
			foreach ($out as $conn) {
				$this->mapObject($conn);
			}
		}
	}

	private function mapConn($conn) {
		// skip conns that were somewere else in what orientdb returned
		if (!isset($conn->out)) {
			return;
		}
		
		$atRID = '@rid';
		$this->_asConns[$conn->$atRID] = $conn;
		
		if (isset($conn->in)) {
			$in = $conn->in;
			
			$this->mapObject($in);
		}
		
		if (isset($conn->out)) {
			$out = $conn->out;
			
			$this->mapObject($out);
		}
	}
	
}















