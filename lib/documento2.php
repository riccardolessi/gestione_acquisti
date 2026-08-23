<?php
class Documento {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getOrCreate($numero_fattura, $data_fattura, $id_fornitore) {
        $query = "SELECT id FROM documenti WHERE numero_documento = ? AND data_documento = ? AND id_fornitore = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $numero_fattura, $data_fattura, $id_fornitore);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return [$row['id'], 'esistente' => true]; // Il documento esiste già
        }

        // Se non esiste, lo creiamo
        $insertQuery = "INSERT INTO documenti (numero_documento, data_documento, id_fornitore) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($insertQuery);
        $stmt->bind_param("ssi", $numero_fattura, $data_fattura, $id_fornitore);
        $stmt->execute();

        return [$this->db->insert_id, 'esistente' => false];
    }
}
?>