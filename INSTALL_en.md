# Installation Guide for Raspbian 
### Step-by-Step


### Update to 1.6.0, Firmware 6.0.1 und above:
     
Ignore if directly installing the latest version.
If you're upgrading, you'll need to add this table to your database, however:

	CREATE TABLE `Config` (
		`ID` int NOT NULL,
		`Interval` int NOT NULL,
		`Token` varchar(64) NOT NULL,
		`Polynomial` varchar(64) NOT NULL,
	        `Sent` boolean NOT NULL,
		PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin COMMENT='iSpindle Config Data';


### Update to 1.4.0, Firmware 5.8 and later:

Again, ignore this if newly installing.
If you are using the previous version, however, you will need to add yet 2 more columns to your database:

        mysql -u iSpindle -p                                    
        (Password if unchanged: ohyeah)                         
        USE iSpindle;                                                                                       
        ALTER TABLE Data ADD `Interval` int;                                                    
        ALTER TABLE Data ADD RSSI int;                                             
        quit;                                                                                                
 

### Update to latest Version (1.3.1), compatible with Firmware 5.4.x and later:

(Ignore this when newly installing)

GIT PULL within the iSpindel Directory, then configure (edit) iSpindle.py and copy it to /usr/local/bin.

Update the Database:

        mysql -u iSpindle -p
        (Password if unchanged: ohyeah)
        USE iSpindle;
        ALTER TABLE Data MODIFY ID INT UNSIGNED NOT NULL;
        ALTER TABLE Calibration MODIFY ID INT UNSIGNED NOT NULL;
        ALTER TABLE Data ADD UserToken VARCHAR(64);
        quit;

Of course there will be an updated ready-to-flash image for the Raspi as well, but this would kill your existing data.
The above commands will bring you to the same level without losing anything.
Even without these, everything will probably continue to work as usual, but I still highly recommend to follow these steps, as it will make future debugging much easier and ensure you are up-to-date with the current development branch.

### Update to Firmware 5.x:
If you already have this running and want to update to the new firmware, you'll need to add a new column to the Data table:

	USE iSpindle;
	ALTER TABLE Data ADD Gravity double NOT NULL DEFAULT 0;

This is already taken into account if you newly install this and follow the instructions below.

### Update for Resetflag for charts:
To use the charts with the parameter Resetflag, you'll need to add a new column to the Data table:

	USE iSpindle;
	ALTER TABLE Data ADD ResetFlag boolean;

This is already taken into account if you newly install this and follow the instructions below.

### Preliminary Remarks:

These recommended software requirements might seem like overkill, but this solution is highly flexible and wide open for future enhancements.    
Most Raspberry owners will use Raspbian, but you should be able to get this to work just fine under any other Linux based distribution.    
Of course you can use slimmer solutions for the database and the web server.    
I am using MySQL and Apache because they're quite standard and there's a lot of potential for future development.    
The whole install will use roughly 5 GB on the sd card, so a 8 GB one should suffice, 16 GB being just perfect.
If you don't need phpmyadmin and the visualization charts you can safely omit both mySql and Apache, of course.

### Prepare Raspbian 
- raspi-config: activate ssh, establish network connection (via Ethernet or WiFi). You'll probably need a keyboard and an HDMI capable display for this step. Or you create an empty file called "ssh" in the boot directory of the SD card prior to booting the Raspi from it.

If you don't have an ethernet connection available and want the Raspberry Pi to immediately connect to your WLAN on first boot, you can create a file named "wpa_supplicant.conf" in the /boot partition with the following contents:

	country=DE
	ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
	update_config=1
	
	network={
    		ssid="YOUR_SSID"
    		psk="YOUR_PSK"
	}

- raspi-config: activate ssh, establish network connection (via Ethernet or WiFi). You'll probably need a keyboard and an HDMI capable display for this step. Or you create an empty file called "ssh" in the boot directory of the SD card prior to booting the Raspi from it.
- connect via putty (Windows) or Terminal and SSH (Mac OS X, Linux):
```           
   ssh pi@[ip-address or hostname] 
   Password: raspberry (change this)
```
The host name should be "raspberrypi" by default; if that doesn't work, try "raspberrypi.local".
if neither of these work, you'll need to find out the raspi's IP address via the router configuration menu.

First thing you should do now is do some basic configurations via raspi-config:

	sudo raspi-config

Make sure you set the system locale properly, and change your password and perhaps the host name, too.
Improperly set system locale might lead to some nasty problems later.
Check by entering the "locale" command and make sure all the variables are defined and correct.


Then, update your system:

	sudo apt-get update
	sudo apt-get dist-upgrade

You'll need to reboot the Raspi after this.
If this fails, there is a good chance that your SD Card or Power Supply are faulty.
I once wasted 2 days because both SD cards I tried were bad.
Warnings regarding X11 Authentication can be safely ignored.

### Download the Server Software

	git clone https://github.com/DottoreTozzi/iSpindel-TCP-Server iSpindel-Srv

### MySQL, Apache2 and phpMyAdmin database GUI 

#### Install:

	sudo apt-get install apache2 mysql-server mysql-client python-mysql.connector

When asked, choose a password for the database admin account.

	sudo apt-get install phpmyadmin

When asked, choose Apache2 as web server, and enter the database root user's password (as above).
Or keep it blank and look it up in /etc/dbconfig-common/phpmyadmin.conf later.
MySQL is now managable through http://[myraspi]/phpmyadmin.

The following steps should also be done on the command line, as the user "phpmyadmin" doesn't have the needed access rights (yet).

	sudo mysql -u root

You should not be asked for a password there, as you are already logging in as the super user (root).
Now you should see a **mysql>** prompt.

#### Create and Select the Database:
	CREATE DATABASE iSpindle;
	USE iSpindle;

#### Create Tables:

If you wish to use the charts with density calibration, you should create both tables now.    
[Here](./MySQL_CreateTables.sql) you can find scripts with both definitions.
Otherwise, the main data table will suffice:

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

The field "ID" stores the iSpindle's unique hardware ID, which we'll need in order to use calibration and remote config.

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


#### Create a Database User, Grant Permissions, Set Password):

	CREATE USER 'iSpindle' IDENTIFIED BY 'ohyeah';
	GRANT USAGE ON *.* TO 'iSpindle';
	GRANT ALL PRIVILEGES ON `iSpindle`.* TO 'iSpindle' WITH GRANT OPTION;

Now the database is accessible by the Python server script.
Configure it as explained here: [README](./README_en.md).
See section below on how to install it.

Leave the MySQL Command Shell:

	QUIT;

### Optional: Install Samba (Recommended):

	sudo apt-get install samba samba-common-bin

#### Share pi User's Home Directory and Log Files:

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

#### Start Samba Daemon:

	sudo apt-get install insserv
	sudo insserv smbd
	sudo service smbd start

The pi user's home and system log directories are now being shared and you should be able to see them in Explorer/Finder.

### Install the Python Server Script for genericTCP:
Configure the script as explained here: [README](./README_en.md), if you need to.
You'll want to in order to enable Ubidots forwarding or other features such as CSV file output.
Otherwise just leave it as is.

Then:
Copy both iSpindle.py and ispindle-serv to the pi home directory.
The insserv package and its dependencies have to yet be installed, should you have chosen to skip Samba:

	sudo apt-get install insserv
	
Then, within the SSH terminal session, type:

	cd /home/pi/iSpindel-Srv
	sudo mv ./iSpindle.py /usr/local/bin
	sudo mv ./ispindle-srv /etc/init.d
	sudo chmod 755 /usr/local/bin/iSpindle.py
	sudo chmod 755 /etc/init.d/ispindle-srv
	cd /etc/init.d
	sudo systemctl daemon-reload
	sudo insserv ispindle-srv
	sudo service ispindle-srv start

Now would be a good time to reboot the Raspi (should not be required, though).    
You should be able to see the script running now:

    ps -ax | grep iSpindle

Done.
If everything is configured correctly, the database should receive the iSpindle data and your device(s) should show up in Ubidots, if you have enabled forwarding.    
[Here](web/README_en.md) are some charts I made for visualization I found essential.    

Have fun!

Yours,
Tozzi (stephan@sschreiber.de)



