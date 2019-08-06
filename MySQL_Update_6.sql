-- phpMyAdmin SQL Dump
-- version 4.0.10deb1ubuntu0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 28. Apr 2019 um 12:36
-- Server Version: 10.3.13-MariaDB-1:10.3.13+maria~trusty-log
-- PHP-Version: 5.5.9-1ubuntu4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `iSpindle`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Settings`
--

CREATE TABLE IF NOT EXISTS `Settings` (
  `Section` varchar(64) CHARACTER SET utf8 NOT NULL,
  `Parameter` varchar(64) CHARACTER SET utf8 NOT NULL,
  `value` varchar(80) CHARACTER SET utf8 NOT NULL,
  `Description_DE` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `Description_EN` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`Parameter`,`Section`,`value`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

--
-- Daten für Tabelle `Settings`
--

INSERT INTO `Settings` (`Section`, `Parameter`, `value`, `Description_DE`, `Description_EN`) VALUES
('EMAIL', 'ALARMDELTA', '1', 'Limit für Delta Plato Alarm. Ist die Änderung der letzten 12 Stunden ist, als diese Wert wird ein email Alarm gesendet.', 'Limit for Delta Plato alarm. If change within past 12 hours becomes lower, Alarm will be sent.'),
('EMAIL', 'ALARMLOW', '4.5', 'Gravity Limit (Plato) für Email Alarm (z.B. 4 -> Alarm, wenn Gravity unter 4 fällt)', 'Lower Gravity Limit for Email Alarm in case acutal gravity is below limit'),
('EMAIL', 'ALARMSVG', '60', 'Vergärungsgrad Limit (%) für Email Alarm (z.B. 60 Alarm, wenn SVG 60 Prozent erreicht)', 'Limit for Email Alarm of apparent attenuation (e.g. 60 raises an alarm once apparent attenuation reaches 60 percent)'),
('BREWPILESS', 'BREWPILESSADDR', '0.0.0.0:80', 'IPADRESSE:PORT des BrewPiLess Servers', 'IPADDRESS:PORT of the BrewPiLess Servers'),
('CRAFTBEERPI3', 'CRAFTBEERPI3ADDR                 ', 'localhost:5000', 'IPADRESSE:PORT des Craftbeerpi3 servers', 'IPADDRESS:PORT of the Craftbeerpi3 servers'),
('CRAFTBEERPI3', 'CRAFTBEERPI3_SEND_ANGLE', '0', 'Falls 1: Weiterleitung des Winkels anstelle der berechneten Gravity. CBPI3 kann dann gravity berechnen', 'If 1: raw angle will be send instead of gravity. Polynom inside craftbeerpi3 can be used for gravity calculation'),
('CSV', 'DATETIME', '1', 'Ein Wert von 1 schreibt einen Excel kompatiblen Zeitstempel in das CSV file', 'Leave this at 1 to include Excel compatible timestamp in CSV'),
('CSV', 'DELIMITER', ';', 'Trennzeichen, dass fuer CSV File genutzt wird', 'Delimiter used for CSV file'),
('EMAIL', 'ENABLEALARMLOW', '1', 'Sende email Alarm, wenn Gravity unter einen bestimmern Wert fällt (0:nein 1: ja)', 'Enable Alarm for low gravity'),
('EMAIL', 'ENABLEALARMSVG', '1', 'Sende email Alarm, wenn Vergärungsgrad oberhalb eines bestimmern Werts liegt (0:nein 1: ja)', 'Enable Alarm for apparent attenuation above threshhold value (0:no 1:yes)'),
('EMAIL', 'ENABLESTATUS', '1', 'Sende tägliche Status Email (0:nein 1: ja)', 'Send daily status email (1: yes 0: no)'),
('ADVANCED', 'ENABLE_ADDCOLS', '0', 'Enable dynamic columns (do not use this unless you''re a developer)', 'Enable dynamic columns (do not use this unless you''re a developer)'),
('EMAIL', 'ENABLE_ALARMDELTA', '0', 'Alarm, wenn Plato Veränderung innerhalb der letzten 12 Stunden unter limit faellt (An: 1)', 'Alarm for delta plato (On: 1) If change of plato within past 12 hours falls below setting, email alarm will be sent'),
('BREWPILESS', 'ENABLE_BREWPILESS', '0', 'Weiterleitung an BrewPiLess', 'Forward to BrewPiLess'),
('CRAFTBEERPI3', 'ENABLE_CRAFTBEERPI3', '0', 'Weiterleitung an CraftbeerPI3', 'Enable forwarding to CraftbeerPi3'),
('CSV', 'ENABLE_CSV', '0', 'Schreiben der Daten in CSV File (1: ja 0: nein)', 'Write Data to CSV file (1: yes 0: no)'),
('FERMENTRACK', 'ENABLE_FERMENTRACK', '0', 'Weiterleitung der Daten an Fermentrack', 'Forward data to Fermentrack'),
('FORWARD', 'ENABLE_FORWARD', '0', 'Weiterleitung der Daten an öffentlichen Server oder andere Instanz vom TCP Server', 'Forward to public server or other relay (i.e. another instance of this script)'),
('REMOTECONFIG', 'ENABLE_REMOTECONFIG', '0', 'Bei 1: Konfiguration wird vom TCP Server an die Spindel während eines Datentransfers gesendet (noch in der Testung)', 'If enabled, config from TCP server will be send to Spindle during data transfer once (still under testing)'),
('UBIDOTS', 'ENABLE_UBIDOTS', '0', 'Weiterleitung der Daten an Ubidots (1: an 0: aus)', '1 to enable output to ubidots'),
('FERMENTRACK', 'FERMENTRACKADDR', '0.0.0.0', 'IP Adresse des Fermentrack Servers', 'IP Address of the Fermentrack Server'),
('FERMENTRACK', 'FERMENTRACKPORT', '80', 'Port des Fermentrack Servers', 'Port of Fermentrack Server'),
('FERMENTRACK', 'FERMENTRACK_TOKEN', 'my_token', 'Token für Fermentrack Server', 'Token for Fermentrack Server'),
('FERMENTRACK', 'FERM_USE_ISPINDLE_TOKEN', '0', 'Verwendung des ISpindle Tokens zur Weiterleitung', 'Use token from iSpindle for data forwarding'),
('FORWARD', 'FORWARDADDR', '0', 'IP Adresse des anderen Servers', 'IP Adress of the other server'),
('FORWARD', 'FORWARDPORT', '9501', 'Port des anderen Servers', 'Port of the remote server'),
('EMAIL', 'FROMADDR', 'your.mail@gmail.com', 'Email Adresse von der eine Nachricht versendet werden soll.', 'email, from which the '),
('GENERAL', 'HOST', '0.0.0.0', 'Erlaubter IP Bereich. 0.0.0.0 ermöglicht Verbindungen von überall', 'Allowed IP range. Leave at 0.0.0.0 to allow connections from anywhere'),
('GENERAL', 'LANGUAGE', 'DE', 'Verwendete Sprache (DE für Deutsch, EN for Englisch)', 'Displayed Language (DE for German, EN for English)'),
('CSV', 'NEWLINE', '\\r\\n', 'Zeichen fuer Zeilenumbruch', 'Newline characters'),
('CSV', 'OUTPATH', '/home/pi/iSpindl-srv/csv/', 'Pfad zum schreiben des CSV files. Der filename lautet dann name_id.csv', 'CSV output file path; filename will be name_id.csv'),
('EMAIL', 'PASSWD', 'yourpassword', 'SMTP Server Passwort', 'SMTP server password'),
('GENERAL', 'PORT', '9501', 'Port zur Kommunikation zwischen Spindel und TCP Server (muss auch in der Spindel hinterlegt sein)', 'TCP Port to listen to (to be used in iSpindle config as well)'),
('EMAIL', 'SMTPPORT', '587', 'SMTP Server Port (z.B. 587)', 'smpt server port'),
('EMAIL', 'SMTPSERVER', 'smtp.gmail.com', 'SMTP Server Adresse (z.B. smtp.gmail.com)', 'smtp server addresss'),
('EMAIL', 'TIMEFRAMESTATUS', '2', 'Zeitraum der letzten Datenübermittlung in Tagen, wenn ein Statusalarm gesendet werden soll', 'Timeframe in days when last spindel data was send and should be displayed.'),
('EMAIL', 'TIMESTATUS', '6', 'Uhrzeit in vollen Stunden für tägliche Status Email (z.B. 6 fuer 6 Uhr morgens)', 'Set time for Status email around full hour. e.g. 6 means 6:00'),
('EMAIL', 'TOADDR', 'your.mail@gmail.com', 'Email Adresse, an die eine Nachricht gesendet werden soll', 'email address to which the alarm email is sent'),
('UBIDOTS', 'UBI_TOKEN', 'my_token', 'UBIDOTS Token. Siehe Anleitung oder ubidots.com', 'global ubidots token, see manual or ubidots.com'),
('UBIDOTS', 'UBI_USE_ISPINDLE_TOKEN', '0', 'Benutzung des in der Spindel gespeicherten Tokens zur Weiterleitung an Ubidots ', '1 to use "token" field in iSpindle config (overrides UBI_TOKEN)');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
