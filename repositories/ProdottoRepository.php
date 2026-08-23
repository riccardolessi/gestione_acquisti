<?php
declare(strict_types=1);

class ProdottoRepository
{
    public function __construct(private mysqli $db) {}

    /**
     * Ricerca prodotti per descrizione e fornitore DA REINSERIRE int limit = 50
     */
    public function search(string $term, ?int $fornitoreId, int $usaFornitore, ?string $anno): array
    {
        $sql = "
            SELECT 
                p.id,
                p.descrizione,
                f.denominazione AS fornitore
            FROM prodotti p
            JOIN fornitori f ON f.id = p.id_fornitore
            WHERE 1=1
            AND EXISTS (
                SELECT 1
                FROM movimenti m
                WHERE m.id_prodotto = p.id
                    AND m.data_acquisto >= ?
            )
        ";


        $params = [];
        $types  = '';

        if ($anno !== null) {
            $params[] = $anno;
            $types .= "s";
        }

        if ($term !== '') {
            $sql .= " AND p.descrizione LIKE ?";
            $params[] = '%' . $term . '%';
            $types .= 's';
        }

        if ($fornitoreId !== null && $usaFornitore == 1) {
            $sql .= " AND f.id = ?";
            $params[] = $fornitoreId;
            $types .= 'i';
        }

        $sql .= " ORDER BY p.descrizione";
        // $params[] = $limit;  LIMIT ?
        // $types .= 'i';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
