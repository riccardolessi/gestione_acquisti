<?php
    require_once("../db.php");
    require_once("../repositories/FornitoreRepository.php");

    $fornitoreRepo = new FornitoreRepository($conn);
    $fornitori = $fornitoreRepo->getAll();

    require_once('../partials/header.php');
?>

<form id="search-form" method="post" class="mb-4">

    <!-- ===============================
         DESCRIZIONE PRODOTTO
    =============================== -->
    <div class="form-group">
        <label for="search">
            Descrizione prodotto
        </label>
        <input
            type="text"
            name="term"
            id="search"
            class="form-control"
            placeholder="Scrivi almeno 3 lettere"
            autocomplete="off"
        >
    </div>

    <!-- ===============================
         FILTRO FORNITORE
    =============================== -->
    <div class="form-group">

        <div class="form-check mb-2 mt-2">
            <input
                type="checkbox"
                class="form-check-input"
                id="usa_fornitore"
                name="usa_fornitore"
                value="1"
            >
            <label class="form-check-label" for="usa_fornitore">
                Filtra per fornitore
            </label>
        </div>

        <select
            name="fornitore_id"
            id="fornitore"
            class="form-control"
            disabled
        >
            <option value="">-- Seleziona fornitore --</option>

            <?php foreach ($fornitori as $fornitore): ?>
                <option value="<?= (int)$fornitore['id'] ?>">
                    <?= htmlspecialchars($fornitore['denominazione'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>

        </select>
    </div>

    <!-- ===============================
         FILTRO ANNO
    =============================== -->

    <div class="form-group">
        <label for="anno">
            Seleziona da che anno cercare i prodotti
        </label>
        <select id="select_anno" name="anno">
            <?php
                $annoInizio = 2019;
                $annoFine = date("Y");

                for ($anno = $annoInizio; $anno <= $annoFine; $anno++) {
                    $data = $anno . "-01-01";

                    echo '<option value="' . $data . '">' . $anno . "</option>";
                }
                // <option value="2019-01-01">2019</option>
                // <option value="2020-01-01">2020</option>
                // <option value="2021-01-01">2021</option>
                // <option value="2022-01-01">2022</option>
                // <option value="2023-01-01">2023</option>
                // <option value="2024-01-01">2024</option>
                // <option value="2025-01-01">2025</option>
            ?>
        </select>
    </div>

    <!-- ===============================
         SUBMIT (opzionale)
    =============================== -->
    <button type="submit" class="btn btn-primary mt-3" id="submitBtn">
        Cerca
    </button>
</form>

<button id="select-all" disabled>
    Seleziona tutti
</button>

<div id="tabella-container">
    <!-- La tabella verrà inserita qui -->
</div>

<button id="btn-movimenti" class="btn btn-secondary" disabled>Visualizza movimenti prodotti selezionati</button>

<script src="../assets/js/prodotti.js" defer></script>

<?php require "../partials/footer.php"; ?>