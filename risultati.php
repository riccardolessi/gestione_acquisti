<?php
// Connessione al database
include('db.php');

// Recupera i parametri dalla query string
$prodotto = isset($_GET['prodotto']) ? $_GET['prodotto'] : '';
$fornitore = isset($_GET['fornitore']) ? $_GET['fornitore'] : '';

// Crea la query di ricerca
$sql = "SELECT * FROM prodotti WHERE descrizione LIKE ?";
$params = ["%$prodotto%"];

if ($fornitore != '') {
    $sql .= " AND id_fornitore = ? ORDER BY descrizione ASC";
    $params[] = $fornitore;
} else {
    $sql .= " ORDER BY descrizione ASC";
}

$stmt = $conn->prepare($sql);
// Creare una variabile per il parametro
if ($fornitore != '') {
    // Se sono presenti due parametri, binding per stringa e intero
    $stmt->bind_param("si", $params[0], $params[1]);
} else {
    // Se c'è solo un parametro (prodotto), binding solo per stringa
    $stmt->bind_param("s", $params[0]);
}

$stmt->execute();
$result = $stmt->get_result();

// Mostra i risultati
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultati Ricerca Prodotti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body data-bs-theme="dark">
    <div class="container mt-5">
        <h2>Risultati della ricerca</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Fornitore</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($prodotto = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $prodotto['descrizione']; ?></td>
                        <td>
                            <?php
                            // Ottieni il nome del fornitore
                            $fornitore_sql = "SELECT denominazione FROM fornitori WHERE id = ?";
                            $fornitore_stmt = $conn->prepare($fornitore_sql);
                            $fornitore_stmt->bind_param('i', $prodotto['id_fornitore']);
                            $fornitore_stmt->execute();
                            $fornitore_result = $fornitore_stmt->get_result();
                            $fornitore_data = $fornitore_result->fetch_assoc();
                            echo $fornitore_data['denominazione'];
                            ?>
                        </td>
                        <td><a href="movimenti.php?id_prodotto=<?php echo $prodotto['id']; ?>" class="btn btn-info">Vedi Movimenti</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
