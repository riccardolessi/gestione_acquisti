-- Struttura del database `inventario`
--
-- Questo file riflette lo schema reale usato dall'applicazione, senza modifiche.
-- I limiti noti (assenza di indici secondari, vincoli di integrità e UNIQUE)
-- sono documentati nel README: la deduplica è garantita solo dal codice PHP.

SET NAMES utf8mb4;

CREATE TABLE `fornitori` (
  `id`             int NOT NULL AUTO_INCREMENT,
  `denominazione`  text,
  `partita_iva`    text,
  `codice_fiscale` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `documenti` (
  `id`               int NOT NULL AUTO_INCREMENT,
  `numero_documento` varchar(50) DEFAULT NULL,
  `data_documento`   date        DEFAULT NULL,
  `id_fornitore`     int         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `prodotti` (
  `id`                     int  NOT NULL AUTO_INCREMENT,
  `descrizione`            text NOT NULL,
  `descrizione aggiuntiva` text,                    -- colonna mai usata dal codice
  `codice_articolo`        varchar(255) DEFAULT NULL,
  `id_fornitore`           int          DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `movimenti` (
  `id`            int    NOT NULL AUTO_INCREMENT,
  `id_prodotto`   int    DEFAULT NULL,
  `prezzo`        double DEFAULT NULL,
  `quantita`      double DEFAULT NULL,
  `um`            varchar(30) DEFAULT NULL,
  `data_acquisto` date        DEFAULT NULL,
  `id_documento`  int         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
