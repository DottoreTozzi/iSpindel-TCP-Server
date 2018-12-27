# iSpindel Generic TCP Server
#### (iSpindle.py Version 1.6.0)

**New (15.10.2018)**
Moving Average Diagrams (thanks to mrhyde).
Also introducing the long-awaited "landing page", index.php.
Select iSpindels having been active for a default of 7 days (change this by appending "?days=x" to the URL or editing include/common_db.php)
Put this on your desktop or have it start up.

**New (02.10.2018)**
CBP3 support thanks to jlanger     
Brewpiless support thanks to ollinator2000     
iSpindel Remote Configuration: a set of config parameters can now be sent to the iSpindel as response.     
Currently, this is limited by the firmware to the "Interval" setting, although you can already set the polynomial and token parameters server side.      
You'll need firmware 6.0.1 or above for this.

**New (15.02.2018)**
New chart wifi.php shows current connection quality (RSSI).          
angle.php, plato.php and plato4.php have been updated and accept the new parameters "days" and "weeks".        
Their subtitles will reflect these changes now and will make the charts easier to read.        
For example, a timeframe of 3600 hours will now be displayed as 21 weeks, 3 days and 0 hours.      
These parameters can be combined completely at will.

**New (12.02.2018)**
iSpindle.py Version 1.4.0        
Updated to [Sam's iSpindel](https://github.com/universam1/iSpindel) Firmware 5.8 and higher.         
Newly sent data (interval, Wi-Fi signal quality) will now be registered and stored to the database accordingly.
Everything should still be backwards compatible.
Debug mode will show a message asking you to consider updating the iSpindel firmware, if an outdated version is being detected.
When updating an existing version of this script, please take a look at MySQL_Update-3.sql and add the required fields to your database accordingly before using this new version of the python script.


**New (20.01.2018)**     
iSpindle.py Version 1.3.3     
The iSpindle config option "token" can now be used as Ubidots Token, in order to only forward the data of certain devices, or use different tokens for several of them.     
Leave blank or put a leading asterisk ("*") in order to exclude an iSpindle from Ubidots forwarding.     
This is now the default behavior (Ubidots Forwarding switched on; new parameter UBI_USE_ISPINDLE_TOKEN enabled, too).     
The global UBIDOTS_TOKEN parameter will now only be used if UBI_USE_ISPINDLE_TOKEN is disabled while UBIDOTS is enabled.     

**New (25.11.2017)**     
Interim Release.      
Restored compatibility with Raspbian/Debian Jessie and PHP7.      
Using mysqli Library now.      
Docs have been updated accordingly and also reflect the new mirror.      

**New (27.09.2017)**  
Added Update Scripts  

**New (31.08.2017)**  
Compatible to firmware >= 5.4.x  
Optional data forwarding to other JSON capable TCP clients, including this one and the upcoming "public server".  
Fixed incoherency with legacy Plato charts (plato4.php), properly using signed parameters in Calibration table now for building the polynomial  
Fixed some minor issues in chart legends (formerly mislabeled but still only in German, sorry for that...)  
Fixed Unix permissions  
Several other small bugfixes  

In order to update from former versions:  
Take a look at the [Install Instructions](INSTALL_en.md).

There will of course also be an updated full image for the Raspberry.  
Please watch this thread on the German Hobbybrauer Forum for the announcement:  
http://hobbybrauer.de/forum/viewtopic.php?f=58&t=12869

Future enhancements and goodies are being announced there, too, so make sure you check it once in a while. ;)

**Older News:**
**New: Charts:**
[Charts](web/README_en.md)

[Installation Instructions](INSTALL_en.md)      

This script was written in Python and its purpose is to accept raw data from an iSpindle via a generic TCP connection, usually in a local network environment.
It purposely avoids unneccessary higher-level protocols such as http, in order to maximize the battery lifetime of the iSpindle and generally make things more easy and transparent, also for you fellow developers out there.

The data received can be stored (or forwarded) in three different ways, non exclusively.    
You can enable or disable them separately, one by one.   
One option is to save incoming data to a CSV (comma separated values) file, one per iSpindle.
This might be useful for example to do a quick import in Excel.   
The second one allows you to write it to a MySQL table.   
And finally, in order to get the best out of two worlds and not have to say goodbye to Ubidots, you can configure the script to foward it all transparently, so Ubidots won't even know it's not connected directly to your iSpindle, with the added advantage of being able to also process your data locally, so, for example, you could come up with some new super nice way to calibrate it.   

In addition, the time your iSpindle has to wait for a connection will decrease, further enhancing its battery life.   
But even without Internet access, you'll be able to process the data your iSpindle sends.

The script is completely platform independent and should run on any OS that supports Python.
It has been tested on Mac OS X (Sierra) and Linux (Debian), but it should work under Windows just as well.
If you own or have rented a dedicated or virtual server, or if there is any computer in your home network that is running 24/7, this should work for you.    
A Raspberry Pi will always do the trick.
Just make sure you have Python installed, and if you are using the MySQL feature, don't forget to install the `python-mysql.connector`, too.
Multithreading is implemented, so even if your multiple iSpindles send their data at the same time, there should be no delays or other problems during the transmission.

When configuring your iSpindle, choose **TCP** as protocol, enter the IP address of the server the script is running on, and enter **9501** as TCP port (which is the default port the script will listen to).

Then, configure the script by opening it in a text editor.
Make sure you adjust all the settings according to the descriptions following below.

Finally, copy it to some path on your server. If using a Raspi, good choices would be `/usr/local/bin` or simply `/home/pi`.

Make it executable by typing `chmod 755 iSpindle.py` on the command line inside the path you've chosen.
Then start it by typing `./iSpindle.py`.
Alternatively (or when using Windows), you can start it by typing `python iSpindle.py`.    
Once it all works, set DEBUG to 0, restart it in the background and enjoy.


### Configuration:

#### General:

	DEBUG = 0      
	PORT = 9501    
	HOST = '0.0.0.0'

**DEBUG** = 1 allows detailed output on the console.
You'll want this when first configuring the script and your iSpindle.
After that, not so much, probably.   
If TCP **Port** is already in use (unlikely), you can change it here to another value.   
**HOST** determines the IP range clients have to be in in order to be allowed to connect. Leave this at 0.0.0.0 for no restrictions.
Port 9501 is usually not reachable from the (potentially hostile) outside unless you are explicitly forwarding it through your router (firewall settings: port forwarding), so, no worries there, usually.
And if you've read this far, you'll probably know what you're doing, anyway... ;)

#### CSV:

	CSV = 0
	OUTPATH = '/home/pi/iSpindel'
	DELIMITER = ';'
	NEWLINE = '\r\n'
	DATETIME = 1    

Set **CSV** to 1 if you want CSV files to be generated.    
**OUTPATH** configures the path CSV files will be stored at. Share it in your local network to allow easy import for Excel or whatever frontend you want to use.    
**DELIMITER** sets the character to be used to separate the data fields. ';' is usually good for Excel. Common choices would be ',' or ';'.    
**NEWLINE** is normally '\n', but if you're using anything made by Microsoft, use '\n\r'.    
**DATETIME** should be left at its default setting of 1, unless for some reason you don't want timestamps being added to the data output.


#### MySQL

	SQL = 1
	SQL_HOST = '127.0.0.1'
	SQL_DB = 'iSpindle'
	SQL_TABLE = 'Data'
	SQL_USER = 'iSpindle'
	SQL_PASSWORD = 'xxxxxxxx'


If you want to switch off MySQL connectivity, set **SQL** to 0 and all other settings will be ignored.     
**SQL\_HOST** defines the DB host's IP address. Usually, this will be 'localhost' or 127.0.0.1.    
The remaining fields define the connection to the database.    
By default, we assume the database name and user ID are 'iSpindle', and the table name is 'Data'.

In order to create a table inside your MySQL database accordingly, use this SQL statement:

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

Of course you could always just log in to the database using your default admin account, but a better solution is to create a dedicated user for the server script:

	CREATE USER 'iSpindle'@'localhost' IDENTIFIED BY 'password';
	GRANT ALL PRIVILEGES ON iSpindle . * TO 'iSpindle'@'localhost';
	FLUSH PRIVILEGES;

The script is able to create additional table columns dynamically from the received JSON dataset.    
This is, however, only recommended if you are developing your own firmware and wish to store some variables not being exported by default.
If your server is reachable from the Internet, make sure **ENABLE\_ADDCOLS** is 0.
In its current version I cannot guarantee the script is not vulnerable to SQL Injection attacks when this is enabled (set to 1).
If unsure, set it to 0.


#### Ubidots Forwarding

	UBIDOTS = 1
	UBI_USE_ISPINDLE_TOKEN = 1
	UBI_TOKEN = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'

**UBIDOTS** = 0 will switch off Ubidots Forwarding.    
**UBI\_TOKEN** is where you can globally (i.e. for all your devices) enter your personal Ubidots Token (see iSpindle docs).
In more recent versions (beginning with 1.3.3) it is recommended to enter your Ubidots token within the iSpindel's Configuration and use the default UBI_USE_ISPINDLE_TOKEN parameter setting of "1" (true), so that the Script will use this entry instead, for each iSpindel individually, as is the case with the standard (direct) connection.

Your data should now show up in Ubidots as usual, plus you have it available locally to fiddle around with.    
Ubidots will not know the difference and even create new devices just as well.

Have Fun!    
Tozzi (stephan@sschreiber.de)
