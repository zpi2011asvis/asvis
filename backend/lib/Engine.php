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
				$num  = $oDBRecord->data->num;
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
		$connected = $result['connected'];
		
		$origin->parse();
		foreach ($connected as $object) {
			$object->parse();
		}
		
		$nodes = array();
		
		$nodes[$origin->data->num] = self::parseASNode($origin, $connected);
		
		foreach($connected as $object) {
			if($object->className === 'ASNode') {
				$nodes[$object->data->num] = self::parseASNode($object, $connected);
			}
		}

		uasort($nodes, array('asvis\lib\Engine','compareParsedNodes'));

		return $nodes;
	}
	
	public function structureTree($nodeNum, $depth) {
		
	}
	
	private static function parseASNode($asNode, $connected) {
		$connections_up = array();
		$connections_down = array();
		
		foreach ($asNode->data->out as $link) {
			if(!isset($connected[$link->get()])) {
				continue;
			}
			
			$asConn = $connected[$link->get()];
			$linkedNode = self::parseASConn($asConn, $connected);
			
			if($asConn->data->up === true) {
// 				echo 'ASConn '.$asConn->recordID.' is UP'.PHP_EOL;
				$connections_up[] = $linkedNode->data->num;
			} else {
// 				echo 'ASConn '.$asConn->recordID.' is DOWN'.PHP_EOL;
				$connections_down[] = $linkedNode->data->num;
			}
			
		}
		
		$connections_count = count($connections_up) + count($connections_down);
		
		return array(
			'connections_up' => $connections_up,
			'connections_down' => $connections_down,
			'connections_count' => $connections_count
		);
	}
	
	private static function parseASConn($asConn, $connected) {
		$link = $asConn->data->out;
		$linkedASnode = $connected[$link->get()];
		
		return $linkedASnode;
	}
	
	
	static function compareParsedNodes($a, $b) {
		if ($a['connections_count'] == $b['connections_count']) {
			return 0;
		}
	
		return ($a['connections_count'] > $b['connections_count']) ? -1 : 1;
	}
}

