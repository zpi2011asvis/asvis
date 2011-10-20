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
//				$oDBRecord->parse();
				$num  = $oDBRecord->data->num;
				$name = $oDBRecord->data->name;
				$nodes[$num] = array(
					'name' => $name
				);
			}
		}

		return $nodes;			
	}
	
	public static function nodesMeta($numbers) {
		$num_array = explode(',', $numbers);
		
		$nodes = array();
		
		foreach ($num_array as $number) {
			$query = 'SELECT FROM ASNode WHERE num = '.$number;
			$result = self::$_db->query($query);
		
			$nodes = array();
			
			if ($result) {
				foreach ($result as $oDBRecord) {
					$num  = $oDBRecord->data->num;
					$name = $oDBRecord->data->name;
					//$network = $oDBRecord->data->network;
					//$mask = $oDBRecord->data->netmask;
					$nodes[$num] = array(
						'name' => $name,
						'pools' => array(
							//'network' => $network,
							//'netmask' => $netmask
						)
					);
				}
			}
		}

		return $nodes;
	}
	
	public static function structureGraph($nodeNum, $depth) {
		$result = self::$_db->loadGraph($nodeNum, '*:'.($depth*2));
		
		$origin = $result['origin'];
		$connected = $result['connected'];
		
//		$t = microtime(true);
/*
		$origin->parse();
		foreach ($connected as $object) {
			$object->parse();
		}
*/
//		echo (microtime(true) - $t) . PHP_EOL;

		$nodes = array();
		
		$nodes[$origin->data->num] = self::parseASNode($origin, $connected);
		
		foreach ($connected as $object) {
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
			if (!isset($connected[$link->get()])) {
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
			'up' => $connections_up,
			'down' => $connections_down,
			'count' => $connections_count
		);
	}
	
	private static function parseASConn($asConn, $connected) {
		$link = $asConn->data->out;
		$linkedASnode = $connected[$link->get()];
		
		return $linkedASnode;
	}
	
	
	static function compareParsedNodes($a, $b) {
		return $b['count'] - $a['count'];
	}
}

