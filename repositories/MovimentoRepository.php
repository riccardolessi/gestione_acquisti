<?php
declare(strict_types=1);

class MovimentoRepository
{
    public function __construct(private mysqli $db) {}

    /**
     * Ritorna i movimenti per più prodotti
     *
     * @param int[] $prodottiIds
     */
    public function getByProdotti(array $prodottiIds): array
    {
        if (empty($prodottiIds)) {
            return [];
        }

        // Cast di sicurezza
        $prodottiIds = array_map('intval', $prodottiIds);

        // ?, ?, ?, ?
        $placeholders = implode(',', array_fill(0, count($prodottiIds), '?'));
        $types = str_repeat('i', count($prodottiIds));

        $sql = "
            SELECT 
                m.id,
                m.data_acquisto,
                m.quantita,
                m.prezzo,
                p.descrizione AS prodotto,
                f.denominazione AS fornitore
            FROM movimenti m
            JOIN prodotti p ON p.id = m.id_prodotto
            JOIN fornitori f ON f.id = p.id_fornitore
            WHERE m.id_prodotto IN ($placeholders)
            ORDER BY m.data_acquisto DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$prodottiIds);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
