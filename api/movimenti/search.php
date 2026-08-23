<?php

header('Content-Type: application/json');

require_once('../../db.php');
require_once('../../repositories/ProdottoRepository.php');

try {
    $term = trim($_GET['term'] ?? '');
    
    $fornitoreId = isset($_GET['fornitore_id']) && $_GET['fornitore_id'] !== ''
        ? (int) $_GET['fornitore_id']
        : null;

    $usa_fornitore = trim($_GET['usa_fornitore'] ?? 0);
    
    // $limit = isset($_GET['limit'])
    //     ? min((int) $_GET['limit'], 100)
    //     : 50;
    
    $repo = new ProdottoRepository($conn);

    $result = $repo->search(
        term: $term,
        fornitoreId: $fornitoreId,
        usaFornitore: $usa_fornitore
        // limit: $limit
    );

    echo json_encode($result);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => "Errore interno: $e"
    ]);
}

?>