<?php

namespace asvis\lib;

class OrientObjectMapper {
	
	/**
	* @var array()
	*/
	private $_asConns;
	
	/**
	* @var array()
	*/
	private $_asNodes;
	
	private $_origin;
	
	private $_isParsed;

	private $_depth;

	public $badNodes;
	
	public function __construct($origin = null, $depth) {
		$this->_asConns = array();
		$this->_asNodes = array();
		$this->_depth = $depth;
		$this->badNodes = array();
		
		if (is_null($origin)) {
			$this->_isParsed = true;
		} else {
			$this->setOrigin($origin);
		}
		
	}
	
	public function getNodes() {
		$this->map();
		return $this->_asNodes;
	}
	
	public function getConns() {
		$this->map();
		return $this->_asConns;
	}
	
	public function setOrigin($origin) {
		$this->_origin = $origin;
		$this->_isParsed = false;
	}
	
	private function map() {
		if ($this->_isParsed) {
			return;
		}
		
		// don't know why it has to be +1... 
		// but it has to be because for e.g. 3/3 and 3/5
		// node 174 is on this level
		$this->mapObject($this->_origin, $this->_depth + 1);
		
		$this->_isParsed = true;
	}
	
	private function mapObject($object, $depth) {
		if ($depth < 0) {
			return;
		}
		if (!is_object($object)) {
			return;
		}

		$atClass	= '@class';
		$atRID		= '@rid';
		$objectClass = $object->$atClass;
		
		if ($objectClass === 'ASNode') {
			$this->mapNode($object, $depth);
		}
		
		if ($objectClass === 'ASConn') {
			$this->mapConn($object, $depth);
		}
	}
	
	private function mapNode($asnode, $depth) {
		if (!is_object($asnode)) {
			throw new \Exception('Thought it was redundant');
			return;
		}
	
		$atRID = '@rid';
		
		// initialize with big depth
		// will be usefull while calculating proper distance
		$asnode->distance = $this->_depth * 2;
		$this->_asNodes[$asnode->$atRID] = $asnode;
	
		/*
		// I threw inconns out from fetch plan
		// we hopefully don't need them
		if (isset($asnode->in)) {
			$in = $asnode->in;
	
			foreach ($in as $object) {
				$this->mapObject($object, $depth);
			}
		}*/
	
		if (isset($asnode->out)) {
			$out = $asnode->out;
	
			foreach ($out as $object) {
				$this->mapObject($object, $depth);
			}
		}
	}
	
	private function mapConn($asconn, $depth) {
		if (!is_object($asconn)) {
			throw new \Exception('Thought it was redundant');
			return;
		}
	
		$atRID = '@rid';
	
		// We push connection without checking if it goes to node which fits
		// into given depth.
		// This can gives us to much connections, but we need them because
		// we don't know if they link leafs or not.
		// OrientConnectionsMapper takes this into account.
		$this->_asConns[$asconn->$atRID] = $asconn;
	
		/*
		// probably don't need this - we traverse only by out fields
		if (isset($asconn->in)) {
			$this->mapObject($asconn->in, $depth);
		}
		*/
	
		if (isset($asconn->out)) {
			$this->mapObject($asconn->out, $depth - 1);
		}
	
	}
	
}















