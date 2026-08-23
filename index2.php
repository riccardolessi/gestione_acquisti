<?php
// Connessione al database
require_once('db.php');

// Ottieni la lista dei fornitori per il filtro
$sql = "SELECT * FROM fornitori ORDER BY denominazione ASC";
$result = $conn->query($sql);
$fornitori = $result->fetch_all(MYSQLI_ASSOC);

$title = 'Cerca Prodotti';
include('./partials/header.php');

?>
<div class="container mt-5">
    <h2>Cerca Prodotto</h2>
    <form method="GET" action="risultati.php">
        <div class="mb-3">
            <label for="prodotto" class="form-label">Nome Prodotto</label>
            <input type="text" class="form-control" id="prodotto" name="prodotto" placeholder="Cerca prodotto">
        </div>

        <div class="mb-3">
            <label for="fornitore" class="form-label">Fornitore</label>
            <select class="form-control" id="fornitore" name="fornitore">
                <option value="">Seleziona Fornitore</option>
                <?php foreach ($fornitori as $fornitore): ?>
                    <option value="<?php echo $fornitore['id']; ?>"><?php echo $fornitore['denominazione']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Cerca</button>
    </form>
</div>

<?php include('./partials/footer.php'); ?>