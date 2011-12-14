<?php

namespace asvis\lib\nodedb;

/**
 * Object representation of Autonomous System.
 */
class Node {
	
	/**
	 * AS number
	 * @var int
	 */
	private $num;
	
	/**
	 * Outgoing connections
	 * @var array of int
	 */
	public $out;
	
	/**
	 * Incoming connections
	 * @var array of int
	 */
	public $in;
	
	/**
	 * Distance from root node
	 * @var int
	 */
	public $distance;
	
	/**
	 * Node weight (sum of incoming and outgoing connections count)
	 * @var int
	 */
	public $weight;
	
	/**
	 * @param string $rid
	 * @param int $num
	 * @param string $name
	 * @param array of int $out
	 * @param array of int $in
	 * @param int $distance
	 */
	public function __construct($num = null, $out = null, $in = null, $distance = null, $weight = null) {
		$this->num	= $num;
		
		$this->out	= $out;
		$this->in	= $in;
		
		$this->distance	= $distance;
		$this->weight = $weight;
	}
	
	public function __get($name) {
		if(isset($this->$name)) {
			return $this->$name;
		} else {
			return null;
		}
	}
	
	public function __set($name, $value) {
		if(isset($this->$name)) {
			$this->$name = $value;
		}
	}
	
}
