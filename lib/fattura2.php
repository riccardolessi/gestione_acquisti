<?php
require_once('anagrafica2.php');
require_once('documento2.php');
require_once('prodotto2.php');

class Fattura {
    private $db;
    private $anagrafica;
    private $documento;
    private $prodotto;

    public function __construct($db) {
        $this->db = $db;
        $this->anagrafica = new Anagrafica($db);
        $this->documento = new Documento($db);
        $this->prodotto = new GestioneProdotti($db);
    }

    public function importaFattura($url) {
        $file = simplexml_load_file($url); // Caricamento XML

        $partita_iva = (string) $file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $codice_fiscale;
        if (isset($file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->CodiceFiscale)) {
            $codice_fiscale = (string) $file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->CodiceFiscale;
        } else {
            $codice_fiscale = $partita_iva;
        }

        $fattura = [
            'numero_fattura' => (string) $file->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero,
            'data_fattura' => (string) $file->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data,
            'denominazione' => (string) $file->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->Anagrafica->Denominazione,
            'partita_iva' => (string) $partita_iva,
            'codice_fiscale' => (string) $codice_fiscale
        ];

        try {
            // Ottieni o crea l'anagrafica
            $id_anagrafica = $this->anagrafica->getOrCreate(
                $fattura['denominazione'],
                $fattura['partita_iva'],
                $fattura['codice_fiscale']
            );

            // Ottieni o crea il documento
            $id_documento = $this->documento->getOrCreate(
                $fattura['numero_fattura'], 
                $fattura['data_fattura'], 
                $id_anagrafica
            );

            if ($id_documento['esistente'] == true) {
                $numFt = $fattura['numero_fattura'];
                $denom = $fattura['denominazione'];
                $dataFt = $fattura['data_fattura'];
                echo "fattura n. $numFt del $dataFt (di $denom) già presente con ID: $id_documento[0]<br>";
                return null;
            }

            // getOrCreate() restituisce [id, 'esistente' => bool]: serve solo l'id
            $id_doc = $id_documento[0];

            $regex = '/\b(trasporto|incasso|ddt|d.d.t)\b/i';

            // Inserisci le righe della fattura
            foreach ($file->FatturaElettronicaBody->DatiBeniServizi->DettaglioLinee as $riga) {
                $descrizione = (string) $riga->Descrizione;

                if (!preg_match($regex, $descrizione)) {
                    $codice_articolo = isset($riga->CodiceArticolo->CodiceValore) ? (string) $riga->CodiceArticolo->CodiceValore : "";
                    $unitaMisura = (string) $riga->UnitaMisura;
                    $id_prodotto = $this->prodotto->getOrCreateProduct((string) $descrizione, (string) $codice_articolo, (int) $id_anagrafica);
                    $prezzo_totale = (double) $riga->PrezzoTotale;
                    $quantita = (double) $riga->Quantita;
                    $prezzo;
                    if ($prezzo_totale != 0 && $quantita != 0) {
                        $prezzo = number_format($prezzo_totale / $quantita, 2);
                    } else {
                        $prezzo = 0;
                    }
                    echo "id prodotto: $id_prodotto<br>";
                    $query = "INSERT INTO movimenti (id_prodotto, prezzo, quantita, um, data_acquisto, id_documento) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bind_param("iddssi", $id_prodotto, $prezzo, $quantita, $unitaMisura, $fattura['data_fattura'], $id_doc);
                    $stmt->execute();
                }   
            }

            $this->db->commit();
            return "Fattura importata con successo!";
        } catch (Exception $e) {
            return "Errore: " . $e->getMessage();
        }
    }
}
?>