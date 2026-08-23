<?php
require_once 'db.php';
include 'includes/header.php';

// Logica di ricerca
$search_query = $_GET['q'] ?? '';
$whereClauses = [];
$params = [];
$types = "";

// Query di base
$sql = "
    SELECT p.id, p.descrizione, p.codice_articolo, f.denominazione as fornitore_default
    FROM prodotti p
    LEFT JOIN fornitori f ON p.id_fornitore = f.id
";

if (!empty($search_query)) {
    $sql .= " WHERE p.descrizione LIKE ? OR p.codice_articolo LIKE ?";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= "ss";
}

$sql .= " ORDER BY p.descrizione ASC LIMIT 50";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="page-header">
    <h1 class="page-title">Ricerca Prodotti</h1>
</div>

<!-- Form di ricerca -->
<div class="search-container">
    <form action="search.php" method="GET" class="search-form" style="grid-template-columns: 1fr auto;">
        <div class="form-group">
            <label for="q">Cerca Prodotto (Nome o Codice)</label>
            <input type="text" name="q" id="q" placeholder="Digita per cercare..."
                value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="height: 48px; margin-top: auto;">
                <i class="fa-solid fa-search"></i> Cerca
            </button>
        </div>
    </form>
</div>

<!-- Tabella dei risultati -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1.25rem;">Prodotti Trovati (<?php echo $result->num_rows; ?>)</h2>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Codice</th>
                    <th>Descrizione</th>
                    <th>Fornitore Predefinito</th>
                    <th class="text-right">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span
                                    style="font-family: monospace; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">
                                    <?php echo htmlspecialchars($row['codice_articolo']); ?>
                                </span>
                            </td>
                            <td style="font-weight: 500;">
                                <?php echo htmlspecialchars($row['descrizione']); ?>
                            </td>
                            <td style="color: var(--text-secondary);">
                                <?php echo htmlspecialchars($row['fornitore_default'] ?? '-'); ?>
                            </td>
                            <td class="text-right">
                                <a href="product_details.php?id=<?php echo $row['id']; ?>" class="btn btn-primary"
                                    style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                    <i class="fa-solid fa-chart-pie"></i> Analisi
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 3rem; color: var(--text-secondary);">
                            <i class="fa-solid fa-box-open"
                                style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i><br>
                            Nessun prodotto trovato. Prova a cercare altro.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>