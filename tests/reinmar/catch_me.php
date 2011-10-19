<?php

namespace x;
require 'catch_me2.php';
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

try {
	\y\shittyCode();
}
catch (Exception $e) {
	echo 'Gotcha!';
}
