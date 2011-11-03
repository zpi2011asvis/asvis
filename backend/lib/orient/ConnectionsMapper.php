<?php

namespace asvis\lib\orient;

class OrientConnectionsMapper {

	/**
	 * @var array()
	 */
	private $_asConns;

	/**
	 * @var array()
	 */
	private $_asNodes;

	private $_rootNum;
	
	private $_isParsed;
	
	private $_structure;
	
	public function __construct($objectMapper, $rootNum) {
		$this->_asNodes = $objectMapper->getNodes();
		$this->_asConns = $objectMapper->getConns();
		$this->_rootNum = $rootNum;
		
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
		
		if (is_array($this->_asConns)) {
			foreach ($this->_asConns as $conn) {
				$nodeFrom	= $this->getNodeFromConn($conn, 'in');
				$nodeTo		= $this->getNodeFromConn($conn, 'out');
	
				if ($nodeFrom !== null and $nodeTo !== null) {
					$dir = $conn->up ? 'up' : 'down';
					array_push($this->_structure[$nodeFrom->num]->$dir, $nodeTo->num);
				}
			}
		}

		$this->countConnections();
		$this->calculateDistances();

		$this->_isParsed = true;
	}

	/*
	 * @param $dir ('in', 'out')
	 */
	private function getNodeFromConn($asconn, $dir) {
		$node = $asconn->$dir;
		$atRID = '@rid';
		$rid = null;

		if (is_object($node)) {
			$rid = $node->$atRID;
		}
		else {
			$rid = $node;
		}

		if (array_key_exists($rid, $this->_asNodes)) {
			return $this->_asNodes[$rid];
		}
		
		return null;
	}
	
	private function initStructureRecord($node) {
		$obj = new \StdClass;
		$obj->up = array();
		$obj->down = array();
		$obj->weight = 0;
		$obj->distance = null;

		$this->_structure[$node->num] = $obj;
	}
	
	private function countConnections() {
		foreach ($this->_structure as $num => $node) {
			$count = count($node->up) + count($node->down);
			$this->_structure[$num]->weight = $count;
		}
	}

	private function calculateDistances() {
		$heap = array();
		
		// begin from root node
		$heap[] = array('node' => $this->_structure[$this->_rootNum], 'distance' => 0);

		while (count($heap) > 0) {
			$current = array_shift($heap);
			$node = $current['node'];
			$distance = $current['distance'];
			
			if (is_null($node->distance)) {
				$node->distance = $distance;
				$distance += 1;
				foreach ($node->up as $num) {
					$heap[] = array('node' => $this->_structure[$num], 'distance' => $distance);
				}
				foreach ($node->down as $num) {
					$heap[] = array('node' => $this->_structure[$num], 'distance' => $distance);
				}
			}
		}
	}
	
	public function getWeightOrder() {
		$this->mapConnections();
		
		uasort($this->_structure, array('asvis\lib\OrientConnectionsMapper', 'cmpByWeight'));
		
		return array_keys($this->_structure);
	}

	public function getDistanceOrder() {
		$this->mapConnections();
		
		uasort($this->_structure, array('asvis\lib\OrientConnectionsMapper', 'cmpByDistance'));
	
		return array_keys($this->_structure);
	}
	
	private function cmpByDistance($a, $b) {
		return $a->distance - $b->distance;
	}
	
	private function cmpByWeight($a, $b) {
		return $b->weight - $a->weight;
	}
	
}
