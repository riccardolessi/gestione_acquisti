<?php
require_once 'db.php';
include 'includes/header.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id === 0) {
    echo "<div class='container'><div class='card'>Prodotto non specificato. <a href='search.php'>Torna alla ricerca</a></div></div>";
    include 'includes/footer.php';
    exit;
}

// 1. Dati del prodotto
$sqlProd = "
    SELECT p.*, f.denominazione as fornitore_default 
    FROM prodotti p 
    LEFT JOIN fornitori f ON p.id_fornitore = f.id 
    WHERE p.id = ?
";
$stmt = $conn->prepare($sqlProd);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='container'><div class='card'>Prodotto non trovato.</div></div>";
    include 'includes/footer.php';
    exit;
}

// 2. Movimenti del prodotto, con filtro opzionale per data
$date_start = $_GET['start'] ?? '';
$date_end = $_GET['end'] ?? '';

$sqlMov = "
    SELECT *
    FROM movimenti
    WHERE id_prodotto = ?
";

$params = [$product_id];
$types = "i";

if (!empty($date_start)) {
    $sqlMov .= " AND data_acquisto >= ?";
    $params[] = $date_start;
    $types .= "s";
}
if (!empty($date_end)) {
    $sqlMov .= " AND data_acquisto <= ?";
    $params[] = $date_end;
    $types .= "s";
}

$sqlMov .= " ORDER BY data_acquisto DESC";

$stmtMov = $conn->prepare($sqlMov);
$stmtMov->bind_param($types, ...$params);
$stmtMov->execute();
$movementsResult = $stmtMov->get_result();

// Calcolo delle statistiche di riepilogo
$totalQty = 0;
$totalSpend = 0;
$movements = [];

while ($row = $movementsResult->fetch_assoc()) {
    $totalQty += $row['quantita'];
    $totalSpend += ($row['quantita'] * $row['prezzo']);
    $row['fornitore_acquisto'] = $product['fornitore_default']; // Il fornitore viene preso dal prodotto, non dal documento del movimento:
    // regge perché ogni prodotto è già associato a un solo fornitore in fase di import
    $movements[] = $row;
}
$avgPrice = ($totalQty > 0) ? ($totalSpend / $totalQty) : 0;

?>

<div class="page-header">
    <div>
        <div style="font-size: 0.875rem; color: var(--accent-color); margin-bottom: 0.5rem;">
            <a href="search.php" style="text-decoration: underline;"><i class="fa-solid fa-arrow-left"></i> Torna alla
                Ricerca</a>
        </div>
        <h1 class="page-title">
            <?php echo htmlspecialchars($product['descrizione']); ?>
        </h1>
        <div style="color: var(--text-secondary); margin-top: 0.5rem; display: flex; gap: 1.5rem; align-items: center;">
            <span><i class="fa-solid fa-barcode"></i>
                <?php echo htmlspecialchars($product['codice_articolo']); ?>
            </span>
            <span><i class="fa-solid fa-truck"></i> Default:
                <?php echo htmlspecialchars($product['fornitore_default'] ?? 'N/A'); ?>
            </span>
        </div>
    </div>
</div>

<!-- Riepilogo statistiche -->
<div class="dashboard-grid" style="margin-bottom: 2rem;">
    <div class="card stat-card">
        <div>
            <div class="stat-title">Quantità Totale</div>
            <div class="stat-value">
                <?php echo number_format($totalQty, 2, ',', '.'); ?> <span
                    style="font-size: 1rem; color: var(--text-secondary);">PZ</span>
            </div>
        </div>
        <div class="text-right"><i class="fa-solid fa-cubes stat-icon"></i></div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-title">Prezzo Medio</div>
            <div class="stat-value">€
                <?php echo number_format($avgPrice, 2, ',', '.'); ?>
            </div>
        </div>
        <div class="text-right"><i class="fa-solid fa-tag stat-icon"></i></div>
    </div>
    <div class="card stat-card">
        <div>
            <div class="stat-title">Spesa Totale</div>
            <div class="stat-value">€
                <?php echo number_format($totalSpend, 2, ',', '.'); ?>
            </div>
        </div>
        <div class="text-right"><i class="fa-solid fa-wallet stat-icon"></i></div>
    </div>
</div>

<!-- Filtri e storico acquisti -->
<div class="card">
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <h2 style="font-size: 1.25rem;">Storico Acquisti</h2>
        <form action="product_details.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
            <input type="date" name="start" value="<?php echo htmlspecialchars($date_start); ?>"
                aria-label="Data Inizio">
            <span style="color: var(--text-secondary);">-</span>
            <input type="date" name="end" value="<?php echo htmlspecialchars($date_end); ?>" aria-label="Data Fine">
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filtra</button>
        </form>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Fornitore</th>
                    <th class="text-right">Q.tà</th>
                    <th class="text-right">Prezzo Unit.</th>
                    <th class="text-right">Totale Riga</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movements) > 0): ?>
                    <?php foreach ($movements as $mov): ?>
                        <tr>
                            <td>
                                <?php echo date('d/m/Y', strtotime($mov['data_acquisto'])); ?>
                            </td>
                            <td style="color: var(--text-secondary);">
                                <?php echo htmlspecialchars($mov['fornitore_acquisto'] ?? 'N/A'); ?>
                            </td>
                            <td class="text-right">
                                <?php echo number_format($mov['quantita'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-right">€
                                <?php echo number_format($mov['prezzo'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-right">€
                                <?php echo number_format($mov['quantita'] * $mov['prezzo'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 2rem;">Nessun movimento trovato in questo
                            periodo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>