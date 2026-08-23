<?php
class GestioneProdotti {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Metodo per ottenere l'ID di un prodotto, inserendolo se non esiste
    public function getOrCreateProduct($descrizione, $codice_prodotto, $id_fornitore) {
        // Controlla se il prodotto esiste già
        $query = "SELECT id FROM prodotti WHERE descrizione = ? AND codice_articolo = ? AND id_fornitore = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $descrizione, $codice_prodotto, $id_fornitore);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();

        if ($id) {
            return $id; // Se esiste, restituisce l'ID
        } else {
            // Se non esiste, lo inserisce e restituisce il nuovo ID
            $query = "INSERT INTO prodotti (descrizione, codice_articolo, id_fornitore) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssi", $descrizione, $codice_prodotto, $id_fornitore);
            $stmt->execute();
            $newId = $stmt->insert_id; // Ottiene l'ID del nuovo prodotto
            $stmt->close();
            return $newId;
        }
    }
}
?>
