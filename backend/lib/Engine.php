<?php

namespace asvis\lib;

interface Engine {
	
	/**
	 * Returns array of node_num => "node_name" which numbers begin
	 * like given param, i.e.:
	 * 12 gives nodes with numbers:
	 * 12, 120, ..., 1252352355234, ...
	 * 
	 * @param int $num
	 */
	public function nodesFind($num);
	
	/**
	 * Returns array of meta data for given nodes.
	 * $nodes param should be formatted:
	 * "[num1,num2,num3]"
	 * i.e.:
	 * "[1234,2345,52345,234523]"
	 * 
	 * @param string $nodes
	 */
	public function nodesMeta($nodes);
	
	/**
	 * Returns array of nodes and their connections for given
	 * origin node number and depth of recurson.
	 * $depth <1,inf>
	 * 
	 * @param int $nodeNum
	 * @param int $depth
	 */
	public function structureGraph($nodeNum, $depth);
	
	/**
	 * Returns array of nodes and their connections for given
	 * origin node number and depth of recurson. Returned data
	 * represents a tree which begins in root node (nodeNum) and
	 * includes only nodes with single incoming link.
	 * 
	 * @param int $nodeNum
	 * @param int $height
	 */
	public function structureTree($nodeNum, $height);
	
	/**
	 * Returns array of nodes and their connections representing 
	 * the shortest path between two nodes.
	 * 
	 * @param int $num_start
	 * @param int $num_end
	 */
	public function structurePath($num_start, $num_end);
	
}
