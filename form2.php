<?php
    // Connessione al database
    require_once('db.php');

    $result = $conn->query("SELECT id, denominazione FROM fornitori ORDER BY denominazione;");
?>

<?php require_once('./partials/header.php');?>

<div class="container">
    <h2 class="mt-4">Cerca e Seleziona Prodotti</h2>

    <!-- Input di ricerca -->
    <div class="form-group">
        <label for="search">Cerca un prodotto:</label>
        <input type="text" id="search" class="form-control" placeholder="Inserisci il nome del prodotto">
        <input type="checkbox" value="filtra_fornitori">
        <label for="filtra_fornitori">Filtra Fornitori</label><br>
        <select name="fornitore" id="fornitore">
            <option value="">-- Tutti i fornitori --</option>
            <?php
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['denominazione'] . "</option>";
                }
            ?>
        </select>
    </div>

    <!-- Tabella per mostrare i risultati -->
    <form id="product-form" action="movimenti_selezionati.php" method="post">
        <table class="table table-bordered mt-3">
            <thead class="thead-dark">
                <tr>
                    <th>Seleziona</th>
                    <th>Nome Prodotto</th>
                    <th>Fornitore</th>
                </tr>
            </thead>
            <tbody id="product-list">
                <!-- Risultati AJAX verranno inseriti qui -->
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary" disabled id="submit-btn">Vedi Movimenti</button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- <script>
    $(document).ready(function(){
        let delayTimer;

        $('#search').on('keyup', function(){
            clearTimeout(delayTimer); // Cancella il timer precedente
            var query = $(this).val();
            var id_fornitore = $('#fornitore').val();

            delayTimer = setTimeout(function() {
                if (query.length > 2) { // Cerca solo dopo 3 caratteri
                    $.ajax({
                        url: 'cerca_prodotti.php',
                        method: 'POST',
                        data: {search: query, fornitore: id_fornitore},
                        success: function(data) {
                            $('#product-list').html(data);
                        }
                    });
                } else {
                    $('#product-list').html(''); // Svuota la lista se il campo è vuoto
                }
            }, 1000); // Delay di 1 secondo (1000 ms)
        });

        // Abilita il pulsante solo se c'è almeno un prodotto selezionato
        $(document).on('change', 'input[name="prodotti[]"]', function(){
            $('#submit-btn').prop('disabled', $('input[name="prodotti[]"]:checked').length === 0);
        });
    });
</script> -->

<script>
let delayTimer;

// Funzione per effettuare la richiesta fetch
function aggiornaProdotti() {
    const search = document.getElementById('search').value;
    const fornitore = document.getElementById('fornitore').value;

    // Solo se almeno 3 lettere
    if (fornitore !== '') {
        fetch('cerca_prodotti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                search: search,
                fornitore: fornitore
            })
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('product-list').innerHTML = data;
        });
    } else {
        document.getElementById('product-list').innerHTML = '';
    }
}

// Attiva fetch con debounce su search
document.getElementById('search').addEventListener('keyup', function () {
    clearTimeout(delayTimer);
    delayTimer = setTimeout(aggiornaProdotti, 500);
});

// Attiva fetch immediato su cambio fornitore
document.getElementById('fornitore').addEventListener('change', function () {
    clearTimeout(delayTimer);
    delayTimer = setTimeout(aggiornaProdotti, 500);
});
</script>



<?php
    require_once('./partials/footer.php');
    $conn->close(); 
?>
