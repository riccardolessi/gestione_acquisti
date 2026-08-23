**C'è un branch di questo repository con la UI rifatta a fine 2025, ma abbandonata a favore di un'app Windows completamente nuova fatta in C# e framework WPF**

# Inventario — analisi acquisti da fatture elettroniche XML

Web app in PHP che importa fatture elettroniche (XML, formato SDI/FatturaPA) e ricostruisce lo storico degli acquisti: chi ti ha venduto cosa, a che prezzo, e come quel prezzo è cambiato nel tempo.

Nasce da un'esigenza reale: capire l'andamento dei prezzi d'acquisto senza reinserire a mano centinaia di righe di fattura.

> **Stato: progetto archiviato, non mantenuto.**
> Sviluppato nel 2023-2024 per uso personale in locale (MAMP). Contiene parti incomplete e bug noti, documentati onestamente più sotto nella sezione [Limiti noti](#limiti-noti-e-cosa-rifarei-oggi). È pubblicato come portfolio, non come software pronto all'uso.

---

## Parsing delle fatture

La fattura elettronica italiana è un XML con struttura profonda e molti campi opzionali. I punti che l'app deve gestire:

- il **prezzo unitario reale** non è `PrezzoUnitario`: quello è il listino. Il prezzo effettivamente pagato è `PrezzoTotale / Quantita`, perché sconti e maggiorazioni vivono in un blocco separato;
- il `CodiceArticolo` è opzionale e i fornitori lo popolano in modo incoerente;
- il `CodiceFiscale` del cedente può mancare: in quel caso si usa la partita IVA come fallback;
- le righe di fattura non sono solo prodotti — contengono trasporto, incasso, riferimenti DDT. Vengono filtrate con una regex prima dell'inserimento;
- la stessa fattura non deve essere importabile due volte: la deduplica avviene sulla tripla `(fornitore, numero_documento, data_documento)`.

---

## Caratteristiche

- Import massivo di file XML da cartella
- Riconoscimento e deduplica di fornitori, documenti e prodotti (`getOrCreate`)
- Normalizzazione in 4 tabelle relazionali invece di una tabella piatta
- Ricerca prodotti con filtro per fornitore e per anno
- Selezione multipla di prodotti e confronto dei relativi movimenti d'acquisto

---

## Flusso dell'applicazione

```
File XML in uploads/
        ↓
ImportaFatture  →  itera i file della cartella
        ↓
Fattura::importaFattura()
        ↓
   ┌────────────────┬──────────────────┬─────────────────┐
   ↓                ↓                  ↓                 ↓
Anagrafica     Documento          Prodotto          Movimenti
getOrCreate    getOrCreate        getOrCreate       INSERT riga
(fornitori)    (deduplica ft.)    (prodotti)        (storico prezzi)
        ↓
Ricerca prodotti  →  selezione multipla  →  storico movimenti
```

---

## Struttura

```
├── api/                    endpoint JSON (ricerca prodotti)
├── assets/js/              frontend vanilla JS, fetch + DOM
├── config/                 singleton di connessione (vedi Limiti noti)
├── lib/                    dominio: parsing XML e import
├── partials/               header e footer condivisi
├── repositories/           accesso ai dati con prepared statement
├── views/                  pagine della UI
└── db.php                  connessione legacy, ancora in uso
```

---

## Stack

- PHP 7.4+ (sviluppato su MAMP)
- MySQL 8
- Apache
- Bootstrap 5 da CDN, JavaScript vanilla (nessun build step, nessuna dipendenza Composer)

---

## Installazione

```bash
git clone https://github.com/<utente>/<repo>.git
```

Copiare la configurazione di esempio e compilarla:

```bash
cp config.ini.example config.ini
```

```ini
DB_HOST = localhost
DB_USER = root
DB_PASS = root
DB_NAME = inventario
```

Creare il database e caricare la struttura:

```bash
mysql -uroot -proot inventario < schema.sql
```

Creare la cartella `uploads/`, copiarci dentro i file XML e aprire `inserisci.php` per lanciare l'import.

---

## Dati

Lo schema è normalizzato su quattro tabelle: `fornitori` → `documenti` → `movimenti` ← `prodotti`.
Un *movimento* è una riga di fattura: lega un prodotto al documento da cui proviene, con prezzo, quantità e data.

L'applicazione è stata usata su un archivio reale di circa **1.900 fatture**, da cui sono stati estratti **~23.000 prodotti** e **~49.000 movimenti d'acquisto**.

---

## Limiti noti e cosa rifarei oggi

Questa sezione è volutamente dettagliata: il codice è pubblicato com'era quando ho smesso di lavorarci, e mi interessa mostrare che oggi so dove sono i problemi.

### Scelte consapevoli, legate all'uso personale

- **Nessuna autenticazione.** L'app girava su `localhost` in MAMP, utente singolo, mai esposta in rete. Per un deploy reale servirebbe come minimo login con sessioni e password hashate, oltre a protezione CSRF sulle azioni di scrittura.
- **Nessun framework.** All'epoca non ero riuscito a configurare Laravel sull'ambiente locale e sono andato avanti in PHP puro, senza dipendenze esterne. Col senno di poi il problema era la configurazione dell'ambiente, non il framework — ma scrivere a mano connessione, query e struttura mi ha costretto a capire prepared statement, escaping e separazione delle responsabilità, ed è il motivo per cui la seconda iterazione introduce i repository. Oggi lo imposterei su Laravel, soprattutto per avere le migrazioni versionate.

### Refactoring lasciato a metà

A metà sviluppo ho riorganizzato l'accesso ai dati verso il repository pattern, ma il refactoring non è mai stato portato a termine. Si vede:

- in `lib/` convivono due generazioni della stessa logica: `fattura.php`, `documento.php`, `anagrafica.php` e le rispettive `*2.php`. Solo le seconde sono usate; le prime sono codice morto e vanno cancellate.
- `config/database.php` implementa un singleton di connessione ma **non funziona e non è mai chiamato**: `$config` è definito fuori dalla classe e letto dentro un metodo statico, dove non è in scope, e le chiavi lette (`host`, `user`) non corrispondono a quelle di `config.ini` (`DB_HOST`, `DB_USER`). Tutta l'app usa ancora la connessione globale di `db.php`.

### Bug noti, non corretti

- `lib/fattura2.php` — il prezzo unitario passa da `number_format()`, che inserisce la virgola come separatore delle migliaia; bindato poi come `double`, un prezzo di 1234.57 viene salvato come 1.0. Corruzione silenziosa sugli importi a quattro cifre.
- `lib/fattura2.php` — `commit()` viene chiamato senza `begin_transaction()`: con autocommit attivo non è una transazione, e un import interrotto lascia dati parziali.
- `api/movimenti/search.php` — endpoint non funzionante: usa il repository sbagliato e chiama `search()` senza l'argomento obbligatorio `anno`.
- `risultati.php` — è l'unica view rimasta senza `htmlspecialchars()` sull'output.
- La voce "Inserisci dati" della navbar punta a `#`: la UI non è mai stata completata.

### Cosa cambierei a livello di struttura

- **Nessun indice e nessun vincolo di integrità.** Lo schema ha solo le chiavi primarie: `movimenti.id_prodotto`, `prodotti.id_fornitore` e le colonne di lookup dei documenti non sono indicizzate, quindi ogni ricerca fa una scansione completa (su 49.000 movimenti si sente, e l'import esegue una SELECT per ogni riga di fattura). Mancano anche le foreign key e gli indici UNIQUE su `fornitori.partita_iva` e sulla tripla che identifica un documento: **la deduplica è garantita solo dal codice PHP**, non dal database. È la prima cosa che sistemerei.
- **Tipi di colonna approssimativi.** `denominazione`, `partita_iva` e `descrizione` sono `TEXT` dove basterebbe `VARCHAR` (e `TEXT` non è indicizzabile senza prefisso); i prezzi sono `DOUBLE` invece di `DECIMAL(10,2)`, che è il tipo corretto per il denaro. Resta anche una colonna `descrizione aggiuntiva` — con uno spazio nel nome — mai usata dal codice.
- **Zero test.** Il parsing XML è logica pura con input deterministico: è esattamente il caso in cui i test unitari costano poco e valgono molto.
- **Percorsi relativi fragili.** `require_once('db.php')` e `uploads/` dipendono dalla working directory, quindi lo stesso file si comporta in modo diverso a seconda di dove viene incluso. Servirebbe `__DIR__` ovunque, o un front controller unico.
- **Import sincrono e bloccante.** Su molte fatture l'import gira dentro la richiesta HTTP, senza barra di avanzamento e senza ripresa in caso di errore. Andrebbe spostato in un comando CLI.

---

## Roadmap (non sviluppata)

- [ ] Completare il refactoring ed eliminare le classi duplicate in `lib/`
- [ ] Completare la UI (pagina di inserimento, impostazioni)
- [ ] Adottare davvero il repository pattern e il singleton di connessione
- [ ] Grafico dell'andamento dei prezzi nel tempo per prodotto

---

## Licenza

MIT
