<?php

namespace asvis\lib;

class Engine {
	static $_db = null;

	public static function init() {
		//?
	}

	public static function nodesFind($db, $number) {
		$query = 'SELECT FROM ASNode WHERE name LIKE "'.$number.'%"';
		$result = $db->query($query);
		
		$nodes = array();
		
		if($result) {
			foreach ($result as $oDBRecord) {			
				$oDBRecord->parse();
				$num = $oDBRecord->data->num;
				$name = $oDBRecord->data->name;
				$nodes[$num] = array(
					'name' => $name
				);
			}
		}

		return $nodes;			
	}
}
