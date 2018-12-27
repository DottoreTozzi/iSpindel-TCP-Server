# iSpindel Generic TCP Server
#### (iSpindle.py Version 1.6.0)

[English Version](README_en.md)

**Neu (15.10.2018)**
Neue Diagramme: Moving Average (Glättung für angle.php und plato4.php, konfigurierbar)
"Landing Page" index.php - Die lang ersehnte Auswahlseite für iSpindeln und Diagramme.
Erreichbar über "http://myraspi/index.php" (je nach Konfiguration).
Am besten gleich einen Shortcut (Lesezeichen) erstellen.

**Neu (02.10.2018)**
CBP3 support (Dank an jlanger)     
Brewpiless support (Dank an ollinator2000)     
iSpindel Remote Configuration: Die iSpindel kann nun bestimmte Konfigurations Parameter empfangen.     
Momentan funktioniert das nur mit der "Intervall" Einstellung.     
Das Polynom und das Token können Server seitig bereits konfiguriert werden, diese werden aber von der momentanen Firmware Version (6.0.1) noch ignoriert.     
Benötigt wird auf jeden Fall Firmware 6.0.1 oder neuer.

**Neu (15.02.2018)**
Neues Diagramm wifi.php zeigt die zuletzt gemessene und übertragene Verbindungsqualität zum WLAN.        
angle.php, plato.php und plato4.php wurden erweitert um neue Parameter "days" und "weeks".        
Die Untertitel dieser Diagramme wurden angepasst, um dies zu reflektieren und deren Lesbarkeit zu erhöhen.      
3600 Stunden werden jetzt angezeigt als 21 Wochen, 3 Tage.     
Die Parameter können beliebig kombiniert werden.

**Neu (12.02.2018)**
iSpindle.py Version 1.4.0      
Angepasst an [Sam's iSpindel](https://github.com/universam1/iSpindel) Firmware 5.8 und aufwärts.      
Die nunmehr mitgesendeten Daten (Intervall, WLAN Empfangsqualität) werden jetzt mit abgefragt und in der Datenbank hinterlegt.
Alles sollte nach wie vor rückwärtskompatibel sein.
Im Debug Modus wird ein Hinweis ausgegeben, falls die iSpindel Firmware "veraltet" ist.
Bitte bei bestehenden Installationen die Datenbank anpassen und um die nötigen Felder erweitern, siehe MySQL_Update-3.sql Skript.

**Neu (20.01.2018)**     
iSpindle.py Version 1.3.3     
Neuer Parameter UBI_USE_ISPINDLE_TOKEN     
Die neue Version erlaubt das in der iSpindel Konfiguration vorgesehene Feld "Token" als Ubidots Token zu verwenden.     
Das im Skript global gesetzte Ubidots Token wird durch das in der iSpindel hinterlegte überschrieben, falls diese Option ausgewählt ist.     
Damit wird es möglich, nur die Daten einzelner iSpindeln weiterzuleiten oder einzelnen iSpindeln verschiedene Ubidots Token zuzuweisen.     
Um die Weiterleitung für einzelne iSpindeln zu unterdrücken, wird das Feld "Token" in der iSpindel Konfiguration leer gelassen, oder (falls das Feld für Kommentare etc. genutzt wird) mit einem Asterisk ("*") eingeleitet.     

**Neu (28.11.2017)**     
Interim Release.      
Diagramme benutzen jetzt die mysqli Library.      
Damit ist die Kompatibilität zu PHP7 (Debian/Raspbian Stretch) wieder hergestellt.      
Die Dokumentation wurde entsprechend angepasst.      
Die Änderungen sollten rückwärtskompatibel sein.      
Das neue Repo wurde in den Docs ebenfalls berücksichtigt.      

**Neu (27.09.2017)**  
update.sh: Skript für automatisches Update auf neue Versionen
update-raspi.sh: Skript für komplettes Update (Image/Debian/Raspbian)

**Neu (31.08.2017)**  
Kompatibel zu Firmware >= 5.4.x  
Optionale Weiterleitung zum öffentlichen Server bzw. weiteren lokalen (Raspi-) Server Instanzen implementiert  
Inkohärenz bei Legacy Plato Diagrammen (Version 4, serverseitiges Polynom) beseitigt  
Kleinere Fehler in den Diagrammen beseitigt (Legenden)  
Unix Dateizugriffsrechte angepasst  
Kleinere Bugfixes  

Update von bestehenden Versionen:
Bitte die [Installations Anleitung](INSTALL.md) beachten.

Ein neues Raspbian "Plug & Play" Image wird natürlich ebenfalls in den nächsten Tagen bereitgestellt.  
Hierzu bitte diesen Thread im Hobbybrauer Forum beobachten:
http://hobbybrauer.de/forum/viewtopic.php?f=58&t=12869

**Ältere News:**
**Neu: Diagramme, siehe:**
[Diagramme Readme](web/README.md)

**Installations Anleitung für Raspbian:**
[Installations Anleitung](INSTALL.md)

Dieses in Python geschriebene Server Skript dient dazu, von der iSpindel kommende Rohdaten über eine generische TCP Verbindung zu empfangen.
Auf zusätzlichen, unnötigen Overhead durch Protokolle wie http wird hierbei bewusst verzichtet, um den Stromverbrauch der iSpindel so gut es geht zu minimieren.
Die empfangenen Daten können als CSV (“Comma Separated Values”, also durch Kommas getrennte Werte) in Textdateien gespeichert (und so zum Beispiel in Excel leicht importiert) werden.
Ebenso ist es möglich, die Daten in einer MySQL Datenbank abzulegen.    
Somit hat man einen lokalen Server, der auch ohne Internet Anbindung funktioniert und den Einsatz der iSpindel im Heimnetzwerk ermöglicht.
Die Zugriffszeiten sind kürzer und dadurch sinkt natürlich auch der (ohnehin geringe) Stromverbrauch der iSpindel noch weiter.

Um nicht auf die Anbindung an Ubidots verzichten zu müssen, besteht aber auch die Option, die Daten zusätzlich dorthin weiterzuleiten.    
Das geschieht transparent, ohne dass die iSpindel auf die Verbindung warten muss. Der lokale Server fungiert dann sozusagen als Proxy.

Das Skript ist plattformunabhängig und kann z.B. auf einem Raspberry Pi, eingesetzt werden.
Aber auch der Einsatz auf einem evtl. gemieteten dedizierten (oder virtuellen) Server oder einem beliebigen Rechner im Heimnetz ist möglich.
Der Betrieb mehrerer iSpindeln gleichzeitig funktioniert problemlos und ohne Verzögerungen, da Multithreading implementiert ist.    
Getestet wurde es unter Mac OS X (Sierra) und Linux (Debian), es sollte aber auch unter Windows problemlos laufen.    
Die einzige Voraussetzung ist, dass Python installiert ist.

Für die Anbindung an MySQL muss auch der `python-mysql.connector` installiert sein.
In der Konfiguration der iSpindel wählt man **TCP** aus, und trägt die IP Adresse des Servers ein, auf dem das Skript läuft.
Als Port wählt man am besten die Voreinstellung **9501**.

Nun muss das Skript selbst unbedingt noch konfiguriert werden.
Dazu öffnet man es mit einem Text Editor und bearbeitet die gleich beschriebenen Einstellungen.

Dann wird es in einen beliebigen Pfad auf dem Zielsystem kopiert, `/usr/local/bin` bietet sich an, oder einfach `/home/pi`.
Mit `chmod 755 iSpindle.py` macht man es ausführbar und startet es mit `./iSpindle.py`.
Alternativ (z.B. unter Windows) startet man es mit `python iSpindle.py`.
Wenn alles funktioniert, beendet man das Skript, setzt `DEBUG = 0` und startet es im Hintergrund neu mit `./iSpindle.py &`.

### Konfiguration:

#### Allgemeines:

	DEBUG = 0      
	PORT = 9501    
	HOST = '0.0.0.0'

Wenn **DEBUG** auf 1 gesetzt wird, werden auf der Konsole detaillierte Informationen ausgegeben.    
Während der ersten Inbetriebnahme ist dies sehr empfehlenswert.    
Falls der TCP **Port** 9501 bereits belegt ist (was unwahrscheinlich ist), kann man diesen auf einen anderen Wert einstellen.    
**HOST** legt fest, von welchen IP Adressen aus der Server erreichbar sein soll.    
Am besten lässt man das auf der Voreinstellung, also keine Einschränkungen.    
Von außerhalb des eigenen Netzwerks ist Port 9501 normalerweise sowieso nicht zu erreichen, es sei denn man konfiguriert den Router entsprechend (Port Forwarding).

#### CSV:

	CSV = 0
	OUTPATH = '/home/pi/iSpindel'
	DELIMITER = ';'
	NEWLINE = '\r\n'
	DATETIME = 1    

Um die Ausgabe von CSV Dateien einzuschalten, muss **CSV** = 1 gesetzt werden.   
**OUTPATH** ist der Pfad, unter dem die CSV Datei gespeichert wird. Pro Spindel wird hierbei jeweils eine eigene Datei angelegt.
Wenn OUTPATH im Netzwerk freigegeben ist kann man sehr leicht eine solche Datei z.B. in Excel importieren.   
**DELIMITER** legt das Zeichen fest, durch welches die einzelnen Datenfelder separiert sind. Üblicherweise wählt man hier Komma oder Semikolon.   
**NEWLINE** definiert das Zeilenende. Ist die CSV Datei für Windows oder Office für Mac bestimmt, gilt die Voreinstellung.   
Für UNIX Systeme (Linux, Mac OS X) wählt man besser '\n'.   
**DATETIME** legt fest, ob das aktuelle Datum und Uhrzeit mit in die CSV Datei geschrieben werden sollen. Normalerweise dürfte das der Fall sein.

#### MySQL

	SQL = 1
	SQL_HOST = '127.0.0.1'
	SQL_DB = 'iSpindle'
	SQL_TABLE = 'Data'
	SQL_USER = 'iSpindle'
	SQL_PASSWORD = 'xxxxxxxx'

Will man auf MySQL verzichten, setzt man **SQL** = 0.    
**SQL\_HOST** definiert die IP Adresse der Datenbank. Normalerweise ist das derselbe Rechner auf dem auch das Skript läuft; dies ist die Voreinstellung (“localhost” oder 127.0.0.1).    
Die restlichen Felder definieren den Zugang zur Datenbanktabelle.    
Die Default Einstellung geht davon aus, dass die Datenbank “iSpindle”; heißt, ebenso die ID des Datenbank Users.    
Die Tabelle, in welcher die Daten landen, heißt “Data”.    
Um die Tabelle mit den grundlegenden Feldern anzulegen, verwendet man am besten dieses SQL Statement:

	CREATE TABLE 'Data' (
		'Timestamp' datetime NOT NULL,
		'Name' varchar(64) COLLATE ascii_bin NOT NULL,
		'ID' varchar(64) COLLATE ascii_bin NOT NULL,
		'Angle' double NOT NULL,
		'Temperature' double NOT NULL,
		'Battery' double NOT NULL,
		'ResetFlag' boolean,
		'Gravity' double NOT NULL,
		PRIMARY KEY ('Timestamp', 'Name', 'ID')
	) 
	ENGINE=InnoDB DEFAULT CHARSET=ascii 
	COLLATE=ascii_bin COMMENT='iSpindle Data';

Als Datenbankbenutzer kann man natürlich das vordefinierte Admin Konto verwenden, oder aber (schöner) man legt einen Benutzer an, mit Zugriff auf diese Datenbank:

	CREATE USER 'iSpindle'@'localhost' IDENTIFIED BY 'password';
	GRANT ALL PRIVILEGES ON iSpindle . * TO 'iSpindle'@'localhost';
	FLUSH PRIVILEGES;

Weitere Felder können vom Skript dynamisch angelegt werden; dies ist aber nur zu empfehlen, wenn man eine eigene Firmware testen will, die zusätzliche Variablen ausgibt.    
Hierzu wird **ENABLE\_ADDCOLS** = 1 gesetzt. Für den Normalbetrieb ist egal, ob hier 0 oder 1 eingestellt ist.    
Falls der Server nach außen offen ist (z.B. extern gehostet), empfehle ich aber (in dieser Version des Skripts) ENABLE\_ADDCOLS auf 0 zu setzen um eventuelle SQL Injections zu verhindern(!).


#### Ubidots Anbindung

	UBIDOTS = 1
	UBI_USE_ISPINDLE_TOKEN = 1
	UBI_TOKEN = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'

**UBIDOTS** = 0 schaltet die Ubidots Weiterleitung aus.    
In **UBI\_TOKEN** das eigene Token eintragen (siehe Dokumentation der iSpindel).
Dieses gilt dann global (also für alle angeschlossenen iSpindeln).
Die neuere Methode (empfohlen) ist, das Token stattdessen direkt in der iSpindel Konfiguration einzutragen und den Parameter UBI_USE_ISPINDLE_TOKEN auf 1 (Standardeinstellung) zu lassen.

Die Daten sollten nun sowohl wie gewohnt in Ubidots erscheinen als auch auf Eurem lokalen Server.
Auch neue iSpindeln (Devices) lassen sich so problemlos anlegen, für Ubidots macht es keinen Unterschied, ob die Daten von der iSpindel direkt kommen oder vom lokalen Server.

Viel Spaß,     
Euer Tozzi (stephan@sschreiber.de)
