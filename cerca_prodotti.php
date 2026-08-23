<?php
// // Connessione al database
// require_once('db.php');

// // Riceve il parametro di ricerca
// $search = isset($_POST['search']) ? trim($_POST['search']) : '';

// $fornitore = (isset($_POST['fornitore']) && $_POST['fornitore'] != '') ? $_POST['fornitore'] : null;

// $sql = "SELECT prodotti.id, prodotti.descrizione, fornitori.denominazione AS fornitore 
//         FROM prodotti 
//         JOIN fornitori ON prodotti.id_fornitore = fornitori.id 
//         WHERE prodotti.descrizione LIKE ?";
    
// $params = ["%$search%"];
// $types = "s";

// if ($fornitore !== null) {
//     $sql .= " AND fornitori.id = ?";
//     $params[] = $fornitore;
//     $types .= "i";
// }

// $sql .= " ORDER BY prodotti.descrizione ASC";
// $stmt = $conn->prepare($sql);

// // Binding dei parametri
// if ($fornitore !== null) {
//     $stmt->bind_param($types, $params[0], $params[1]);
// } else {
//     $stmt->bind_param($types, $params[0]);
// }

// $stmt->execute();
// $result = $stmt->get_result();

// // Genera le righe della tabella
// if ($result->num_rows > 0) {
//     while ($row = $result->fetch_assoc()) {
//         echo '<tr>';
//         echo '<td><input type="checkbox" name="prodotti[]" value="'.$row['id'].'"></td>';
//         echo '<td>'.htmlspecialchars($row['descrizione']).'</td>';
//         echo '<td>'.htmlspecialchars($row['fornitore']).'</td>';
//         echo '</tr>';
//     }
// } else {
//     echo '<tr><td colspan="2">Nessun prodotto trovato.</td></tr>';
// }

// $stmt->close();

// $conn->close();
?>

<?php
require_once 'db.php';

$search = trim($_POST['search'] ?? '');
$fornitore = $_POST['fornitore'] ?? null;

if (strlen($search) < 3 && empty($fornitore)) {
    exit;
}

$sql = "
    SELECT 
        p.id, 
        p.descrizione, 
        f.denominazione AS fornitore
    FROM prodotti p
    INNER JOIN fornitori f ON p.id_fornitore = f.id
    WHERE 1=1
";

$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND p.descrizione LIKE ?";
    $params[] = "%{$search}%";
    $types .= 's';
}

if (!empty($fornitore)) {
    $sql .= " AND f.id = ?";
    $params[] = (int)$fornitore;
    $types .= 'i';
}

$sql .= " ORDER BY p.descrizione ASC LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<tr><td colspan="3">Nessun prodotto trovato</td></tr>';
    exit;
}

while ($row = $result->fetch_assoc()):
?>
<tr>
    <td>
        <input type="checkbox" name="prodotti[]" value="<?= (int)$row['id'] ?>">
    </td>
    <td><?= htmlspecialchars($row['descrizione'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($row['fornitore'], ENT_QUOTES, 'UTF-8') ?></td>
</tr>
<?php endwhile; ?>
