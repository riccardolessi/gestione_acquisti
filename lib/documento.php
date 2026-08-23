<?php

require_once('./db.php');

class Documento {
    private $conn;
    public $id_fornitore;
    public $numero_fattura;
    public $data_fattura;

    public function _construct($id_fornitore, $numero_fattura, $data_fattura) {
        $this->id_fornitore = $id_fornitore;
        $this->numero_fattura = $numero_fattura;
        $this->data_fattura = $data_fattura;
    }

    public function verificaPresenza() {
        global $conn;

        // Query SQL
        $sql = "SELECT id FROM documenti WHERE id_fornitore = ? AND numero_documento = ? AND data_documento = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Lega il parametro
            $stmt->bind_param("iss", $this->id_fornitore, $this->numero_fattura, $this->data_fattura);

            // Esegui la query
            $stmt->execute();

            // Ottieni il risultato
            $stmt->store_result();

            // Se il documento esiste, restituire l'ID
            if ($stmt->num_rows ==1) {
                $stmt->bind_result($id);
                $stmt->fetch();
                $stmt->close();
                return $id; // Restituisce l'ID
            } else {
                $stmt->close();
                return false; // Il documento non è presente
            }
        } else {
            // Se la query non può essere preparata
            echo "Errore nella preparazione della query: " . $conn->error;
            return false;
        }
    }

    public function inserisciDocumento() {
        global $conn;

        $sql ="INSERT INTO documenti (id_fornitore, numero_documento, data_documento) VALUES (?, ?, ?)";

        // Prepara lo statement
        if ($stmt = $conn->prepare($sql)) {
            // Lega i parametri
            $stmt->bind_param("iss", $this->id_fornitore, $this->numero_fattura, $this->data_fattura);

            // Esegui la query
            if ($stmt->execute()) {
                // Ottieni l'ID dell'ultimo inserimento
                $last_id = $conn->insert_id;
                $stmt->close();
                return $last_id; // Restituisce l'ID dell'inserimento
            } else {
                echo "Errore nell'inserimento: " . $stmt->error;
                return false;
            }

            // Chiudi lo statement
            $stmt->close();
        } else {
            echo "Errore nella preparazione della query: " . $conn->error;
            return false;
        }
    }
}

?>