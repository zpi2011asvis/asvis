<?php

// require_once 'ASImporter.php';
require_once 'EvenNewerImporter.php';

$importer = new EvenNewerImporter();
$importer->import();

// OR
// new ASImporter(true);
