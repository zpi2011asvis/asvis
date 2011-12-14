<?php

namespace asvis\lib;

interface Engine {
	
	/**
	 * Returns array of node_num => "node_name" which numbers begin
	 * like given param, i.e.:
	 * 12 gives nodes with numbers:
	 * 12, 120, ..., 1252352355234, ...
	 * 
	 * @param int $num ASNode number
	 */
	public function nodesFind($num);
	
	/**
	 * Returns array of meta data for given nodes numbers.
	 * 
	 * @param Array $number e.g. array(1234,2345,52345,234523)
	 */
	public function nodesMeta($numbers);

	/**
	 * Returns an array of meta data for connections
	 * taken from in and out fields of given node.
	 * 
	 * @param int $for_number ASNode number
	 */
	public function connectionsMeta($for_node);
	
	/**
	 * Returns array of nodes and their connections for given
	 * origin node number and depth of recurson.
	 * $depth <1,inf>
	 * 
	 * @param int $nodeNum ASNode number
	 * @param int $depth recursion depth
	 */
	public function structureGraph($nodeNum, $depth);
	
	/**
	 * Returns array of nodes and their connections for given
	 * origin node number and depth of recurson. Returned data
	 * represents a tree which begins in root node (nodeNum) and
	 * includes only nodes with single incoming link.
	 * 
	 * @param int $nodeNum ASNode number
	 * @param int $height tree height
	 * @param string $dir search direction; 'in','out' or 'both'
	 */
	public function structureTree($nodeNum, $height, $dir);
	
	/**
	 * Returns array of nodes and their connections representing 
	 * the shortest path between two nodes.
	 * 
	 * @param int $num_start ASNode number
	 * @param int $num_end ASNode number
	 * @param string $dir search direction; 'in','out' or 'both'
	 */
	public function structurePath($num_start, $num_end, $dir);
	
}
