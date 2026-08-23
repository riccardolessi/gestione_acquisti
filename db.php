<?php
// Vecchia configurazione del database NON USARE
$config = parse_ini_file(__DIR__ . "/config.ini");

// Creazione della connessione
$conn = new mysqli($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_NAME"]);

// Controllo della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

?>