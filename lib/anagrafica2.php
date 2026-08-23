<?php
class Anagrafica {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getOrCreate($denominazione, $partita_iva, $codice_fiscale) {
        $query = "SELECT id FROM fornitori WHERE partita_iva = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $partita_iva);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['id']; // L'anagrafica esiste già
        }

        // Se non esiste, la creiamo
        $insertQuery = "INSERT INTO fornitori (denominazione, partita_iva, codice_fiscale) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($insertQuery);
        $stmt->bind_param("sss", $denominazione, $partita_iva, $codice_fiscale);
        $stmt->execute();
        
        return $this->db->insert_id;
    }
}
?>