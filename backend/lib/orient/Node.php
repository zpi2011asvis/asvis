<?php

namespace asvis\lib\orient;

class Node {
	
	/**
	 * OrientDB Resource IDentifier
	 * @var string
	 */
	private $rid;
	
	/**
	 * AS number
	 * @var int
	 */
	private $num;
	
	/**
	 * AS name
	 * @var string
	 */
	private $name;
	
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
	public function __construct($rid = null, $num = null, $name = null, $out = null, $in = null, $distance = null) {
		$this->rid	= $rid;
		$this->num	= $num;
		$this->name	= $name;
		
		$this->out	= $out;
		$this->in	= $in;
		
		$this->distance	= $distance;
		$this->calculateWeight();
	}
	
	/**
	 * Calculates node weight, stores it internally and returns it.
	 */
	public function calculateWeight() {
		$this->weight = count($this->out) + count($this->in);
		return $this->weight;
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
