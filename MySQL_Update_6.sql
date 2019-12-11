ALTER TABLE `Settings`
ADD COLUMN `Description_IT` varchar(128) CHARACTER SET utf8 DEFAULT NULL AFTER `Description_EN`;

UPDATE Settings
SET Description_DE='Verwendete Sprache (DE für Deutsch, EN for Englisch, IT für Italienisch)',
    Description_EN='Displayed Language (DE for German, EN for English, IT for Italian)',
    Description_IT='Lingua visualizzata (DE per tedesco, EN per inglese, IT per italiano)'
WHERE Parameter='LANGUAGE';

UPDATE Settings SET Description_IT=('Abilita colonne dinamiche (non usare se non siete dei sviluppatori)') WHERE Parameter=('ENABLE_ADDCOLS');
UPDATE Settings SET Description_IT=('INDIRIZZO IP del server Brewfather') WHERE Parameter=('BREWFATHERADDR');
UPDATE Settings SET Description_IT=('Porta del server Brewfather') WHERE Parameter=('BREWFATHERPORT');
UPDATE Settings SET Description_IT=('Polinomio iSpindle configurato per ... ([SG] = densità specifica, [PL] = plato)') WHERE Parameter=('BREWFATHERSUFFIX');
UPDATE Settings SET Description_IT=('Token per il server Brewfather') WHERE Parameter=('BREWFATHER_TOKEN');
UPDATE Settings SET Description_IT=('Inoltro a Brewfather') WHERE Parameter=('ENABLE_BREWFATHER');
UPDATE Settings SET Description_IT=('Utilizzare il token sul iSpindle per l\'inoltro dati') WHERE Parameter=('FAT_USE_ISPINDLE_TOKEN');
UPDATE Settings SET Description_IT=('INDIRIZZO_IP:PORTA del server BrewPiLess') WHERE Parameter=('BREWPILESSADDR');
UPDATE Settings SET Description_IT=('Inoltro a BrewPiLess') WHERE Parameter=('ENABLE_BREWPILESS');
UPDATE Settings SET Description_IT=('INDIRIZZO IP del server BrewSpy') WHERE Parameter=('BREWSPYADDR');
UPDATE Settings SET Description_IT=('Porta del server BrewSpy') WHERE Parameter=('BREWSPYPORT');
UPDATE Settings SET Description_IT=('Token per il server BrewSpy') WHERE Parameter=('BREWSPY_TOKEN');
UPDATE Settings SET Description_IT=('Inoltro a BrewSpy') WHERE Parameter=('ENABLE_BREWSPY');
UPDATE Settings SET Description_IT=('Utilizzare il token sul iSpindle per l\'inoltro dati') WHERE Parameter=('SPY_USE_ISPINDLE_TOKEN');
UPDATE Settings SET Description_IT=('INDIRIZZO_IP:PORTA del server Craftbeerpi3') WHERE Parameter=('CRAFTBEERPI3ADDR');
UPDATE Settings SET Description_IT=('Se 1: viene inviato il valore del angolo invece della densità.Il polinomio inserito in craftbeerpi3 può calcolare la densità') WHERE Parameter=('CRAFTBEERPI3_SEND_ANGLE');
UPDATE Settings SET Description_IT=('Inoltro a CraftbeerPI3') WHERE Parameter=('ENABLE_CRAFTBEERPI3');
UPDATE Settings SET Description_IT=('Se lasciato a 1 viene incluso un timestamp compatibile nel file CSV') WHERE Parameter=('DATETIME');
UPDATE Settings SET Description_IT=('Separatore usato nel file CSV') WHERE Parameter=('DELIMITER');
UPDATE Settings SET Description_IT=('Scrivere dati in un file CSV (1: si 0: no) ') WHERE Parameter=('ENABLE_CSV');
UPDATE Settings SET Description_IT=('Caratteri Newline') WHERE Parameter=('NEWLINE');
UPDATE Settings SET Description_IT=('Percorso file per CSV; il nome del file sarà name_id.csv') WHERE Parameter=('OUTPATH');
UPDATE Settings SET Description_IT=('Limite delta allarme plato. Se nelle ultime 12 ore la variazione e inferiore, viene inviata una mail.') WHERE Parameter=('ALARMDELTA');
UPDATE Settings SET Description_IT=('Linite inferiore densità (plato) (p.es. 4 -> allarme quando densità scende al di sotto di quel valore) ') WHERE Parameter=('ALARMLOW');
UPDATE Settings SET Description_IT=('Limite superiore allarme attenuazione apparente (p.es. 60 -> allarme quando viene raggiunto il 60 per cento)') WHERE Parameter=('ALARMSVG');
UPDATE Settings SET Description_IT=('Abilita allarme in caso la densità scenda al di sotto di un valore impostato (0:no 1:si)') WHERE Parameter=('ENABLEALARMLOW');
UPDATE Settings SET Description_IT=('Abilita allarme in caso l\'attenuazione apparente superi un valore impostato (0:no 1:si)') WHERE Parameter=('ENABLEALARMSVG');
UPDATE Settings SET Description_IT=('Abilita l\'Invio di una mail di stato giornaliera (1: si 0: no)') WHERE Parameter=('ENABLESTATUS');
UPDATE Settings SET Description_IT=('Abilita allarme se il delta plato scende al di sotto un valore impostato nelle ultime 12 ore.') WHERE Parameter=('ENABLE_ALARMDELTA');
UPDATE Settings SET Description_IT=('Indirizzo email provenienza, inviato da ') WHERE Parameter=('FROMADDR');
UPDATE Settings SET Description_IT=('Server SMTP password') WHERE Parameter=('PASSWD');
UPDATE Settings SET Description_IT=('Porta SMTP server (p. es. 587)') WHERE Parameter=('SMTPPORT');
UPDATE Settings SET Description_IT=('Indirizzo server SMTP') WHERE Parameter=('SMTPSERVER');
UPDATE Settings SET Description_IT=('Periodo in giorni dall\'ultimo invio di una mail di allarme') WHERE Parameter=('TIMEFRAMESTATUS');
UPDATE Settings SET Description_IT=('Ora per l\'invio della mail di stato giornaliera p. es. 6 sono le 6:00') WHERE Parameter=('TIMESTATUS');
UPDATE Settings SET Description_IT=('Indirizzo email a cui inviare la mail di stato/allarme') WHERE Parameter=('TOADDR');
UPDATE Settings SET Description_IT=('Inoltro dati a Fermentrack') WHERE Parameter=('ENABLE_FERMENTRACK');
UPDATE Settings SET Description_IT=('Indirizzo IP del server Fermentrack') WHERE Parameter=('FERMENTRACKADDR');
UPDATE Settings SET Description_IT=('Porta del server Fermentrack') WHERE Parameter=('FERMENTRACKPORT');
UPDATE Settings SET Description_IT=('Token per server Fermentrack') WHERE Parameter=('FERMENTRACK_TOKEN');
UPDATE Settings SET Description_IT=('Uso del token che si trova sulla iSpindle per inoltrare dati') WHERE Parameter=('FERM_USE_ISPINDLE_TOKEN');
UPDATE Settings SET Description_IT=('Inoltro a server pubblico o altra istanza di un server TCP') WHERE Parameter=('ENABLE_FORWARD');
UPDATE Settings SET Description_IT=('Indirizzo IP dell\'altro server') WHERE Parameter=('FORWARDADDR');
UPDATE Settings SET Description_IT=('Porta del server remoto') WHERE Parameter=('FORWARDPORT');
UPDATE Settings SET Description_IT=('Gamma IP concessa. lasciare a 0.0.0.0 per permettere la connessione da ovunque') WHERE Parameter=('HOST');
UPDATE Settings SET Description_IT=('Porta TCP di communicazione (da impostare anche nella configurazione iSpindle)') WHERE Parameter=('PORT');
UPDATE Settings SET Description_IT=('Se abilitato, vengono scritti i dati di configurazione sulla Spindel durante un trasferimento (in fase di testing)') WHERE Parameter=('ENABLE_REMOTECONFIG');
UPDATE Settings SET Description_IT=('1 per abilitare l\'inoltro a ubidots ') WHERE Parameter=('ENABLE_UBIDOTS');
UPDATE Settings SET Description_IT=('Token ubidots vedi istruzioni o ubidots.com') WHERE Parameter=('UBI_TOKEN');
UPDATE Settings SET Description_IT=('Utilizzo del token salvato nella iSpindle per l\'inoltro a ubidots') WHERE Parameter=('UBI_USE_ISPINDLE_TOKEN');

ALTER TABLE `Strings`
ADD COLUMN `Description_IT` varchar(1024) CHARACTER SET utf8 DEFAULT NULL AFTER `Description_EN`;

REPLACE INTO `Strings` (`File`, `Field`, `Description_DE`, `Description_EN`, `Description_IT`) VALUES
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
('index', 'or', 'oder:', 'or:', 'o:'),
('index', 'recipe_name', 'Optional Sudnamen eingeben:', 'Enter optional recipe name:', 'Imposta nome ricetta (opzionale):'),
('index', 'reset_flag', 'Daten seit zuletzt gesetztem \"Reset\" Flag zeigen', 'Show data since last set \'Reset\'', 'Visualizzare dati dall\'ultimo reset impostato'),
('index', 'send_reset', 'Gärbegin festlegen', 'Set fermentation start', 'Imposta inizio fermentazione'),
('index', 'server_not_running', 'Warnung: TCP Server läuft nicht!', 'Warning: TCP Server is not running!', 'Attenzione: server TCP non avviato!'),
('index', 'server_running', 'TCP Server läuft', 'TCP Server is running', 'Server TCP avviato'),
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
('sendmail', 'subject_alarm_low_gravity', 'Alarm von iSpindel-TCP-Server: Gravity unter Limit gefallen', 'Alarm from iSpindel-TCP-Server: Gravity below limit', 'Allarme dal server-TCP-iSpindle: Densità inferiore al limite'),
('sendmail', 'subject_alarm_svg', 'Alarm von iSpindel-TCP-Server: Vergärungsgrad oberhalb Alarm Limit', 'Alarm from iSpindel-TCP-Server: Apparent attenuation above alarm limit', 'Allarme dal server-TCP-iSpindle: Attenuazione apparente superiore al limite'),
('sendmail', 'subject_status', 'Status Email von iSpindel-TCP-Server', 'Status Email from iSpindel-TCP-Server', 'Email di stato dal server-TCP-iSpindle'),
('settings', 'description', 'Beschreibung', 'Description', 'Descrizione'),
('settings', 'problem', 'Probleme beim Schreiben der Settings', 'Problem with writing settings', 'Problema nella scrittura delle impostazioni'),
('settings', 'select_section', 'Section Auswahl', 'Select Section', 'Selezione sezione'),
('settings', 'send', 'Settings in DB schreiben', 'Send settings to database', 'Invia impostazioni al database'),
('settings', 'stop', 'Zurück', 'Go back', 'Indietro'),
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
