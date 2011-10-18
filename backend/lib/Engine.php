<?php

namespace asvis\lib;

class Engine {
	protected static $_db = null;

	public static function init($db) {
		self::$_db = $db;
	}

	public static function nodesFind($number) {
		$query = 'SELECT FROM ASNode WHERE num.asString() LIKE "'.$number.'%"';
		$result = self::$_db->query($query);
		
		$nodes = array();
		
		if ($result) {
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
