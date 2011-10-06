<?php

namespace asvis;

class Config {
	private static $opts = array(
		'example'		=> 'value'
	);

	public static function get($name) {
		return self::$opts[$name];
	}
}
