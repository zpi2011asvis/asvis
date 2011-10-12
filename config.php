<?php

namespace asvis;

class Config {
	private static $opts = array(
		'env'			=> 'dev', //or 'prod'
		'example'		=> 'value',
		
		'mysql_db_host'	=> 'localhost',
		'mysql_db_name'	=> 'asmap',
		'mysql_db_user'	=> 'root',
		'mysql_db_pass'	=> 'gloowa178',
	);

	public static function get($name) {
		return self::$opts[$name];
	}
}
