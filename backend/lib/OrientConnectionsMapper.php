<?php

namespace asvis\lib;

class OrientConnectionsMapper {

	/**
	 * @var array()
	 */
	private $_asConns;

	/**
	 * @var array()
	 */
	private $_asNodes;
	
	private $_isParsed;
	
	private $_structure;
	
	public function __construct($nodes, $conns) {
		$this->_asNodes = $nodes;
		$this->_asConns = $conns;
		
		$this->_isParsed = false;
		$this->_structure = null;
	}
	
	public function getConnectionsMap() {
		$this->mapConnections();
		return $this->_structure;
	}
	
	private function mapConnections() {
		if ($this->_isParsed) {
			return;
		}
		
		foreach ($this->_asNodes as $node) {
			$this->initStructureRecord($node);
		}

		//$this->debug_checkFixBrokenConns();
		
		foreach ($this->_asConns as $conn) {
			$nodeFrom	= $this->getNodeFrom($conn);
			$nodeTo		= $this->getNodeTo($conn);

			$dir = $conn->up ? 'up' : 'down';
		
			$this->_structure[$nodeFrom->num][$dir][] = $nodeTo->num;
		}
	}
	
	private function getNodeFrom($asconn) {
		$nodeFrom = null;
			
		if (is_object($asconn->in)) {
			$nodeFrom = $asconn->in;
		}
			
		if (is_string($asconn->in)) {
			$nodeFrom = $this->_asNodes[$asconn->in];
		}
		
		return $nodeFrom;
	}
	
	private function getNodeTo($asconn) {
		$nodeTo = null;
		
		if (is_object($asconn->out)) {
			$nodeTo = $asconn->out;
		}
		
		if (is_string($asconn->out)) {
			$nodeTo = $this->_asNodes[$asconn->out];
		}
		
		return $nodeTo;
	}
	
	private function initStructureRecord($node) {
		$this->_structure[$node->num] = array(
			'up' => array(),
			'down' => array(),
			'count' => 0,
			'distance' => $node->distance
		);
	}
	
	private function countConnections() {
		foreach ($this->_structure as $num => $node) {
			$count = count($node['up']) + count($node['down']);
			$this->structure[$num]['count'] = $count;
		}
	}
	
	/*
	 * Niektóre fetchplany zwracją ASConny bez pól in/out (WTF?!)
	 * to się chyba dzieje w sytuacji:
	 * detpth-1    depth  
	 * ASNode      ASConn <- depth ograniczył wczytanie ASNode'a więc ASConn ma pusty link.
	 */
	private function debug_checkFixBrokenConns($verbose = false) {
		$atRID = '@rid';
		$brokenConns = array();
		
		foreach ($this->_asConns as $conn) {
			if (!(isset($conn->in) && isset($conn->out))) {
				$brokenConns[] = $conn;
			}
		}
		
		if ($verbose) {
			echo 'Found '.count($brokenConns).' broken connections (in '.count($this->_asConns).' total)';
		}
		
		foreach ($brokenConns as $conn) {
			unset($this->_asConns[$conn->$atRID]);
		}
		
	}
}
