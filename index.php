<?php

if(!file_exists('db_offline.lock')) {	
	require_once 'frontend/index.php';
} else {
	require_once 'frontend/offline.php';
}
