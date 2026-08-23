document.addEventListener('DOMContentLoaded', () => {
    const checkboxFornitore = document.getElementById('usa_fornitore');
    const selectFornitore = document.getElementById('fornitore');
    const searchForm = document.getElementById('search-form');
    const btnMovimenti = document.getElementById('btn-movimenti');
    const container = document.getElementById('tabella-container');
    const bottoneSelectAll = document.getElementById('select-all')

    checkboxFornitore.addEventListener('change', () => {
        selectFornitore.disabled = !checkboxFornitore.checked;

        // Reset valore se disabilitato
        if (!checkboxFornitore.checked) {
            selectFornitore.value = '';
        }
    });

    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        // let prodotto = document.getElementById('search').value;
        // let fornitore = document.getElementById('fornitore').value;

        const form = e.target;
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        try {
            const response = await fetch(`../api/prodotti/search.php?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Errore HTTP: ${response.status}`);
            }

            const data = await response.json();

            // Mostra la tabella
            const container = document.getElementById('tabella-container');
            container.innerHTML = ''; // pulisci eventuale tabella precedente

            if (data.length === 0) {
                container.innerHTML = '<p>Nessun risultato trovato.</p>';
                return;
            }

            // Creazione tabella
            const table = document.createElement('table');
            table.classList.add("table");
            table.style.borderCollapse = 'collapse';
            table.style.width = '100%';

            // Header
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            ['Checkbox', 'ID', 'Descrizione', 'Fornitore'].forEach(text => {
                const th = document.createElement('th');
                th.textContent = text;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Body
            const tbody = document.createElement('tbody');
            data.forEach(item => {
                const row = document.createElement('tr');

                // Checkbox
                const tdCheckbox = document.createElement('td');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = item.id;
                tdCheckbox.appendChild(checkbox);
                row.appendChild(tdCheckbox);

                // ID
                const tdId = document.createElement('td');
                tdId.textContent = item.id;
                row.appendChild(tdId);

                // Descrizione
                const tdDescr = document.createElement('td');
                tdDescr.textContent = item.descrizione;
                row.appendChild(tdDescr);

                // Fornitore
                const tdForn = document.createElement('td');
                tdForn.textContent = item.fornitore;
                row.appendChild(tdForn);

                tbody.appendChild(row);
            });

            table.appendChild(tbody);
            container.appendChild(table);

            bottoneSelectAll.disabled = false;
            btnMovimenti.disabled = false;

        } catch (error) {
            console.error(error);
            document.getElementById('tabella-container').innerHTML = `<p style="color:red;">Errore: ${error.message}</p>`;
        }
    });

    // Bottone per movimenti prodotti selezionati
    btnMovimenti.addEventListener('click', function(e) {
        e.preventDefault()
        const selected = container.querySelectorAll('input[type="checkbox"]:checked');
        const selectedIds = Array.from(selected).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
        alert('Seleziona almeno un prodotto.');
        return;
        }

        // Creazione query string
        //const query = selectedIds.map(id => `id=${encodeURIComponent(id)}`).join('&');
        let query = "id=";
        for (let i = 0; i < selectedIds.length; i++) {
            query += selectedIds[i];
            if (i < selectedIds.length -1) {
                query += ",";
            }
        }

        // Reindirizzamento a movimenti.php
        window.location.href = `movimenti.php?${query}`;
    });

    bottoneSelectAll.addEventListener('click', () => {
        const checkboxes = container.querySelectorAll('input[type="checkbox"]');

        const allSelected = [...checkboxes].every(cb => cb.checked);

        checkboxes.forEach(cb => {
            cb.checked = !allSelected;
        });

        bottoneSelectAll.textContent = allSelected ? 'Seleziona tutti' : 'Deseleziona tutti';
    })
})