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
	
	public function __construct($origin = null) {
		$this->_asConns = array();
		$this->_asNodes = array();
		
		if(is_null($origin)) {
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
		if($this->_isParsed) {
			return;
		}
		
		$this->mapObject($this->_origin);
		
		$this->_isParsed = true;
	}
	
	private function mapObject($object) {
		if( !is_object($object) ) {
			return;
		}
		
		$atClass	= '@class';
		$atRID		= '@rid';
		
		$objectClass = $object->$atClass;
		
		if($objectClass === 'ASNode') {
			$this->mapNode($object);
		}
		
		if($objectClass === 'ASConn') {
			$this->mapConn($object);
		}
	}
	
	private function mapNode($asnode) {
		if( !is_object($asnode) ) {
			return;
		}
	
		$atRID = '@rid';
	
		$this->_asNodes[$asnode->$atRID] = $asnode;
	
		if(isset($asnode->in)) {
			$in = $asnode->in;
	
			foreach ($in as $object) {
				$this->mapObject($object);
			}
		}
	
		if(isset($asnode->out)) {
			$out = $asnode->out;
	
			foreach ($out as $object) {
				$this->mapObject($object);
			}
		}
	}
	
	private function mapConn($asconn) {
		if( !is_object($asconn) ) {
			return;
		}
	
		$atRID = '@rid';
	
		$this->_asConns[$asconn->$atRID] = $asconn;
	
		if(isset($asconn->in)) {
			$this->mapObject($asconn->in);
		}
	
		if(isset($asconn->out)) {
			$this->mapObject($asconn->out);
		}
	
	}
	
}















