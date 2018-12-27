# Installationsanleitung für Raspberry Pi (Raspbian)
### Schritt-für-Schritt

[English Version](INSTALL_en.md)

### Interim Release: Debian/Raspbian Stretch


### Update auf Version 1.6.0, Firmware 6.0.1 und höher:
     
Bei Einsatz einer älteren Version muss die Datenbank angepasst werden, wenn die "Remote Configuration" der iSpindel genutzt werden soll.

	CREATE TABLE `Config` (
		`ID` int NOT NULL,
		`Interval` int NOT NULL,
		`Token` varchar(64) NOT NULL,
		`Polynomial` varchar(64) NOT NULL,
	        `Sent` boolean NOT NULL,
		PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin COMMENT='iSpindle Config Data';

### Update auf Version 1.4.0, Firmare 5.8 und höher...

Diese Schritte sind nur nötig, wenn eine bestehende Version aktualisiert werden soll.
Auch diese Anpassungen sind im Folgenden bereits berücksichtigt.
Falls momentan noch die ältere Version im Einsatz ist, müssen der Datenbank 2 neue Felder hinzugefügt werden:

        mysql -u iSpindle -p
        (Password if unchanged: ohyeah)
        USE iSpindle;
        ALTER TABLE Data ADD `Interval` int;
        ALTER TABLE Data ADD RSSI int;
        quit;


### Update auf Version 1.3.0, kompatibel mit Firmware 5.4.x und höher:

(Bei Neuinstallation bitte ignorieren; dieser Schritt ist nicht notwendig und führt nur zur Verwirrung, falls irgendwas nicht geht.)

GIT PULL im iSpindel Verzeichnis machen, das Skript (iSpindle.py) konfigurieren und nach /usr/local/bin kopieren.

Die Datenbank aktualisieren:

	 mysql -u iSpindle -p
	 (Passwort falls nicht geändert: ohyeah)
	 USE iSpindle;
	 ALTER TABLE Data MODIFY ID INT UNSIGNED NOT NULL;
	 ALTER TABLE Calibration MODIFY ID INT UNSIGNED NOT NULL;
	 ALTER TABLE Data ADD UserToken VARCHAR(64);
	 quit;

Natürlich wird es ein neues Image geben, aber dann sind halt die alten Daten weg.
Wie oben beschrieben kommt Ihr auch so auf den neuesten Stand.
Auch ohne diese Änderungen wird erst mal alles weiterhin funktionieren, aber ich empfehle, Ihr bringt das jetzt gleich hinter euch.
Das erleichtert die Fehlersuche, falls künftig irgendwas schiefgeht.

### Update auf Firmware Version 5.x:
Um die neue Firmware einsetzen zu können, bitte das Skript auf die neueste Version aktualisieren.     
Falls die Datenbank schon besteht, muss in die Tabelle "Data" ein neues Feld eingefügt werden:

	USE iSpindle;
	ALTER TABLE Data ADD Gravity double NOT NULL DEFAULT 0;

Bei einer Neuinstallation ist dies bereits im Folgenden berücksichtigt.

### Update für Resetflag bei Grafiken:
Um bei den Grafiken das Resetflag nutzen zu können, muss, falls die Datenbank schon besteht, in die Tabelle "Data" ein neues Feld eingefügt werden:

	USE iSpindle;
	ALTER TABLE Data ADD ResetFlag boolean;

Bei einer Neuinstallation ist dies bereits im Folgenden berücksichtigt.

### Vorbemerkung:
Es mag so aussehen, als würde hier viel zu viel Ballast installiert, das geht alles auch schlanker.
Stimmt.
Aber die meisten werden ja Raspbian verwenden, und das ist schon so aufgebläht dass ich da kein schlechtes Gewissen habe, noch Apache, Samba und MySQL mit draufzupacken.
Der Raspi 3 schafft das sowieso problemlos, aber auch die kleineren Modelle sollten klarkommen.
Die ganze Installation braucht gute 5GB, also eine SD mit 8 GB sollte reichen, ab 16GB reicht der Speicherplatz auf jeden Fall.
Wer auf die Diagramme und auf phpmyadmin verzichten will, also auf die Möglichkeit, die Datenbank mit einer html Oberfläche zu administrieren, kann den Apache2 und phpmyadmin auch weglassen.

### Raspbian vorbereiten
- raspi-config: ssh einschalten, Netzwerkverbindung herstellen (hierfür braucht man einmalig eine Tastatur und einen HDMI Bildschirm)             
- Oder: Beim Vorbereiten der SD Karte im /boot Verzeichnis eine leere Datei "ssh" anlegen. Dann kann man sich direkt über einen anderen Rechner im Netzwerk per SSH einloggen und braucht weder Bildschirm noch Tastatur am Raspi.

Hat man keinen Ethernet Anschluss verfügbar und möchte den Raspberry beim ersten Start gleich mit dem Heim WLAN verbinden, so ist auch dieses möglich:
Hierzu eine Datei namens wpa_supplicant.conf im /boot Verzeichnis anlegen mit diesem Inhalt:

	country=DE
	ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
	update_config=1

	network={
    		ssid="YOUR_SSID"
    		psk="YOUR_PSK"
	}

- Verbinden mit putty (Windows) oder Terminal (Mac OS X, Linux) und ssh:

		ssh pi@[ip-adresse oder hostname] 
		Passwort: raspberry (ändern)

Der Hostname ist per default "raspberrypi" und wenn der nicht gefunden wird, einfach mal "raspberrypi.local" ausprobieren.
Die IP Adresse kann über die Konfiguration des Routers herausgefunden werden, falls das nicht klappt.

Nun als erstes bitte raspi-config aufrufen, das Benutzerpasswort und eventuell den Host Namen ändern, und unbedingt die Sprache unter "Localisation Options" richtig einstellen. Dabei zur Sicherheit auch immer die englischen Sprachversionen mit generieren lassen, also alles unter "en_US" oder auch en_GB:

	sudo raspi-config

Anschließend sicherstellen, dass die Lokale wirklich richtig eingestellt ist, sonst kann es zu Problemen kommen.

	sudo update-locale LANG="de_DE.utf8" LANGUAGE="de:en" LC_ALL="de_DE.utf8"
	locale

Es sollten die richtigen Einstellungen angezeigt werden, keine der Variablen sollte leer (undefiniert) sein.
Anstelle von DE kann natürlich AT, LU, CH stehen, je nach Land.
Oder man wählt Englisch, in dem Fall dann normalerweise en_US.utf8.
Meistens gibt es Fehlermeldungen. Klappt seltsamerweise fast nie auf Anhieb.
Einfach nochmal ausloggen ("exit") und neu einloggen.
Eventuell muss man (je nach Debian Version) auch die richtige Locale in /etc/default/locale eintragen.

Spätestens dann sollte das so aussehen:

	LANG=de_DE.utf8
	LANGUAGE=de:en
	LC_CTYPE="de_DE.utf8"
	LC_NUMERIC="de_DE.utf8"
	LC_TIME="de_DE.utf8"
	LC_COLLATE="de_DE.utf8"
	LC_MONETARY="de_DE.utf8"
	LC_MESSAGES="de_DE.utf8"
	LC_PAPER="de_DE.utf8"
	LC_NAME="de_DE.utf8"
	LC_ADDRESS="de_DE.utf8"
	LC_TELEPHONE="de_DE.utf8"
	LC_MEASUREMENT="de_DE.utf8"
	LC_IDENTIFICATION="de_DE.utf8"
	LC_ALL=de_DE.utf8

Dann das System auf den neuesten Stand bringen:

	sudo apt-get update
	sudo apt-get dist-upgrade

(Oder auch nicht. Es sollte alles auch so funktionieren und falls das Update aus irgend einem Grund nicht hinhaut, wird die Fehlersuche schwierig. Also am besten erst machen, wenn sonst alles funktioniert und Ihr ein Backup gemacht habt).
Spätestens jetzt zeigt sich, ob die SD Karte und das Netzteil in Ordnung sind.
Ich habe an dieser Stelle 2 Tage verschwendet aufgrund letztlich fehlerhafter SD Karten.
Warnmeldungen bezüglich X11 können hier ignoriert werden.

Danach aber auf jeden Fall neu starten:
	
	sudo reboot

### Server Software herunterladen

	git clone https://github.com/DottoreTozzi/iSpindel-TCP-Server iSpindel-Srv

### MySQL Datenbank, Apache2 Webserver und phpMyAdmin Datenbank GUI 

#### Installieren:

	sudo apt-get install apache2 mysql-server mysql-client python-mysql.connector

Passwort für Datenbank root Benutzer eingeben, falls angefordert.

	sudo apt-get install phpmyadmin

Apache2 als Webserver auswählen, Datenbank root Passwort wieder eingeben.
Falls im vorigen Schritt keines angegeben wurde, freilassen und das Passwort danach in der Datei /etc/dbconfig-common/phpmyadmin.conf nachschauen.
MySQL kann nun über http://[meinraspi]/phpmyadmin erreicht werden.

Die folgenden Schritte sollten aber über die Kommandozeile gemacht werden.
Der phpmyadmin User verfügt noch nicht über die nötigen Rechte.

	sudo mysql -u root

Danach landet Ihr auf einem **mysql>** Prompt und seit als Datenbank Admin angemeldet. Dies ist neu seit Debian Stretch.
Da Ihr Euch bereits via "sudo" als Superuser identifiziert habt, werdet Ihr hier nicht (mehr) nach einem Passwort gefragt.

#### Datenbank erstellen und auswählen:
	CREATE DATABASE iSpindle;
	USE iSpindle;

#### Tabelle(n) anlegen:

Am besten gleich beide Tabellen (Daten und Kalibrierung) anlegen.       
[Hier](./MySQL_CreateTables.sql) ist ein [SQL Skript](./MySQL_CreateTables.sql) für beide.        
Die Datentabelle folgt diesem Schema:      

	CREATE TABLE `Data` (
 		`Timestamp` datetime NOT NULL,
 		`Name` varchar(64) COLLATE ascii_bin NOT NULL,
 		`ID` int NOT NULL,
 		`Angle` double NOT NULL,
 		`Temperature` double NOT NULL,
 		`Battery` double NOT NULL,
		`ResetFlag` boolean,
		`Gravity` double NOT NULL DEFAULT 0,
		`UserToken` varchar(64) COLLATE ascii_bin,
		`Interval` int,
		`RSSI` int,
 	PRIMARY KEY (`Timestamp`,`Name`,`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin COMMENT='iSpindle Data';

(Im Feld ID wird die Hardware ID abgelegt, welche wir zum Hinterlegen der Kalibrierung benötigen.)     

	CREATE TABLE `Calibration` (
		`ID` int NOT NULL,
		`const1` double NOT NULL,
		`const2` double NOT NULL,
		`const3` double NOT NULL,
		PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin COMMENT='iSpindle Calibration Data';
		
		
	CREATE TABLE `Config` (
		`ID` int NOT NULL,
		`Interval` int NOT NULL,
		`Token` varchar(64) NOT NULL,
		`Polynomial` varchar(64) NOT NULL,
	        `Sent` boolean NOT NULL,
		PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin COMMENT='iSpindle Config Data';


#### Benutzer anlegen und berechtigen (und ihm ein eigenes Passwort geben):

	CREATE USER 'iSpindle' IDENTIFIED BY 'ohyeah';
	GRANT USAGE ON *.* TO 'iSpindle';
	GRANT ALL PRIVILEGES ON `iSpindle`.* TO 'iSpindle' WITH GRANT OPTION;

Ab sofort steht die MySQL Datenbank für die iSpindel zur Verfügung.
Die MySQL Command Shell verlassen:

	QUIT;

Das Server Skript falls nötig wie im [README](./README.md) beschrieben anpassen.
Der Datenbankzugriff ist vorkonfiguriert. Falls man eine CSV Datei haben will oder das eigene Ubidots Token eintragen möchte, um die Weiterleitung zu aktivieren, muss das Skript wie dort beschrieben editiert werden.
Ansonsten kann man alles so lassen wie es ist und direkt hier weitermachen.

### Optional: Samba installieren (empfohlen):

	sudo apt-get install samba samba-common-bin

#### Home Verzeichnis im Netzwerk freigeben:

/etc/samba/smb.conf:

	[global]
 	server string = RASPBIAN
 	guest ok = yes
 	security = user
 	socket options = TCP_NODELAY SO_RCVBUF=65535 SO_SNDBUF=65535
 	registry shares = yes
 	syslog = 0
 	map to guest = bad user
 	workgroup = WORKGROUP
 	bind interfaces only = No
 	encrypt passwords = true
 	log level = 0
	# smb ports = 445
 	unix extensions = No
 	wide links = yes

 	include = /etc/samba/user.conf
 	include = /etc/samba/shares.conf


/etc/samba/shares.conf:

        [boot]
        path = /boot
        guest ok = yes
        read only = no
        force user = pi
        browseable = yes

        [iSpindle-Srv]
        path = /usr/local/bin
        guest ok = yes
        read only = no
        force user = pi
        browseable = yes

        [pi-home]
        path = /home/pi
        guest ok = yes
        read only = no
        force user = pi
        browseable = yes

        [system-logs]
        path = /var/log
        guest ok = yes
        read only = yes
        force user = root
        browseable = yes

#### Samba daemon (smbd) starten

	sudo apt-get install insserv
	sudo insserv smbd
	sudo service smbd start

Das pi Home Verzeichnis ist nun im Heimnetzwerk freigegeben und Ihr könnt es im Explorer/Finder Eures Computers sehen.
Ebenso das boot Verzeichnis und /usr/local/bin, wo das Python Script (der iSpindel Server) residieren wird.
Wenn das alles mal passt, kann man diese Einträge irgendwann sicherheitshalber entfernen.
Ebenso einfach kann man natürlich andere Ordner freigeben, z.B. für Musik.

### Das genericTCP Skript installieren
Zunächst das Skript konfigurieren, wie im README beschrieben.    
Die Dateien iSpindle.py und ispindle-srv in das mit Samba freigegebene Verzeichnis kopieren. 
Das Paket insserv muss installiert sein, auch wenn auf Samba verzichtet wurde!

	sudo apt-get install insserv

Dann wieder auf dem Raspi im ssh Terminal eingeben:
	
	cd /home/pi/iSpindel-Srv
	sudo mv iSpindle.py /usr/local/bin
	sudo mv ispindle-srv /etc/init.d
	sudo chmod 755 /usr/local/bin/iSpindle.py
	sudo chmod 755 /etc/init.d/ispindle-srv
	cd /etc/init.d
	sudo systemctl daemon-reload
	sudo insserv ispindle-srv
	sudo service ispindle-srv start

Ein guter Zeitpunkt, den Raspi neu zu starten (ist aber nicht nötig):

	sudo reboot

Nach erneuter Verbindung sollte nun "ps -ax | grep iSpindle" einen laufenden iSpindle.py Prozess anzeigen, so in der Art:     

	23826 ?        S      0:00 python2.7 /usr/local/bin/iSpindle.py
	23845 pts/0    R+     0:00 grep iSpindle

Ihr habt jetzt die längstmögliche Batterielaufzeit für die iSpindel und habt Eure Daten auch lokal vorhanden, falls Ubidots mal aussetzen sollte oder Ihr Eure eigenen Visualisierungen machen wollt.

Ein paar (für mich ausreichende) Diagramme habe ich [hier](/web) bereitgestellt.

Euer Tozzi (stephan@sschreiber.de)



