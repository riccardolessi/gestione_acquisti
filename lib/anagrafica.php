<?php

require_once('./db.php');

class AnagraficaFornitore {
    private $conn;
    public $fornitore;
    public $partita_iva;
    public $codice_fiscale;

    public function __construct($file) {
        $this->fornitore = (string) $file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->Anagrafica->Denominazione;
        $this->partita_iva = (string) $file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $this->codice_fiscale;
        if (isset($file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->CodiceFiscale)) {
            $this->codice_fiscale = (string) $file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->CodiceFiscale;
        } else {
            $this->codice_fiscale = $this->partita_iva;
        }
    }

    public function getDati() {
        return [
            'fornitore' => $this->fornitore,
            'partita_iva' => $this->partita_iva,
            'codice_fiscale' => $this->codice_fiscale
        ];
    }

    public function insertFornitore() {
        $fornitorePresente = $this->verificaEsistenza($this->partita_iva);
        if ($fornitorePresente == false) {
            $fornitore = $this->inserisciDB($this->fornitore, $this->partita_iva, $this->codice_fiscale);
            return $fornitore;
            echo " ha attivato il db";
        } else {
            return $fornitorePresente;
            echo " non ha attivato il db";
        } 
    }

    private function verificaEsistenza($partita_iva) {
        global $conn;

        // Prepara la query
        $sql = "SELECT id FROM fornitori WHERE partita_iva = ?";
        
        // Prepara lo statement
        if ($stmt = $conn->prepare($sql)) {
            // Lega il parametro
            $stmt->bind_param("s", $partita_iva);

            // Esegui la query
            $stmt->execute();

            // Ottieni il risultato
            $stmt->store_result();

            // Se la partita IVA esiste, restituire l'ID
            if ($stmt->num_rows ==1) {
                $stmt->bind_result($id);
                $stmt->fetch();
                $stmt->close();
                return $id; // Restituisce l'ID
            } else {
                $stmt->close();
                return false; // La partita IVA non è presente
            }
        } else {
            // Se la query non può essere preparata
            echo "Errore nella preparazione della query: " . $conn->error;
            return false;
        }
    }

    private function inserisciDB($denominazione, $partita_iva, $codice_fiscale) {
        global $conn;

        $sql ="INSERT INTO fornitori (denominazione, partita_iva, codice_fiscale) VALUES (?, ?, ?)";

        // Prepara lo statement
        if ($stmt = $conn->prepare($sql)) {
            // Lega i parametri
            $stmt->bind_param("sss", $denominazione, $partita_iva, $codice_fiscale);

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