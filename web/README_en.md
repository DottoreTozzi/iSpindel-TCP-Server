# Charts From Local Server 

A short heads-up on recent updates:
We now have a "landing page", highly requested and anticipated, in order to select charts from a central html page.
Fittingly, its name is "index.php".
You can give it a parameter ("?days=[days]") in order to make active iSpindels choosable from the dropdown menu.
By default, iSpindels which have been active in the past 7 days will be made available.
From there, you can choose among the different visualizations.
There is a lot of room to expand on this, of course.
It looks ugly, it doesn't do all it COULD do, but it works.
Any help will be highly appreciated.

For now, we'll leave it as-is. For me, personally, it's cool this way.

---------------------

While exporting CSVs or directly accessing the database via ODBC from, for example, Excel, is fine for data analysis, we'll definitely also want a quick way to take a glance at the current fermentation.
So, here are a few essential charts, developed using [highcharts](http://www.highcharts.com), browser accessible.
Especially nice in Firefox fullscreen mode on a Raspi touch display. Just put some bookmarks on the Raspi Desktop.

We'll need a working [install](../INSTALL_en.md) of the backend, including mySQL and Apache2.

My goal was to implement a solution as simple yet effective as possible.

I've implemented these basic charts:

* angle.php - tilt and temperature over the past x hours
* plato4.php - no longer required as per firmware 5.x - gravity and temperature over the past x hours (calibration record required as explained below). This is a cool way to pre-calibrate your iSpindle, however, while it still floats inside the fermenter. You will already receive extract measurements instead of just the iSpindle's angle by simply editing its record in the "Calibration" table.
* plato.php - gravity and temperature over the past x hours, requires firmware 5.x
* battery.php - current battery voltage
* status.php - battery, tilt and temperature of the specified iSpindle

In order to show these charts we pass arguments via GET in order to be able to bookmark the URLs:

* http://raspi/iSpindle/angle.php?name=MySpindle1&hours=24
* http://raspi/iSpindle/status.php?name=MySpindle2

reset_now defines a timestamp (start of fermentation) and the graph shows only the entries after this timestamp:
* http://meinraspi/iSpindle/reset_now.php?name=MeineSpindel2
* http://meinraspi/iSpindle/angle.php?name=MeineSpindel2&reset=true

I hope I've built sort of a foundation with templates for lots of future enhancements.
I am aware that there's probably a ton of things I could have solved more elegantly and there's room for improvement galore.     
Contributions are by all means welcome. Looking forward!


### A Few Hints Regarding Installation:
#### Apache2:
In order for apache to "see" the charts, they'll have to be somewhere in **/var/www/html**.
(This might vary in distributions other than Raspbian).
I achieve that by simply creating a symlink there, pointing towards my work directory.

      cd /var/www/html    
      sudo ln -sf /home/pi/iSpindel-Srv/web/ iSpindle
      sudo chown -R pi:pi iSpindle/*
      sudo chown -h pi:pi iSpindle

#### Database Interface:
You might have to configure the database connection, found in include/common_db.php, so edit this file section:

      // configure your database connection here:
      define('DB_SERVER',"localhost");
      define('DB_NAME',"iSpindle");
      define('DB_USER',"iSpindle");
      define('DB_PASSWORD',"password");

#### Calibration (Angle:Gravity)
Note: This is no longer necessary as per firmware 5.0.1.      
The iSpindle now has its own algorithm for density/gravity output.      
The following applies if you are still using an older firmware version.      
It also comes in handy, however, if you have an iSpindel "floating" in your fermenter and want to calibrate it on-the-fly for the current batch.

Before you can use plato4.php to display the calculated gravity (%w/w) in Plato degrees, you'll need enter the [calibration results](../../../docs/Calibration_en.md) and add them to the database.      
The reference being used is the spindle's unique hardware id, stored as "ID" in the 'Data' table.    
First, if you haven't done that before, you'll need to create a second table now:
     
     CREATE TABLE `Calibration` (
     `ID` varchar(64) COLLATE ascii_bin NOT NULL,
     `const1` double NOT NULL,
     `const2` double NOT NULL,
     `const3` double NOT NULL,
     PRIMARY KEY (`ID`)
     ) 
     ENGINE=InnoDB DEFAULT CHARSET=ascii 
     COLLATE=ascii_bin COMMENT='iSpindle Calibration Data';

ID is the iSpindel's unique hardware ID as shown in the 'Data' table.
const1, 2, 3 are the three coefficients of the polynom you have got as a result of calibrating your iSpindel:

gravity = const1 * tilt<sup>2</sup> - const2 * tilt + const3

You could enter these using phpMyAdmin, or on a mysql prompt, you'd do:

    INSERT INTO Calibration (ID, const1, const2, const3)
    VALUES ('123456', 0.013355798, 0.776391729, 11.34675255);

Have Fun,     
Tozzi (stephan@sschreiber.de)
