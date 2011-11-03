<?php

namespace asvis\lib;

class ASNode {
	
	/**
	 * Resource IDentifier z Orienta
	 * @var string
	 */
	public $rid;
	
	/**
	 * Numer Systemu Autonomicznego
	 * @var int
	 */
	public $num;
	
	/**
	 * Nazwa Systemu Autonomicznego
	 * @var string
	 */
	public $name;
	
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
	 * Konstruktor domyślny - wypełnia pola
	 * klasy pustymi stringami, zerami itd.
	 */
	public function __construct() {
		$this->rid	= '';
		$this->num	= 0;
		$this->name	= '';
		
		$this->out	= array();
		$this->in	= array();
		
		$this->weight	= 0;
		$this->distance	= 0;
	}
	
	/**
	 * Oblicza wagę węzła.
	 */
	public function calculateWeight() {
		$this->weight = count($this->out) + count($this->in);
	}
	
}