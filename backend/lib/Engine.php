<?php

namespace asvis\lib;

interface Engine {
	
	public function nodesFind($num);
	public function nodesMeta($nodes);
	public function structureGraph($nodeNum, $depth);
	public function structureTree($nodeNum, $depth);
	
}