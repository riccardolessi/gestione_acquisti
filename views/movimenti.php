<?php
    
    require_once("../repositories/MovimentoRepository.php");
    require_once("../db.php");

    $prodotti = $_GET['id'] ?? ""; // $_GET['id'] è un array
    
    $lista_prodotti = explode(",", $prodotti);
    
    $repo = new MovimentoRepository($conn);

    $lista_movimenti = $repo->getByProdotti($lista_prodotti);
?>

<?php require_once("../partials/header.php"); ?>

<?php if (!empty($lista_movimenti)): ?>
<table class="table">
    <thead>
        <tr>
            <th>Data Acquisto</th>
            <th>Quantità</th>
            <th>Prezzo</th>
            <th>Descrizione</th>
            <th>Fornitore</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lista_movimenti as $prodotto): ?>
        <tr>
            <td><?= htmlspecialchars($prodotto['data_acquisto']) ?></td>
            <td><?= htmlspecialchars($prodotto['quantita']) ?></td>
            <td><?= htmlspecialchars($prodotto['prezzo']) ?></td>
            <td><?= htmlspecialchars($prodotto['prodotto']) ?></td>
            <td><?= htmlspecialchars($prodotto['fornitore']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>Nessun movimento trovato.</p>
<?php endif; ?>


