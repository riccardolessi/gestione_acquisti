<?php
declare(strict_types=1);
$config = parse_ini_file("config.ini");

class Database
{
    private static ?mysqli $connection = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Restituisce la connessione mysqli (singleton)
     */
    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {

            self::$connection = new mysqli($config["host"], $config["user"], $config["pass"], $config["name"]);

            if (self::$connection->connect_error) {
                throw new RuntimeException(
                    'Errore connessione DB: ' . self::$connection->connect_error
                );
            }

            // Charset corretto (fondamentale)
            self::$connection->set_charset('utf8mb4');
        }

        return self::$connection;
    }

    /**
     * Chiude la connessione (opzionale)
     */
    public static function close(): void
    {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
