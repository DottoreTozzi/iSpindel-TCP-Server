-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Erstellungszeit: 22. Jan 2019 um 07:02
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
-- Tabellenstruktur für Tabelle `Strings`
--

CREATE TABLE `Strings` (
  `File` varchar(64) NOT NULL,
  `Field` varchar(64) NOT NULL,
  `Description_DE` varchar(128) NOT NULL,
  `Description_EN` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `Strings`
--

INSERT INTO `Strings` (`File`, `Field`, `Description_DE`, `Description_EN`) VALUES
('angle', 'first_y', 'Winkel', 'Angle'),
('angle', 'second_y', 'Temperatur', 'Temperature'),
('angle', 'timetext', 'Temperatur und Winkel der letzten', 'Temperature and angle of the last '),
('angle', 'timetext_reset', 'Temperatur und Winkel seit dem letzten Reset: ', 'Temperature and angle since last reset: '),
('angle', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('angle_ma', 'first_y', 'Winkel (geglättet)', 'Angle (moving average)'),
('angle_ma', 'second_y', 'Temperatur', 'Temperature'),
('angle_ma', 'timetext', 'Temperatur und Winkel der letzten', 'Temperature and angle of the last '),
('angle_ma', 'timetext_reset', 'Temperatur und Winkel seit dem letzten Reset: ', 'Temperature and angle since last reset: '),
('angle_ma', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('battery', 'diagram_battery', 'Volt', 'Voltage'),
('battery', 'header_battery', 'Aktueller Ladezustand:', 'Battery condition:'),
('batterytrend', 'first_y', 'Batteriespannung', 'Battery voltage'),
('batterytrend', 'second_y', 'WiFi Signal', 'WiFi reception'),
('batterytrend', 'timetext', 'Batteriespannung und WiFi Signal der letzten', 'Battery voltage and WiFi reception of the last '),
('batterytrend', 'timetext_reset', 'Batteriespannung und WiFi Signal seit dem letzten Reset: ', 'Battery voltage and WiFi reception since last reset: '),
('batterytrend', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('calibration', 'constant1', 'Konstante 1:', 'Constant 1:'),
('calibration', 'constant2', 'Konstante 2:', 'Constant 2:'),
('calibration', 'constant3', 'Konstante 3:', 'Constant 3:'),
('calibration', 'enter_constants', 'Konstanten eingeben:', 'Please enter constants:'),
('calibration', 'header', 'Aktualisieren der Kalibrierung für:', 'Update calibration for:'),
('calibration', 'send', 'Kalibrierung an DB senden', 'Send calibration to database'),
('calibration', 'stop', 'Zurück', 'Go back'),
('calibration', 'window_alert_update', 'Kalibrierung an Datenbank gesendet!', 'Calibration was send to database!'),
('diagram', 'header_no_data_1', 'Keine Daten von', 'No data from'),
('diagram', 'header_no_data_2', 'in diesem Zeitraum. Bitte noch weitere', 'from this timeframe. Please go'),
('diagram', 'header_no_data_3', 'Tage zurueckgehen.', 'more days back.'),
('diagram', 'not_calibrated', 'ist nicht kalibriert.', 'is not calibrated.'),
('diagram', 'recipe_name', 'Sudname:', 'Recipe:'),
('diagram', 'timetext_days', 'Tag(e), ', 'day(s), '),
('diagram', 'timetext_hours', 'Stunde(n).', 'Hour(s).'),
('diagram', 'timetext_weeks', 'Woche(n), ', 'Week(s), '),
('index', 'chart_filename_01', 'Status (Batterie, Winkel, Temperatur)', 'Show Status (Battery, Angle, Temperature)'),
('index', 'chart_filename_02', 'Batteriezustand', 'Show Battery Condition'),
('index', 'chart_filename_03', 'Netzwerk Empfangsqualität', 'Show WiFi quality'),
('index', 'chart_filename_04', 'Extrakt und Temperatur (RasPySpindel)', 'Gravity and temperature (RasPySpindel)'),
('index', 'chart_filename_05', 'Extrakt und Temperatur (RasPySpindel), Geglättet', 'Gravity and temperature (RasPySpindel), Moving Average'),
('index', 'chart_filename_06', 'Tilt und Temperatur', 'Tilt and temperature'),
('index', 'chart_filename_07', 'Tilt und Temperatur, Geglättet', 'Tilt and temperature, Moving Average'),
('index', 'chart_filename_08', 'Extrakt und Temperatur (iSpindel Polynom)', 'Gravity and temperature (iSpindle Polynom)'),
('index', 'chart_filename_09', 'Gärbeginn Zeitpunkt setzen', 'Set Fermentation Start'),
('index', 'chart_filename_10', 'Vergärungsgrad', 'Apparent attenuation'),
('index', 'chart_filename_11', 'Änderung (Delta) Extrakt innerhalb 12 Stunden Anzeigen', 'Delta gravity  (12 hrs interval)'),
('index', 'chart_filename_12', 'Verlauf Batteriespannung/WiFi anzeigen', 'Battery / Wifi trend'),
('index', 'chart_filename_13', 'Spindel im TCP Server Kalibrieren', 'Calibrate Spindel in TCP Server'),
('index', 'days_history', 'Tage Historie\r\n', 'Days history'),
('index', 'diagram_selection', 'Diagramm Auswahl(Tage):', 'Diagram selection (days):'),
('index', 'or', 'oder:', 'or:'),
('index', 'recipe_name', 'Optional Sudnamen eingeben:', 'Enter optional recipe name:'),
('index', 'reset_flag', 'Daten seit zuletzt gesetztem \"Reset\" Flag zeigen', 'Show data since last set \'Reset\''),
('index', 'server_not_running', 'Warnung: TCP Server läuft nicht!', 'Warning: TCP Server is not running!'),
('index', 'server_running', 'TCP Server läuft mit PID: ', 'TCP Server is running with PID: '),
('index', 'server_settings', 'TCP Server Settings Editieren', 'Edit TCP Server Settings'),
('index', 'show_diagram', 'Anzeigen', 'Show Diagram'),
('plato', 'first_y', 'Extrakt % w/w (Spindel)', 'Extract % w/w (Spindle)'),
('plato', 'second_y', 'Temperatur', 'Temperature'),
('plato', 'timetext', 'Temperatur und Extraktgehalt (Spindel) der letzten', 'Temperature and extract (Spindle) of the last '),
('plato', 'timetext_reset', 'Temperatur und Extraktgehalt (Spindel) seit dem letzten Reset: ', 'Temperature and extract (Spindle) since last reset: '),
('plato', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('plato4', 'first_y', 'Extrakt % w/w', 'Extract % w/w'),
('plato4', 'second_y', 'Temperatur', 'Temperature'),
('plato4', 'timetext', 'Temperatur und Extraktgehalt der letzten', 'Temperature and extract of the last '),
('plato4', 'timetext_reset', 'Temperatur und Extraktgehalt seit dem letzten Reset: ', 'Temperature and extract since last reset: '),
('plato4', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('plato4_delta', 'first_y', 'Delta Extrakt % w/w', 'Delta extract % w/w'),
('plato4_delta', 'second_y', 'Temperatur', 'Temperature'),
('plato4_delta', 'timetext', 'Temperatur und Delta Extraktgehalt der letzten', 'Temperature and delta extract of the last '),
('plato4_delta', 'timetext_reset', 'Temperatur und Delta Extraktgehalt seit dem letzten Reset: ', 'Temperature and delta extract since last reset: '),
('plato4_delta', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('plato4_ma', 'first_y', 'Extrakt % w/w (geglaettet)', 'Extract % w/w (moving average)'),
('plato4_ma', 'second_y', 'Temperatur', 'Temperature'),
('plato4_ma', 'timetext', 'Temperatur und Extraktgehalt der letzten', 'Temperature and extract of the last '),
('plato4_ma', 'timetext_reset', 'Temperatur und Extraktgehalt seit dem letzten Reset: ', 'Temperature and extract since last reset: '),
('plato4_ma', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('settings', 'description', 'Beschreibung', 'Description'),
('settings', 'problem', 'Probleme beim Schreiben der Settings', 'Problem with writing settings'),
('settings', 'select_section', 'Section Auswahl', 'Select Section'),
('settings', 'send', 'Settings in DB schreiben', 'Send settings to database'),
('settings', 'stop', 'Zurück', 'Go back'),
('settings', 'window_alert_update', 'Settings aktualisiert!', 'Settings were updated!'),
('status', 'diagram_angle', 'Grad', 'Degree'),
('status', 'diagram_battery', 'Volt', 'Voltage'),
('status', 'diagram_temperature', '°', '°'),
('status', 'header_angle', 'Winkel', 'Angle'),
('status', 'header_battery', 'Akku', 'Battery'),
('status', 'header_temperature', 'Temperatur', 'Temperature'),
('svg_ma', 'first_y', 'Scheinbarer Vergaerungsgrad %', 'Apparent attentuation %'),
('svg_ma', 'second_y', 'Temperatur', 'Temperature'),
('svg_ma', 'timetext', 'Temperatur und scheinbarer Vergaerungsgrad der letzten', 'Temperature and extract of the last '),
('svg_ma', 'timetext_reset', 'Temperatur und scheinbarer Vergaerungsgrad seit dem letzten Reset: ', 'Temperature and apparent attenuation since last reset: '),
('svg_ma', 'x_axis', 'Datum / Uhrzeit', 'Date / Time'),
('wifi', 'header', 'Aktuelle WiFi Empfangsqualitaet: ', 'Current Wifi reception: ');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Strings`
--
ALTER TABLE `Strings`
  ADD UNIQUE KEY `File` (`File`,`Field`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
