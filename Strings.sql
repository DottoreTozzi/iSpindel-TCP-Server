-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Erstellungszeit: 30. Dez 2019 um 01:03
-- Server-Version: 10.4.7-MariaDB-debug
-- PHP-Version: 7.3.7

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
  `File` varchar(64) CHARACTER SET utf8 NOT NULL,
  `Field` varchar(64) CHARACTER SET utf8 NOT NULL,
  `Description_DE` varchar(1024) CHARACTER SET utf8 NOT NULL,
  `Description_EN` varchar(1024) CHARACTER SET utf8 NOT NULL,
  `Description_IT` varchar(1024) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

--
-- Daten für Tabelle `Strings`
--

INSERT INTO `Strings` (`File`, `Field`, `Description_DE`, `Description_EN`, `Description_IT`) VALUES
('angle', 'first_y', 'Winkel', 'Angle', 'Angolo'),
('angle', 'second_y', 'Temperatur', 'Temperature', 'Temperatura'),
('angle', 'timetext', 'Temperatur und Winkel der letzten', 'Temperature and angle of the last ', 'Temperatura e angolo da'),
('angle', 'timetext_reset', 'Temperatur und Winkel seit dem letzten Reset: ', 'Temperature and angle since last reset: ', 'Temperatura e angolo dall\'ultimo reset: '),
('angle', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Orario'),
('angle_ma', 'first_y', 'Winkel (geglättet)', 'Angle (moving average)', 'Angolo (livellato)'),
('angle_ma', 'second_y', 'Temperatur', 'Temperature', 'Temperatura'),
('angle_ma', 'timetext', 'Temperatur und Winkel der letzten', 'Temperature and angle of the last ', 'Temperatura e angolo da'),
('angle_ma', 'timetext_reset', 'Temperatur und Winkel seit dem letzten Reset: ', 'Temperature and angle since last reset: ', 'Temperatura e angolo dall\'ultimo reset: '),
('angle_ma', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Orario'),
('battery', 'diagram_battery', 'Volt', 'Voltage', 'Voltaggio'),
('battery', 'header_battery', 'Aktueller Ladezustand:', 'Battery condition:', 'Stato carica Batteria:'),
('batterytrend', 'first_y', 'Batteriespannung', 'Battery voltage', 'Voltaggio Batteria'),
('batterytrend', 'second_y', 'WiFi Signal', 'WiFi reception', 'Segnale ricezione WiFi'),
('batterytrend', 'timetext', 'Batteriespannung und WiFi Signal der letzten', 'Battery voltage and WiFi reception of the last ', 'Voltaggio Batteria e segnale WiFi delle ultime'),
('batterytrend', 'timetext_reset', 'Batteriespannung und WiFi Signal seit dem letzten Reset: ', 'Battery voltage and WiFi reception since last reset: ', 'Voltaggio Batteria e segnale WiFi dall\'ultimo reset: '),
('batterytrend', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Orario'),
('calibration', 'constant1', 'Konstante 1:', 'Constant 1:', 'Costante 1:'),
('calibration', 'constant2', 'Konstante 2:', 'Constant 2:', 'Costante 2:'),
('calibration', 'constant3', 'Konstante 3:', 'Constant 3:', 'Costante 3:'),
('calibration', 'enter_constants', 'Konstanten eingeben:', 'Please enter constants:', 'Inserire costanti:'),
('calibration', 'header', 'Aktualisieren der Kalibrierung für:', 'Update calibration for:', 'Aggiornamento della calibrazione per:'),
('calibration', 'send', 'Kalibrierung an DB senden', 'Send calibration to database', 'Inoltra calibrazione al database'),
('calibration', 'stop', 'Zurück', 'Go back', 'Indietro'),
('calibration', 'window_alert_update', 'Kalibrierung an Datenbank gesendet!', 'Calibration was send to database!', 'Calibrazione e stata inoltrata al database!'),
('diagram', 'header_no_data_1', 'Keine Daten von', 'No data from', 'Nessun dato da'),
('diagram', 'header_no_data_2', 'in diesem Zeitraum. Bitte noch weitere', 'from this timeframe. Please go', 'In questo periodo. Per favore vai '),
('diagram', 'header_no_data_3', 'Tage zurückgehen.', 'more days back.', 'giorni indietro.'),
('diagram', 'not_calibrated', 'ist nicht kalibriert.', 'is not calibrated.', 'non calibrata.'),
('diagram', 'recipe_name', 'Sudname:', 'Recipe:', 'Ricetta:'),
('diagram', 'timetext_days', 'Tag(e), ', 'day(s), ', 'giorno(i),'),
('diagram', 'timetext_hours', 'Stunde(n).', 'Hour(s).', 'ora(e).'),
('diagram', 'timetext_weeks', 'Woche(n), ', 'Week(s), ', 'settimana(e), '),
('diagram', 'tooltip_at', 'um', 'at', 'alle'),
('diagram', 'tooltip_time', 'Uhr:', ':', ':'),
('index', 'calibrate_spindle', 'Spindle im TCP Server kalibrieren', 'Calibrate spindle in TCP Server ', 'Calibrare la spindle nel server TCP'),
('index', 'change_history', 'Historie anpassen\r\n', 'Change history', 'Personalizza la cronologia'),
('index', 'chart_filename_01', 'Status (Batterie, Winkel, Temperatur)', 'Show Status (Battery, Angle, Temperature)', 'Stato (batteria, angolo, temperatura)'),
('index', 'chart_filename_02', 'Batteriezustand', 'Show Battery Condition', 'Stato batteria'),
('index', 'chart_filename_03', 'Netzwerk Empfangsqualität', 'Show WiFi quality', 'Qualità ricezione WiFi'),
('index', 'chart_filename_04', 'Extrakt und Temperatur (RasPySpindel)', 'Gravity and temperature (RasPySpindel)', 'Densità e temperatura'),
('index', 'chart_filename_05', 'Extrakt und Temperatur (RasPySpindel), Geglättet', 'Gravity and temperature (RasPySpindel), Moving Average', 'Densità e temperatura livellata'),
('index', 'chart_filename_06', 'Tilt und Temperatur', 'Tilt and temperature', 'Tilt e temperatura'),
('index', 'chart_filename_07', 'Tilt und Temperatur, Geglättet', 'Tilt and temperature, Moving Average', 'Tilt e temperatura, Curva livellata'),
('index', 'chart_filename_08', 'Extrakt und Temperatur (iSpindel Polynom)', 'Gravity and temperature (iSpindle Polynom)', 'Densità e temperatura (polinomio iSpindle)'),
('index', 'chart_filename_09', 'Gärbeginn Zeitpunkt setzen', 'Set Fermentation Start', 'Impostazione inizio fermentazione'),
('index', 'chart_filename_10', 'Vergärungsgrad', 'Apparent attenuation', 'Attenuazione apparente'),
('index', 'chart_filename_11', 'Änderung (Delta) Extrakt innerhalb 12 Stunden Anzeigen', 'Delta gravity  (12 hrs interval)', 'Delta densità (Intervallo di 12 ore)'),
('index', 'chart_filename_12', 'Verlauf Batteriespannung/WiFi anzeigen', 'Battery / Wifi trend', 'Batteria / trend WiFi '),
('index', 'chart_filename_13', 'Spindel im TCP Server Kalibrieren', 'Calibrate Spindel in TCP Server', 'Calibrazione Spindel nel server TCP'),
('index', 'days_history', 'Tage Historie\r\n', 'Days history', 'Giorni di storia'),
('index', 'diagram_selection', 'Diagramm Auswahl(Tage):', 'Diagram selection (days):', 'Selezione diagramma (giorni):'),
('index', 'header_initialgravity', 'Stammwürze [°P]', 'Initial gravity [°P]', 'Mosto originale [°P]'),
('index', 'help', 'Hilfe', 'Help', 'Aiuto'),
('index', 'no_data', 'Keine Daten in den letzten %1$d Tagen gespeichert. Bitte Spindel Verbinden, damit Daten angezeigt werden können.<br /><br />Oder ändern sie die Anzahl der Tage:<br />\r\n\r\n', 'No spindle data received in the past %1$d days. Please connect Spindle to generate data.<br /><br />Or change days of history:<br />', 'Nessun dato salvato negli ultimi %1$d giorni. Collegare il mandrino in modo che i dati possano essere visualizzati.<br /> <br /> Oppure modifica il numero di giorni: <br />'),
('index', 'or', 'oder:', 'or:', 'o:'),
('index', 'recipe_name', 'Optional Sudnamen eingeben:', 'Enter optional recipe name:', 'Imposta nome ricetta (opzionale):'),
('index', 'reset_flag', 'Daten seit zuletzt gesetztem \"Reset\" Flag zeigen', 'Show data since last set \'Reset\'', 'Visualizzare dati dall\'ultimo reset impostato'),
('index', 'send_reset', 'Gärbegin festlegen', 'Set fermentation start', 'Imposta inizio fermentazione'),
('index', 'server_not_running', 'Warnung: TCP Server läuft nicht!', 'Warning: TCP Server is not running!', 'Attenzione: server TCP non avviato!'),
('index', 'server_running', 'TCP Server läuft mit PID: ', 'TCP Server is running with PID: ', 'Server TCP avviato: '),
('index', 'server_settings', 'TCP Server Settings Editieren', 'Edit TCP Server Settings', 'Modificare impostazioni server TCP'),
('index', 'show_diagram', 'Diagram Anzeigen', 'Show Diagram', 'Visualizza diagramma'),
('plato', 'first_y', 'Extrakt % w/w (Spindel)', 'Extract % w/w (Spindle)', 'Densità % w/w (Spindle)'),
('plato', 'second_y', 'Temperatur', 'Temperature', 'Temperatura'),
('plato', 'timetext', 'Temperatur und Extraktgehalt (Spindel) der letzten', 'Temperature and extract (Spindle) of the last ', 'Temperatura e densità delle ultime'),
('plato', 'timetext_reset', 'Temperatur und Extraktgehalt (Spindel) seit dem letzten Reset: ', 'Temperature and extract (Spindle) since last reset: ', 'Temperatura e densità dall\'ultimo reset: '),
('plato', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Ora'),
('plato4', 'first_y', 'Extrakt % w/w', 'Extract % w/w', 'Densità % w/w'),
('plato4', 'second_y', 'Temperatur', 'Temperature', 'Temperatura'),
('plato4', 'timetext', 'Temperatur und Extraktgehalt der letzten', 'Temperature and extract of the last ', 'Temperatura e densità di'),
('plato4', 'timetext_reset', 'Temperatur und Extraktgehalt seit dem letzten Reset: ', 'Temperature and extract since last reset: ', 'Temperatura e densità dall\'ultimo reset: '),
('plato4', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Orario'),
('plato4_delta', 'first_y', 'Delta Extrakt % w/w', 'Delta extract % w/w', 'Delta densità % w/w'),
('plato4_delta', 'second_y', 'Temperatur', 'Temperature', 'Temperatura'),
('plato4_delta', 'timetext', 'Temperatur und Delta Extraktgehalt der letzten', 'Temperature and delta extract of the last ', 'Temperatura e delta densità delle ultime'),
('plato4_delta', 'timetext_reset', 'Temperatur und Delta Extraktgehalt seit dem letzten Reset: ', 'Temperature and delta extract since last reset: ', 'Temperatura e delta densità dall\'ultimo reset: '),
('plato4_delta', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Orario'),
('plato4_ma', 'first_y', 'Extrakt % w/w (geglättet)', 'Extract % w/w (moving average)', 'Densità % w/w (curva livellata)'),
('plato4_ma', 'second_y', 'Temperatur', 'Temperature', 'Temperatura'),
('plato4_ma', 'timetext', 'Temperatur und Extraktgehalt der letzten', 'Temperature and extract of the last ', 'Temperatura e densità di'),
('plato4_ma', 'timetext_reset', 'Temperatur und Extraktgehalt seit dem letzten Reset: ', 'Temperature and extract since last reset: ', 'Temperatura e densità dall\'ultimo reset: '),
('plato4_ma', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Orario'),
('reset_now', 'error_read_id', 'Fehler beim Lesen der Spindel ID', 'Cannot read Spindle ID from Database', 'Impossibile leggere l\' ID Spindle dal database'),
('reset_now', 'error_write', 'Fehler beim Insert', 'Cannot insert reset into Database', 'Errore scrittura database'),
('reset_now', 'recipe_written', 'Sudname in Datenbank eingetragen:', 'Recipe name added to database:', 'Nome ricetta inserita nel database:'),
('reset_now', 'reset_written', 'Reset-Timestamp in Datenbank eingetragen', 'Reset-Timestamp added to database', 'Reset-Timestamp inserito nel database'),
('sendmail', 'content_alarm_low_gravity_1', '<b>Der gemessene Extrakt folgender Spindel(n) ist unter das Limit von %s Plato gefallen:</b><br/><br/>', '<b>Measured Gravity for these Spindle(s) is below Limit of %s Plato:</b><br/><br/>', '<b>Densità rilevata dalla Spindle e inferiore al limite di %s plato:</b><br/><br/>'),
('sendmail', 'content_alarm_svg', '<b>Der Vergärungsgrad folgender Spindel(n) ist oberhalbe dem Alarm Limit von %s Prozent gefallen:</b><br/><br/>', '<b>Calculated Apparent Attenuation for these Spindle(s) is above alarm Limit of %s Percent:</b><br/><br/>', '<b>Attenuazione apparente calcolata e superiore al limite di %s per cento:</b><br/><br/>'),
('sendmail', 'content_data', '<b>%s <br/>Datum:</b> %s <br/><b>ID:</b> %s <br/><b>Winkel:</b> %s <br/><b>Stammwürze in Plato:</b> %s <br/><b>Extrakt in Plato:</b> %s <br/><b>Scheinbarer Vergärungsgrad:</b> %s <br/><b>Alkohol im Volumen :</b> %s <br/><b>Delta Plato letzte 24h:</b> %s <br/><b>Delta Plato letzte 12h:</b> %s <br/><b>Temperatur:</b> %s <br/><b>Batteriespannung:</b> %s <br/><b>Sudname:</b> %s <br/><br/>', '<b>%s <br/>Date:</b> %s <br/><b>ID:</b> %s <br/><b>Angle:</b> %s <br/><b>Apparent Attenuation in Plato:</b> %s <br/><b>Current extract in Plato:</b> %s <br/><b>Apparent attentuation:</b> %s <br/><b>Alcohol by Volumen :</b> %s <br/><b>Delta Plato last 24h:</b> %s <br/><b>Delta Plato last 12h:</b> %s <br/><b>Temperature:</b> %s <br/><b>Battery:</b> %s <br/><b>Recipe:</b> %s <br/><br/>', '<b>%s <br/>Data:</b> %s <br/><b>ID:</b> %s <br/><b>Angolo:</b> %s <br/><b>Attenuazione apparente in Plato:</b> %s <br/><b>Attuale densità in Plato:</b> %s <br/><b>Attenuazione apparente:</b> %s <br/><b>ABV :</b> %s <br/><b>Delta Plato ultime 24 ore:</b> %s <br/><b>Delta Plato ultime 12 ore:</b> %s <br/><b>Temperatura:</b> %s <br/><b>Batteria:</b> %s <br/><b>Ricetta:</b> %s <br/><br/>'),
('sendmail', 'content_info', '<b>Alarm bei Plato Unterschreitung:</b> %s Plato<br/><b>Alarm Delta Plato in den letzten 24 Stunden :</b> %s Plato<br/><b>Zeit für Statusemail:</b> %s<br/>                    <b>Aktuelle Zeit:</b> %s', '<b>Lower limit for Plato Alarm:</b> %s Plato<br/><b>Alarm Delta Plato in last 24 hours :</b> %s Plato<br/><b>Time for Statusmail:</b> %s<br/>                    <b>Current Time:</b> %s', '<b>Limite inferiore per allarme plato:</b> %s Plato<br/><b>Allarme Delta Plato nelle ultime 24 ore :</b> %s Plato<br/><b>Ora per la mail di stato:</b> %s<br/>                    <b>Ora attuale:</b> %s'),
('sendmail', 'content_status_1', '<b>Letzter Datensatz innerhalb der letzten %s Tage wurde für folgende Spindel(n) gefunden:</b><br/><br/>', '<b>Last dataset within the last %s days was found for the following Spindles:</b><br/><br/>', '<b>Ultima rilevazione nei ultimi %s giorni sono state trovate dalle seguenti spindle:</b><br/><br/>'),
('sendmail', 'subject_alarm_low_gravity', 'Alarm von iSpindel-TCP-Server (%s): Gravity unter Limit gefallen', 'Alarm from iSpindel-TCP-Server (%s): Gravity below limit', 'Allarme dal server-TCP-iSpindle (%s): Densità inferiore al limite'),
('sendmail', 'subject_alarm_svg', 'Alarm von iSpindel-TCP-Server (%s): Vergärungsgrad oberhalb Alarm Limit', 'Alarm from iSpindel-TCP-Server (%s): Apparent attenuation above alarm limit', 'Allarme dal server-TCP-iSpindle (%s): Attenuazione apparente superiore al limite'),
('sendmail', 'subject_status', 'Status Email von iSpindel-TCP-Server (%s)', 'Status Email from iSpindel-TCP-Server (%s)', 'Email di stato dal server-TCP-iSpindle (%s)'),
('settings', 'add_device', 'Individuelle Settings für Device anlegen', 'Add individual settings for device', 'Crea impostazioni individuali per il dispositivo'),
('settings', 'delete_device', 'Device aus individuellen Settings löschen', 'Remove Device from individual Settings', 'Elimina il dispositivo dalle singole impostazioni'),
('settings', 'description', 'Beschreibung', 'Description', 'Descrizione'),
('settings', 'export_data', 'Daten Tabelle exportieren', 'Export Data Table', 'Esporta tabella dati'),
('settings', 'problem', 'Probleme beim Schreiben der Settings', 'Problem with writing settings', 'Problema nella scrittura delle impostazioni'),
('settings', 'select_section', 'Section Auswahl', 'Select Section', 'Selezione sezione'),
('settings', 'send', 'Settings in DB schreiben', 'Send settings to database', 'Invia impostazioni al database'),
('settings', 'stop', 'Zurück', 'Go back', 'Indietro'),
('settings', 'testmail', 'Sende Test Email', 'Send test email', 'Invia e-mail di prova'),
('settings', 'window_alert_update', 'Settings aktualisiert!', 'Settings were updated!', 'Impostazioni aggiornate!'),
('status', 'diagram_angle', 'Grad', 'Degree', 'Gradi'),
('status', 'diagram_battery', 'Volt', 'Voltage', 'Voltaggio'),
('status', 'diagram_temperature', '°', '°', '°'),
('status', 'header_angle', 'Winkel', 'Angle', 'Angolo'),
('status', 'header_battery', 'Akku', 'Battery', 'Batteria'),
('status', 'header_temperature', 'Temperatur', 'Temperature', 'Temperatura'),
('svg_ma', 'first_y', 'Scheinbarer Vergärungsgrad (%)', 'Apparent attentuation (%)', 'Attenuazione apparente (%)'),
('svg_ma', 'second_y', 'Temperatur', 'Temperature', 'Temperatura'),
('svg_ma', 'third_y', 'Alkohol (Vol. %)', 'ABV (%)', 'ABV (%)'),
('svg_ma', 'timetext', 'Temperatur und scheinbarer Vergärungsgrad der letzten', 'Temperature and extract of the last ', 'Tenperatura e densità delle ultime'),
('svg_ma', 'timetext_reset', 'Temperatur und scheinbarer Vergärungsgrad seit dem letzten Reset: ', 'Temperature and apparent attenuation since last reset: ', 'Temperatura e attenuazione apparente dall\'ultimo reset: '),
('svg_ma', 'x_axis', 'Datum / Uhrzeit', 'Date / Time', 'Data / Orario'),
('wifi', 'header', 'Aktuelle WiFi Empfangsqualität:', 'Current Wifi reception: ', 'Qualità della ricezione attuale WiFi: ');

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
