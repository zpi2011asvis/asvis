<?php

// require_once 'ASImporter.php';
require_once 'NewImporter.php';

$importer = new NewImporter();
$importer->import();

// OR
// new ASImporter(true);
