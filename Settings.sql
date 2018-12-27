-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Erstellungszeit: 27. Dez 2018 um 18:50
-- Server-Version: 10.3.11-MariaDB-1:10.3.11+maria~bionic
-- PHP-Version: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `iSpindle`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Settings`
--

CREATE TABLE `Settings` (
  `Section` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `Parameter` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `value` varchar(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `Description_DE` varchar(128) DEFAULT NULL,
  `Description_EN` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `Settings`
--

INSERT INTO `Settings` (`Section`, `Parameter`, `value`, `Description_DE`, `Description_EN`) VALUES
('EMAIL', 'AlarmLow', '4', 'Gravity Limit (Plato) fuer Email Alarm (z.B. 4 -> Alarm, wenn Gravity unter 4 faellt)', NULL),
('EMAIL', 'EnableAlarmLow', '1', 'Sende email Alarm, wenn Gravity unter einen bestimmern Wert faellt (0:nein 1: ja)', NULL),
('EMAIL', 'EnableStatus', '1', 'Sende Taegliche Status Email (0:nein 1: ja)', NULL),
('EMAIL', 'TimeFrameStatus', '20', 'Zeitraum der letzten Datenuebermittlung in Tagen, wenn ein Statusalarm gesendet werden soll', NULL),
('EMAIL', 'TimeStatus', '6', 'Uhrzeit in vollen Stunden fuer taegliche Status Email (z.B. 6 fuer 6 Uhr morgens)', NULL),
('EMAIL', 'fromaddr', '', 'Email Adresse von der eine Nachricht versendet werden soll.', NULL),
('EMAIL', 'passwd', '', 'SMTP Server Passwort', NULL),
('EMAIL', 'smtpport', '', 'SMTP Server Port (z.B. 587)', NULL),
('EMAIL', 'smtpserver', '', 'SMTP Server Adresse (z.B. smtp.gmail.com)', NULL),
('EMAIL', 'toaddr', '', 'Email Adresse, an die eine Nachricht gesendet werden soll', NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Settings`
--
ALTER TABLE `Settings`
  ADD PRIMARY KEY (`Parameter`,`Section`,`value`) USING BTREE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
