-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Feb 11, 2026 alle 13:25
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `terranova`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `anamnesi`
--

CREATE TABLE `anamnesi` (
  `id` int(11) NOT NULL,
  `allergie` tinyint(1) NOT NULL,
  `dettagli_allergie` text DEFAULT NULL,
  `fumo` tinyint(1) NOT NULL,
  `dettagli_fumo` text DEFAULT NULL,
  `alcol` tinyint(1) NOT NULL,
  `dettagli_alcol` text DEFAULT NULL,
  `patologie` tinyint(1) NOT NULL,
  `dettagli_patologie` text DEFAULT NULL,
  `interventi` tinyint(1) NOT NULL,
  `dettagli_interventi` text DEFAULT NULL,
  `esami` tinyint(1) NOT NULL,
  `dettagli_esami` text DEFAULT NULL,
  `fk_paziente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `osservazioni_finali`
--

CREATE TABLE `osservazioni_finali` (
  `id` int(11) NOT NULL,
  `osservazione` text NOT NULL,
  `fk_visita` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `paziente`
--

CREATE TABLE `paziente` (
  `id` int(11) NOT NULL,
  `nome` text NOT NULL,
  `cognome` text NOT NULL,
  `datanascita` date NOT NULL,
  `citta` text NOT NULL,
  `indirizzo` text NOT NULL,
  `civico` int(11) NOT NULL,
  `proffessione` text NOT NULL,
  `email` text DEFAULT NULL,
  `telefono` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `stato_psico-fisico`
--

CREATE TABLE `stato_psico-fisico` (
  `id` int(11) NOT NULL,
  `ansia` text NOT NULL,
  `umore` text NOT NULL,
  `motivazione` text NOT NULL,
  `concentrazione` text NOT NULL,
  `fk_visita` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `visita`
--

CREATE TABLE `visita` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL DEFAULT current_timestamp(),
  `livello_stress` int(11) NOT NULL,
  `alimentazione` text NOT NULL,
  `fk_paziente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `anamnesi`
--
ALTER TABLE `anamnesi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_paziente` (`fk_paziente`);

--
-- Indici per le tabelle `osservazioni_finali`
--
ALTER TABLE `osservazioni_finali`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visita` (`fk_visita`);

--
-- Indici per le tabelle `paziente`
--
ALTER TABLE `paziente`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `stato_psico-fisico`
--
ALTER TABLE `stato_psico-fisico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visita` (`fk_visita`);

--
-- Indici per le tabelle `visita`
--
ALTER TABLE `visita`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_paziente` (`fk_paziente`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `anamnesi`
--
ALTER TABLE `anamnesi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `osservazioni_finali`
--
ALTER TABLE `osservazioni_finali`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `paziente`
--
ALTER TABLE `paziente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `stato_psico-fisico`
--
ALTER TABLE `stato_psico-fisico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `visita`
--
ALTER TABLE `visita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `anamnesi`
--
ALTER TABLE `anamnesi`
  ADD CONSTRAINT `anamnesi_ibfk_1` FOREIGN KEY (`fk_paziente`) REFERENCES `paziente` (`id`);

--
-- Limiti per la tabella `osservazioni_finali`
--
ALTER TABLE `osservazioni_finali`
  ADD CONSTRAINT `osservazioni_finali_ibfk_1` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `stato_psico-fisico`
--
ALTER TABLE `stato_psico-fisico`
  ADD CONSTRAINT `stato_psico-fisico_ibfk_1` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `visita`
--
ALTER TABLE `visita`
  ADD CONSTRAINT `visita_ibfk_1` FOREIGN KEY (`fk_paziente`) REFERENCES `paziente` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
