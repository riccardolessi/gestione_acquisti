<?php
// Controllo se l'array di prodotti è stato inviato
if (!isset($_POST['prodotti']) || empty($_POST['prodotti'])) {
    die("Nessun prodotto selezionato.");
}

// Connessione al database
require_once('db.php');

// Puliamo gli ID ricevuti per sicurezza
$prodotti_selezionati = array_map('intval', $_POST['prodotti']);
$placeholders = implode(',', array_fill(0, count($prodotti_selezionati), '?'));

// Query per ottenere i dettagli dei prodotti
$sql_prodotti = "SELECT p.id, p.descrizione, f.denominazione AS fornitore
                 FROM prodotti p
                 LEFT JOIN fornitori f ON p.id_fornitore = f.id
                 WHERE p.id IN ($placeholders)";
$stmt_prodotti = $conn->prepare($sql_prodotti);
$stmt_prodotti->bind_param(str_repeat('i', count($prodotti_selezionati)), ...$prodotti_selezionati);
$stmt_prodotti->execute();
$result_prodotti = $stmt_prodotti->get_result();

// Memorizziamo i prodotti in un array associativo
$prodotti = [];
while ($row = $result_prodotti->fetch_assoc()) {
    $prodotti[$row['id']] = $row;
}

// Query per ottenere i movimenti dei prodotti selezionati
$sql_movimenti = "SELECT m.id_prodotto, m.data_acquisto, m.quantita, m.prezzo
                  FROM movimenti m
                  WHERE m.id_prodotto IN ($placeholders)
                  ORDER BY m.data_acquisto DESC";
$stmt_movimenti = $conn->prepare($sql_movimenti);
$stmt_movimenti->bind_param(str_repeat('i', count($prodotti_selezionati)), ...$prodotti_selezionati);
$stmt_movimenti->execute();
$result_movimenti = $stmt_movimenti->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimenti Selezionati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body data-bs-theme="dark">
    <div class="container">
        <h2 class="mt-4">Movimenti dei Prodotti Selezionati</h2>

        <?php if ($result_movimenti->num_rows > 0): ?>
            <table class="table table-bordered mt-4">
                <thead class="thead-dark">
                    <tr>
                        <th>Prodotto</th>
                        <th>Fornitore</th>
                        <th>Data Movimento</th>
                        <th>Quantità</th>
                        <th>Prezzo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_movimenti->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prodotti[$row['id_prodotto']]['descrizione']); ?></td>
                            <td><?php echo htmlspecialchars($prodotti[$row['id_prodotto']]['fornitore']); ?></td>
                            <td><?php echo htmlspecialchars($row['data_acquisto']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantita']); ?></td>
                            <td><?php echo htmlspecialchars($row['prezzo']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nessun movimento trovato per i prodotti selezionati.</p>
        <?php endif; ?>

        <a href="form2.php" class="btn btn-secondary mt-4">Torna alla selezione</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
