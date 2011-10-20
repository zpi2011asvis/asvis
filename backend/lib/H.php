<?php

namespace asvis\lib;

class H {
	public static function pre($sth) {
		echo '<pre>';
		var_dump($sth);
		echo '</pre>';
	}
}