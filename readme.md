# Dashboard acquisti — redesign dell'interfaccia

Redesign dell'interfaccia di [Inventario](../../tree/main): stessa base dati, nuova UI. Trasforma le tabelle di consultazione in una dashboard di analisi, con l'andamento mensile della spesa e una scheda per prodotto che mostra prezzo medio, quantità e storico degli acquisti.

> **Branch separato, non integrato in `main`.**
> Questo ramo contiene solo il livello di presentazione: legge lo stesso database di `main` ma **non include l'import delle fatture XML**, che resta sul ramo principale. Il refactoring non è mai stato completato e i due rami non sono stati unificati — i limiti sono documentati sotto.

---

## Cosa contiene

**Dashboard** — grafico dell'andamento della spesa mese per mese, con selettore dell'anno popolato dagli anni effettivamente presenti a database e totale annuo in evidenza.

**Ricerca prodotti** — ricerca per descrizione o codice articolo, con il fornitore associato.

**Scheda prodotto** — quantità totale acquistata, prezzo medio ponderato, spesa complessiva e storico completo degli acquisti, filtrabile per intervallo di date.

---

## Rapporto con il ramo principale

| | `main` | questo ramo |
|---|---|---|
| Import fatture XML | sì | no |
| Consultazione dati | tabelle Bootstrap | dashboard con grafici |
| Connessione DB | `config.ini` | credenziali in `db.php` |
| Schema database | lo stesso (`schema.sql` su `main`) | |

Per avere dei dati da visualizzare serve prima eseguire l'import dal ramo `main`: questo ramo è di sola lettura.

---

## Stack

- PHP puro, senza framework né dipendenze Composer
- MySQL 8
- [Chart.js](https://www.chartjs.org/) da CDN per il grafico
- CSS scritto a mano su design token (`:root` con 13 variabili), tema scuro, effetto vetro sull'header
- Font Awesome e Inter da CDN

Nessun build step: si apre e funziona.

---

## Struttura

```
├── index.php              dashboard e grafico annuale
├── search.php             ricerca prodotti
├── product_details.php    scheda prodotto e storico acquisti
├── db.php                 connessione al database
├── includes/              header e footer condivisi
└── css/style.css          design system (289 righe)
```

---

## Installazione

Caricare lo schema e importare le fatture dal ramo `main`, poi configurare le credenziali in `db.php` e aprire `index.php`.

---

## Limiti noti e cosa rifarei oggi

### Il limite più importante: confrontare lo stesso prodotto tra fornitori

Nella scheda prodotto la colonna "Fornitore" **non viene dal singolo acquisto**: è copiata dal prodotto (product_details.php). Il join movimenti → documenti → fornitori, che direbbe da chi hai comprato *quella volta*, non viene mai fatto.

Il risultato a schermo è corretto, ma solo per un motivo indiretto: in fase di import i prodotti vengono deduplicati su descrizione + codice articolo + fornitore, quindi ogni prodotto appartiene già a un solo fornitore. Ed è proprio lì il problema di fondo: **lo stesso articolo acquistato da tre fornitori diventa tre righe distinte in prodotti**, e la dashboard non può confrontarne i prezzi — che sarebbe il motivo per cui l'applicazione esiste. Nei dati reali questo si vede bene: circa 1.900 fatture hanno generato oltre 23.000 prodotti.

Risolverlo richiede di cambiare il modello dati, non la UI: separare l'anagrafica di prodotto dal legame con il fornitore, e leggere il fornitore dal documento del movimento.

La limitazione di leggere i prodotti dalle fatture elettroniche è che lo stesso prodotto acquistato da più fornitori ha codici prodotto e descrizioni diverse (spesso le descrizioni sono molto diverse), quindi non è possibile unirli in maniera automatizzata. L'unico modo per verificare che uno stesso prodotto è stato acquistato da più fornitori è usando il codice a barre, che però non viene riportato in fattura da tutti i fornitori e non viene gestito da quest'app.

### Il CSS è applicato a metà

Il foglio di stile definisce un design system coerente — token di colore, spaziature, classi `.card`, `.stat-card`, `.dashboard-grid` — ma restano **31 attributi `style="..."` inline** nelle pagine, scritti mentre sistemavo il layout e mai spostati nel CSS.

Soprattutto: **non c'è nessuna media query**, quindi su schermi stretti il layout non si adatta. Per una dashboard è il difetto che pesa di più, ed è anche il più veloce da correggere (L'app girava solo sul mio computer quindi questo difetto non era importante).

### Altri difetti noti

- `db.php` ha le credenziali scritte nel codice: è un passo indietro rispetto al `config.ini` del ramo principale, e va allineato.
- Il grafico è configurato come `type: 'bar'` ma il dataset porta ancora `tension`, `fill` e `pointRadius`, opzioni da grafico a linee che su un istogramma vengono ignorate: residui di un cambio di tipo mai ripulito.
- Il footer dice "All rights reserved" in un'interfaccia interamente in italiano.
- In `product_details.php` la riga "nessun movimento" usa `colspan="6"` su una tabella di cinque colonne.
- **Nessuna autenticazione**: come il ramo principale, l'applicazione è nata per uso personale su `localhost`. Esposta in rete, mostrerebbe l'intero storico acquisti a chiunque.
- I totali della dashboard sono accurati quanto lo è l'import: i bug di conversione dei prezzi documentati su `main` si riflettono qui, perché questo ramo si limita a leggere.

### Cosa cambierei a livello di struttura

- **Le query stanno dentro le pagine.** Ogni file mescola accesso ai dati, calcoli e HTML. Il ramo principale aveva già introdotto i repository: questa UI andava costruita sopra quelli, non a fianco.
- **Nessuna paginazione**: la ricerca si ferma a `LIMIT 50` fisso, senza modo di vedere i risultati successivi.
- **Nessuna gestione degli errori**: se il database non risponde, la pagina muore con l'errore di sistema invece di un messaggio comprensibile.

---

## Idee non sviluppate

- Confronto dei prezzi dello stesso prodotto tra fornitori diversi (richiede la modifica al modello dati descritta sopra)
- Segnalazione degli aumenti di prezzo rispetto all'acquisto precedente
- Esportazione in CSV dello storico filtrato
- Unificazione con il ramo `main` in un'unica applicazione

---

## Licenza

MIT
