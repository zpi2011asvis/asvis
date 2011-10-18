<?php

namespace asvis\lib;

class Engine {
	/**
	 * @var DB
	 */
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
	
	public static function structureGraph($nodeNum, $depth) {
		$result = self::$_db->loadGraph($nodeNum, '*:'.($depth*2));
		
		$origin = $result['origin'];		
		$origin->parse();
		
		$connected = $result['connected'];
		
		$nodes = array();		
		
		$nodes[$origin->data->num] = self::parseASNode($origin, $connected);
		
		foreach ($connected as $object) {
			if($object->__get('className') === 'ASNode') {
				$nodes[$object->data->num] = self::parseASNode($object, $connected);
			}
		}
	
		return $nodes;
	}
	
	private static function parseASNode($asNode, $connected) {
		$connections_up = array();
		$connections_down = array();
		
		foreach ($asNode->data->out as $link) {
			if(!isset($connected[$link->get()])) {
				continue; // podlinkowany element jest spoza zakresu $depth
			}
			$asconn = $connected[$link->get()];
			$asconn->parse();
				
			$linkedASnode = $connected[$asconn->data->out->get()];
			$linkedASnode->parse();
				
			if($asconn->data->up === true) {
				$connections_up[] = $linkedASnode->data->num;
			} else {
				$connections_down[] = $linkedASnode->data->num;
			}
		}
		
		return array(
				'connections_up' => '['.implode(',', $connections_up).']',
				'connections_down' => '['.implode(',', $connections_down).']',
		);
	}
}

