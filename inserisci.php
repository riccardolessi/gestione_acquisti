<?php
require_once('./lib/importaFatture.php');
require_once('db.php');
// Uso della classe

$oggetto = new importaFatture($conn);
$oggetto->importa();
?>