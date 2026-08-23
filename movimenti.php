<!-- movimenti_prodotto.php -->
<?php
require_once('db.php');
// Recupera l'ID del prodotto dalla query string
$id_prodotto = $_GET['id_prodotto'] ?? '';


// Recupera i dettagli del prodotto (nome e fornitore)
$sql_prodotto = "SELECT p.descrizione, f.denominazione AS fornitore
                 FROM prodotti p
                 LEFT JOIN fornitori f ON p.id_fornitore = f.id
                 WHERE p.id = ?";
$stmt_prodotto = $conn->prepare($sql_prodotto);
$stmt_prodotto->bind_param("i", $id_prodotto);
$stmt_prodotto->execute();
$result_prodotto = $stmt_prodotto->get_result();
$prodotto = $result_prodotto->fetch_assoc();

// Recupera i movimenti del prodotto
$sql_movimenti = "SELECT * FROM movimenti WHERE id_prodotto = ? ORDER BY data_acquisto DESC";
$stmt_movimenti = $conn->prepare($sql_movimenti);
$stmt_movimenti->bind_param("i", $id_prodotto);
$stmt_movimenti->execute();
$result_movimenti = $stmt_movimenti->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimenti Prodotto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body data-bs-theme="dark">
<div class="container">
        <h2 class="mt-4">Movimenti del Prodotto</h2>
        
        <?php if ($prodotto): ?>
            <div class="alert alert-info">
                <strong>Prodotto:</strong> <?php echo htmlspecialchars($prodotto['descrizione']); ?><br>
                <strong>Fornitore:</strong> <?php echo htmlspecialchars($prodotto['fornitore']); ?>
            </div>
        <?php else: ?>
            <p>Prodotto non trovato.</p>
        <?php endif; ?>

        <?php if ($result_movimenti->num_rows > 0): ?>
            <table class="table table-bordered mt-4">
                <thead class="thead-dark">
                    <tr>
                        <th>Data Movimento</th>
                        <th>Quantità</th>
                        <th>Prezzo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_movimenti->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['data_acquisto']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantita']); ?></td>
                            <td><?php echo htmlspecialchars($row['prezzo']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nessun movimento trovato per questo prodotto.</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-secondary mt-4">Torna alla ricerca</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Chiusura della connessione
$conn->close();
?>