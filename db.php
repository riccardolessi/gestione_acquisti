<?php
// Configurazione del database
$host = 'localhost'; // L'host predefinito di MAMP
$username = 'root'; // Username predefinito di MAMP
$password = 'root'; // Password predefinita di MAMP
$dbname = 'inventario'; // Sostituisci con il nome del tuo database

// Creazione della connessione
$conn = new mysqli($host, $username, $password, $dbname);

// Controllo della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Imposta il set di caratteri a utf8mb4
if (!$conn->set_charset("utf8mb4")) {
    printf("Errore nel caricamento del set di caratteri utf8mb4: %s\n", $conn->error);
    exit();
}

?>