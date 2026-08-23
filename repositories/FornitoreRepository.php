<?php
declare(strict_types=1);

class FornitoreRepository
{
    public function __construct(private mysqli $db) {}

    /**
     * Ritorna tutti i fornitori
     */
    public function getAll(): array
    {
        $sql = "SELECT id, denominazione FROM fornitori ORDER BY denominazione";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
