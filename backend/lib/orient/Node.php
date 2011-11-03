<?php

namespace asvis\lib\orient;

class Node {
	
	/**
	 * Resource IDentifier z Orienta
	 * @var string
	 */
	private $rid;
	
	/**
	 * Numer Systemu Autonomicznego
	 * @var int
	 */
	private $num;
	
	/**
	 * Nazwa Systemu Autonomicznego
	 * @var string
	 */
	private $name;
	
	/**
	 * Połączenia wychodzące
	 * @var array
	 */
	public $out;
	
	/**
	 * Połączenia przychodzące
	 * @var array
	 */
	public $in;
	
	/**
	 * Dystans od węzła źródłowego
	 * @var int
	 */
	public $distance;
	
	/**
	 * Waga połączenia (suma połączeń wychodzących i wchodzących)
	 * @var unknown_type
	 */
	public $weight;
	
	/**
	 * Konstruktor wypełniający pola klasy
	 */
	public function __construct($rid = null, $num = null, $name = null, $out = null, $in = null, $weight = null, $distance = null) {
		$this->rid	= $rid;
		$this->num	= $num;
		$this->name	= $name;
		
		$this->out	= $out;
		$this->in	= $in;
		
		$this->weight	= $weight;
		$this->distance	= $distance;
	}
	
	/**
	 * Oblicza wagę węzła
	 */
	public function calculateWeight() {
		$this->weight = count($this->out) + count($this->in);
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