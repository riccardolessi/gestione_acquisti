<?php
require_once('fattura2.php');

class ImportaFatture {
    private $directory;
    private $fattura;
    private $db;

    public function __construct($db) {
        $this->directory = rtrim('uploads/'); // Assicura che il percorso abbia uno slash finale
        $this->fattura = new Fattura($db);
    }


    public function importa() {
        if (!is_dir($this->directory)) {
            die("Errore: la cartella non esiste.");
        }

        // Ottieni tutti i file XML dalla cartella
        $files = glob($this->directory . "*.xml");

        if (empty($files)) {
            echo "Nessun file XML trovato nella cartella.";
            return;
        }

        foreach ($files as $file) {
            echo "Importazione del file: $file <br>";
            
            // Metodo di importazione)
            $this->fattura->importaFattura($file);
        }
    }
}
?>