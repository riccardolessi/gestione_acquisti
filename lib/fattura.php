<?php

require_once('./db.php');
require_once('anagrafica.php');
require_once('documento.php');

class Fattura {
    private $conn;
    private $file;
    private $righe = [];
    public $numero_fattura;
    public $data_fattura;
    public $id_fornitore;

    public function __construct($file, $id_fornitore) {
        $this->file = $file;
        $this->numero_fattura = (string) $file->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero;
        $this->data_fattura = (string) $file->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data;
        $this->id_fornitore = $id_fornitore;
        $this->estraiRighe();
    }

    private function estraiRighe() {
        $linee = $this->file->FatturaElettronicaBody->DatiBeniServizi->DettaglioLinee;
        foreach ($linee as $linea) {
            $this->righe[] = [
                'descrizione' => (string) $linea->Descrizione,
                'quantita' => (float) $linea->Quantita,
                'prezzo_unitario' => (float) $linea->PrezzoUnitario,
                'totale' => (float) $linea->Quantita * (float) $linea->PrezzoUnitario
            ];
        }
    }

    public function verificaFattura() {
        $documento = new Documento($this->id_fornitore, $this->numero_fattura, $this->data_fattura);
        $presente = $documento->verificaPresenza();
        echo $presente;
        if ($presente == false) {
            $documento->inserisciDocumento();
        } else {
            echo 'non è false';
        }
    }
    

    private function verificaEsistenza($fornitore, $numero_fattura, $data_fattura) {
        global $conn;

        // Prepara la query
        $sql = "SELECT id FROM documenti WHERE fornitore = ?";
        
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

    public function getRighe() {
        return $this->righe;
    }

    public function stampaRighe() {
        echo '<hr>';
        echo "Fattura n. {$this->numero_fattura} del {$this->data_fattura}<br>";
        echo "ID fornitore: {$this->id_fornitore}";
        echo '<br>';
        foreach ($this->righe as $riga) {
            echo "Prodotto: {$riga['descrizione']}, Quantità: {$riga['quantita']}, Prezzo: {$riga['prezzo_unitario']}, Totale: {$riga['totale']}€<br>";
        }
    }
}
?>