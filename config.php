<?php

namespace asvis;

class Config {
	private static $opts = array(
		'env'			=> 'dev', //or 'prod'
		'example'		=> 'value'
	);

	public static function get($name) {
		return self::$opts[$name];
	}
}
