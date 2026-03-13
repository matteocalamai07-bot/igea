-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mar 13, 2026 alle 12:09
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

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
CREATE DATABASE IF NOT EXISTS `terranova`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `terranova`;
-- --------------------------------------------------------

--
-- Struttura della tabella `alimenti`
--

CREATE TABLE `alimenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `alimenti`
--

INSERT INTO `alimenti` (`id`, `nome`) VALUES
(1, 'Causticum D30'),
(2, 'Ca-Mg-Fosfato'),
(3, 'Fenolo'),
(4, 'Anatra'),
(5, 'Agnello'),
(6, 'Gallina'),
(7, 'Vitello'),
(8, 'Tacchino'),
(9, 'Manzo'),
(10, 'Carne Maiale'),
(11, 'Grassi Maiale'),
(12, 'Albume uovo'),
(13, 'Tuorlo uovo'),
(14, 'Latte di mucca'),
(15, 'Siero Latte'),
(16, 'Formaggio'),
(17, 'Yogurt'),
(18, 'Ricotta'),
(19, 'Latte Cagliato'),
(20, 'Margarina'),
(21, 'Merluzzo'),
(22, 'Trota'),
(23, 'Carpa'),
(24, 'Salmone'),
(25, 'Rombo'),
(26, 'Aringa'),
(27, 'Baccalà'),
(28, 'Sardina'),
(29, 'Sogliola'),
(30, 'Tonno'),
(31, 'Astice'),
(32, 'Gambero'),
(33, 'Ostrica'),
(34, 'Cozze'),
(35, 'Polpo'),
(36, 'Calamaro'),
(37, 'Ananas'),
(38, 'Mela'),
(39, 'Arancia'),
(40, 'Banana'),
(41, 'Pera'),
(42, 'Fragola'),
(43, 'Pompelmo'),
(44, 'Ribes'),
(45, 'Amarena'),
(46, 'Ciliegia'),
(47, 'Mandarino'),
(48, 'Pesca'),
(49, 'Uva'),
(50, 'Limone'),
(51, 'Prugna/Susina'),
(52, 'Kiwi'),
(53, 'Frutta Secca'),
(54, 'Nocciola'),
(55, 'Noci'),
(56, 'Arachide'),
(57, 'Cacao'),
(58, 'Cioccolato'),
(59, 'Marzapane'),
(60, 'Olio di mais'),
(61, 'Avocado'),
(62, 'Cavolfiore'),
(63, 'Piselli'),
(64, 'Patata'),
(65, 'Aglio'),
(66, 'Carota'),
(67, 'Peperone'),
(68, 'Prezzemolo'),
(69, 'Sedano'),
(70, 'Asparagi'),
(71, 'Melanzana'),
(72, 'Funghi'),
(73, 'Spinaci'),
(74, 'Pomodoro'),
(75, 'Cavolo'),
(76, 'Cipolla'),
(77, 'Ceci'),
(78, 'Lenticchie'),
(79, 'Fagioli'),
(80, 'Gelatina'),
(81, 'Farina Orzo'),
(82, 'Farina Avena'),
(83, 'Farina Mais'),
(84, 'Riso'),
(85, 'Farina Segale'),
(86, 'Farina Soia'),
(87, 'Crusca f.'),
(88, 'F. frumento'),
(89, 'Farina farro'),
(90, 'Lievito'),
(91, 'Glutine'),
(92, 'Birra'),
(93, 'Caffè'),
(94, 'Lattosio'),
(95, 'Senape'),
(96, 'Te\''),
(97, 'Vino rosso'),
(98, 'Vino bianco'),
(99, 'Zucchero'),
(100, 'Glutammato'),
(101, 'Aspartame'),
(102, 'Acido Formico'),
(103, 'Acidobenzoico'),
(104, 'Esametil'),
(105, 'Nitrato Sodio'),
(106, 'Nitrito Sodio'),
(107, 'Solfito sodio'),
(108, 'Acido E-216'),
(109, 'Acido Sorbico'),
(110, 'Anice'),
(111, 'Pepe Caienna'),
(112, 'Cannella'),
(113, 'Curry'),
(114, 'Maggiorana'),
(115, 'Noce moscata'),
(116, 'Timo'),
(117, 'Pepe'),
(118, 'Menta'),
(119, 'Rosmarino'),
(120, 'Salvia');

-- --------------------------------------------------------

--
-- Struttura della tabella `alimenti_sospesi`
--

CREATE TABLE `alimenti_sospesi` (
  `fk_alimenti` int(11) NOT NULL,
  `fk_visita` int(11) NOT NULL,
  `note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `anamnesi`
--

CREATE TABLE `anamnesi` (
  `id` int(11) NOT NULL,
  `allergie` varchar(10) NOT NULL,
  `dettagli_allergie` text DEFAULT NULL,
  `fumo` varchar(10) NOT NULL,
  `dettagli_fumo` text DEFAULT NULL,
  `alcol` varchar(10) NOT NULL,
  `dettagli_alcol` text DEFAULT NULL,
  `patologie` varchar(10) NOT NULL,
  `dettagli_patologie` text DEFAULT NULL,
  `interventi` varchar(10) NOT NULL,
  `dettagli_interventi` text DEFAULT NULL,
  `esami` varchar(10) NOT NULL,
  `dettagli_esami` text DEFAULT NULL,
  `fk_paziente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `appuntamento`
--

CREATE TABLE `appuntamento` (
  `id` int(11) NOT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp(),
  `fk_paziente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `attivita_fisica`
--

CREATE TABLE `attivita_fisica` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `attivita_visita`
--

CREATE TABLE `attivita_visita` (
  `fk_visita` int(11) NOT NULL,
  `fk_attivita` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `domande`
--

CREATE TABLE `domande` (
  `id` int(11) NOT NULL,
  `domanda` text NOT NULL,
  `risposta` text NOT NULL,
  `nota` text NOT NULL,
  `fk_visita` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `farmaci`
--

CREATE TABLE `farmaci` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `farmaci_prescritti`
--

CREATE TABLE `farmaci_prescritti` (
  `fk_visita` int(11) NOT NULL,
  `fk_farmaci` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `integratori`
--

CREATE TABLE `integratori` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `integratori_prescritti`
--

CREATE TABLE `integratori_prescritti` (
  `fk_visita` int(11) NOT NULL,
  `fk_integratori` int(11) NOT NULL
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
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `datanascita` date NOT NULL,
  `citta` varchar(50) NOT NULL,
  `indirizzo` varchar(200) NOT NULL,
  `civico` int(11) NOT NULL,
  `professione` varchar(50) DEFAULT NULL,
  `email` varchar(300) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sonno`
--

CREATE TABLE `sonno` (
  `id` int(11) NOT NULL,
  `ore` varchar(50) NOT NULL,
  `risvegli` text NOT NULL,
  `difficolta` text NOT NULL,
  `qualita` text NOT NULL,
  `fk_visita` int(11) NOT NULL
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
-- Struttura della tabella `storico_attivita`
--

CREATE TABLE `storico_attivita` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL DEFAULT current_timestamp(),
  `tipo` varchar(100) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `supporti`
--

CREATE TABLE `supporti` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `supporti_prescritti`
--

CREATE TABLE `supporti_prescritti` (
  `fk_visita` int(11) NOT NULL,
  `fk_supporti` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `terapie`
--

CREATE TABLE `terapie` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `terapie_prescritte`
--

CREATE TABLE `terapie_prescritte` (
  `fk_terapie` int(11) NOT NULL,
  `fk_visita` int(11) NOT NULL,
  `note` text NOT NULL
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
-- Indici per le tabelle `alimenti`
--
ALTER TABLE `alimenti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `alimenti_sospesi`
--
ALTER TABLE `alimenti_sospesi`
  ADD PRIMARY KEY (`fk_visita`,`fk_alimenti`),
  ADD KEY `fk_sospesi_alimenti` (`fk_alimenti`);

--
-- Indici per le tabelle `anamnesi`
--
ALTER TABLE `anamnesi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_paziente` (`fk_paziente`);

--
-- Indici per le tabelle `appuntamento`
--
ALTER TABLE `appuntamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_paziente` (`fk_paziente`);

--
-- Indici per le tabelle `attivita_fisica`
--
ALTER TABLE `attivita_fisica`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `attivita_visita`
--
ALTER TABLE `attivita_visita`
  ADD PRIMARY KEY (`fk_visita`,`fk_attivita`),
  ADD KEY `fk_attivita` (`fk_attivita`);

--
-- Indici per le tabelle `domande`
--
ALTER TABLE `domande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visita` (`fk_visita`);

--
-- Indici per le tabelle `farmaci`
--
ALTER TABLE `farmaci`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `farmaci_prescritti`
--
ALTER TABLE `farmaci_prescritti`
  ADD PRIMARY KEY (`fk_visita`,`fk_farmaci`),
  ADD KEY `fk_farmaci` (`fk_farmaci`);

--
-- Indici per le tabelle `integratori`
--
ALTER TABLE `integratori`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `integratori_prescritti`
--
ALTER TABLE `integratori_prescritti`
  ADD PRIMARY KEY (`fk_visita`,`fk_integratori`),
  ADD KEY `fk_integratori` (`fk_integratori`);

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
-- Indici per le tabelle `sonno`
--
ALTER TABLE `sonno`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visita` (`fk_visita`);

--
-- Indici per le tabelle `stato_psico-fisico`
--
ALTER TABLE `stato_psico-fisico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visita` (`fk_visita`);

--
-- Indici per le tabelle `storico_attivita`
--
ALTER TABLE `storico_attivita`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `supporti`
--
ALTER TABLE `supporti`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `supporti_prescritti`
--
ALTER TABLE `supporti_prescritti`
  ADD PRIMARY KEY (`fk_visita`,`fk_supporti`),
  ADD KEY `fk_supporti` (`fk_supporti`);

--
-- Indici per le tabelle `terapie`
--
ALTER TABLE `terapie`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `terapie_prescritte`
--
ALTER TABLE `terapie_prescritte`
  ADD PRIMARY KEY (`fk_visita`,`fk_terapie`),
  ADD KEY `fk_tp_terapie` (`fk_terapie`);

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
-- AUTO_INCREMENT per la tabella `alimenti`
--
ALTER TABLE `alimenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT per la tabella `anamnesi`
--
ALTER TABLE `anamnesi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `appuntamento`
--
ALTER TABLE `appuntamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `attivita_fisica`
--
ALTER TABLE `attivita_fisica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `domande`
--
ALTER TABLE `domande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `farmaci`
--
ALTER TABLE `farmaci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `integratori`
--
ALTER TABLE `integratori`
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
-- AUTO_INCREMENT per la tabella `sonno`
--
ALTER TABLE `sonno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `stato_psico-fisico`
--
ALTER TABLE `stato_psico-fisico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `storico_attivita`
--
ALTER TABLE `storico_attivita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `supporti`
--
ALTER TABLE `supporti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `terapie`
--
ALTER TABLE `terapie`
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
-- Limiti per la tabella `alimenti_sospesi`
--
ALTER TABLE `alimenti_sospesi`
  ADD CONSTRAINT `fk_sospesi_alimenti` FOREIGN KEY (`fk_alimenti`) REFERENCES `alimenti` (`id`),
  ADD CONSTRAINT `fk_sospesi_visita` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `anamnesi`
--
ALTER TABLE `anamnesi`
  ADD CONSTRAINT `anamnesi_ibfk_1` FOREIGN KEY (`fk_paziente`) REFERENCES `paziente` (`id`);

--
-- Limiti per la tabella `appuntamento`
--
ALTER TABLE `appuntamento`
  ADD CONSTRAINT `fk_paziente` FOREIGN KEY (`fk_paziente`) REFERENCES `paziente` (`id`);

--
-- Limiti per la tabella `attivita_visita`
--
ALTER TABLE `attivita_visita`
  ADD CONSTRAINT `fk_attivita` FOREIGN KEY (`fk_attivita`) REFERENCES `attivita_fisica` (`id`),
  ADD CONSTRAINT `fk_av_visita` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `domande`
--
ALTER TABLE `domande`
  ADD CONSTRAINT `fk_visita` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `farmaci_prescritti`
--
ALTER TABLE `farmaci_prescritti`
  ADD CONSTRAINT `fk_farmaci` FOREIGN KEY (`fk_farmaci`) REFERENCES `farmaci` (`id`),
  ADD CONSTRAINT `fk_fp_visita` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `integratori_prescritti`
--
ALTER TABLE `integratori_prescritti`
  ADD CONSTRAINT `fk_integratori` FOREIGN KEY (`fk_integratori`) REFERENCES `integratori` (`id`),
  ADD CONSTRAINT `fk_ip_visita` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `osservazioni_finali`
--
ALTER TABLE `osservazioni_finali`
  ADD CONSTRAINT `osservazioni_finali_ibfk_1` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `sonno`
--
ALTER TABLE `sonno`
  ADD CONSTRAINT `sonno_ibfk_1` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `stato_psico-fisico`
--
ALTER TABLE `stato_psico-fisico`
  ADD CONSTRAINT `stato_psico-fisico_ibfk_1` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `supporti_prescritti`
--
ALTER TABLE `supporti_prescritti`
  ADD CONSTRAINT `fk_sp_visita` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`),
  ADD CONSTRAINT `fk_supporti` FOREIGN KEY (`fk_supporti`) REFERENCES `supporti` (`id`);

--
-- Limiti per la tabella `terapie_prescritte`
--
ALTER TABLE `terapie_prescritte`
  ADD CONSTRAINT `fk_tp_terapie` FOREIGN KEY (`fk_terapie`) REFERENCES `terapie` (`id`),
  ADD CONSTRAINT `fk_tp_visita` FOREIGN KEY (`fk_visita`) REFERENCES `visita` (`id`);

--
-- Limiti per la tabella `visita`
--
ALTER TABLE `visita`
  ADD CONSTRAINT `visita_ibfk_1` FOREIGN KEY (`fk_paziente`) REFERENCES `paziente` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
