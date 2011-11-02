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
		
// 		echo 'Nodes: '.count($this->_asNodes).PHP_EOL;
// 		echo 'Conns: '.count($this->_asConns).PHP_EOL.PHP_EOL;
		
		foreach ($this->_asNodes as $node) {
			$this->initStructureRecord($node);
		}
		
		if(is_array($this->_asConns)) {
			foreach ($this->_asConns as $conn) {			
				$nodeFrom	= $this->getNodeFrom($conn);
				$nodeTo		= $this->getNodeTo($conn);
	
				if ($nodeFrom !== null and $nodeTo !== null) {
					$dir = $conn->up ? 'up' : 'down';
					$this->_structure[$nodeFrom->num][$dir][] = $nodeTo->num;
				}
			}
		}

		$this->countConnections();
	}
	
	private function getNodeFrom($asconn) {
		$in = $asconn->in;
		
		if (
			is_object($in) and
			array_key_exists($in->num, $this->_structure)
		) {
			return $in;
		}
		elseif (
			is_string($in) and
			array_key_exists($in, $this->_asNodes)
		) {
			return $this->_asNodes[$in];
		}
		
		return null;
	}
	
	private function getNodeTo($asconn) {
		$out = $asconn->out;

		if (
			is_object($out) and
			array_key_exists($out->num, $this->_structure)
		) {
			return $out;
		}
		elseif (
			is_string($out) and
			array_key_exists($out, $this->_asNodes)
		) {
			return $this->_asNodes[$out];
		}
		
		return null;
	}
	
	private function initStructureRecord($node) {
		$this->_structure[$node->num] = array(
			'up' => array(),
			'down' => array(),
			'count' => 0
		);
	}
	
	private function countConnections() {
		foreach ($this->_structure as $num => $node) {
			$count = count($node['up']) + count($node['down']);
			$this->_structure[$num]['count'] = $count;
		}
	}

	public function calculateDistances($root_node, $max_distance) {
	}
}
