<?php

namespace asvis\lib;

/**
 * Debug helper class
 */
class H {
	
	/**
	 * Var_dumps supplied parameter surrounding it by <pre> tags.
	 * @param mixed $sth anything
	 */
	public static function pre($sth) {
		echo '<pre>';
		var_dump($sth);
		echo '</pre>';
	}
}