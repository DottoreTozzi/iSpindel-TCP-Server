<?php

/* 
Visualizer for iSpindle using genericTCP with mySQL
Shows mySQL iSpindle data on the browser as a graph via Highcharts:
http://www.highcharts.com

Data access via mySQL for the charts is defined in here.

For the original project itself, see: https://github.com/universam1/iSpindel

Got rid of deprecated stuff, ready for Debian Stretch now.

Tozzi (stephan@sschreiber.de), Nov 25 2017

Apr 28 2019:
Intruduced selects for handling of strings from database. This allows usage of multiple languages based on DB configuration
Some changes required in the selects as UTF8mb4 encoding is required. Some tables parameters in the DB need to be changed to utf8mb4
Implemented function for spindle calibration. Spindles can now be calibrated via web interface.
Settings are now stored in the database and not in iSpindle.py. Some functions were added to change settings from web interface.
Select for new diagram introduced: apparent attenuation and ABV calculation with time trend possible.
Select for initial/original gravity added. Average of first two hours after reset for spindle are used to calculate OG
Added select to calculate delta plato for a defined timeframe and display in a trendchart.

Oct 14 2018:
Added Moving Average Selects, thanks to nice job by mrhyde
Minor fixes

Nov 04 2018:
Update of SQL queries for moving average calculations as usage of multiples spindles at the same time caused an issue and resulted in a mixed value of both spindle data 

Nov 16 2018
Function getcurrentrecipe rewritten: Recipe Name will be only returned if reset= true or timeframe < timeframe of last reset
Return of variables changed for all functions that return x/y diagram data. Recipe name is added in array and returned to php script

Jan 24 2019
- Function added to update TCP Server settings in Database
- Added ability to read field description from sql database for diefferent languages. can be easily expanded for more languages
- Function to write calibration data back to databses added which is used by calibration.php. Usercan send calibration data through frontend and does not need to open phpadmin

 */

// get despription fields from strings table in database.
// Language setting from settings database is used to return field in corresponding language
// e.e. Language = DE --> Description_DE column is selected
// can be extended w/o change of php code. to add for instance french, add column Description_FR to settings and strings tables.
// Add desriptions and set LANGUAGE in settings Database to FR
// File is the file which is calling the function (has to be also used in the strings table)
// field is the field for hich the description will be returned 

function get_field_from_sql($conn, $file, $field)
{
// set connection to utf-8 to display characters like umlauts correctly    
    mysqli_set_charset($conn, "utf8mb4");
// query to get language setting
    $sql_language = mysqli_query($conn, "SELECT value FROM Settings WHERE Section = 'GENERAL' AND Parameter = 'LANGUAGE'") or die(mysqli_error($conn));
    $LANGUAGE = mysqli_fetch_array($sql_language);
// choose corresponding description column for selected language
    $DESCRIPTION = "Description_".$LANGUAGE[0];
    $q_sql = "SELECT " . $DESCRIPTION . " FROM Strings WHERE File = '" . $file. "' and Field = '" . $field . "'";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
    $rows = mysqli_num_rows($result);
    if($rows > 0) {
        $r_row = mysqli_fetch_array($result);
        $return_value = $r_row[0];
        if ($return_value == '') {
            $return_value = 'No description in your Language. Please Edit Strings table.';
            }
        return $return_value;
        }
    else {
        return 'No Parameter in Database';
        }
}

// Function to write iSpindel Server settings back to sql database. Function is used by settings.php
function UpdateSettings($conn, $Section, $Parameter, $value)
{
// added to wite newline for csv file correctly to database    
    $value= str_replace('\\', '\\\\', $value);
    $q_sql = mysqli_query($conn, "UPDATE Settings SET value = '" . $value . "' WHERE Section = '" . $Section . "' AND Parameter = '" . $Parameter . "'" . " AND DeviceName = ''") or die(mysqli_error($conn));
    return 1;
}

// Retrieves timestamp of last dataset for corresponding Spindle. If timestamp is older than timeframehours, false will be returned
// Difference between last available data and selected timeframe is calculated and displayed in diagram to go more days back  
function isDataAvailable($conn, $iSpindleID, $Timeframehours)
{
    $q_sql = mysqli_query($conn, "SELECT MAX(UNIX_TIMESTAMP(Timestamp)) AS Timestamp FROM Data WHERE Name ='" . $iSpindleID . "'") or die(mysqli_error($conn));
    $now = time();
    $startdate = $now - $Timeframehours * 3600;
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql);
        $valTimestamp = $r_row['Timestamp'];
        $TimeDiff = $startdate - $valTimestamp;
        $go_back = round(($TimeDiff / (3600 * 24)) + 0.5);
        if ($TimeDiff < 0) {
            $DataAvailable = true;
        } else {
            $DataAvailable = false;
        }
    }
    return array($DataAvailable, $go_back);
}

// Used in calibration.php Values for corresponding SpindleID will be either updated (if already in database) or added to table calibration in SQL database
function setSpindleCalibration($conn, $ID, $Calibrated, $const1, $const2, $const3)
{
// if spindle is calibrated, fields only need to be updated. If not, we need to insert a new row to the calibration database
    if ($Calibrated) {
        $q_sql = mysqli_query($conn, "UPDATE Calibration SET const1 = '" . $const1 . "', const2 = '" . $const2 . "', const3 = '" . $const3 . "' WHERE ID = '" . $ID . "'") or die(mysqli_error($conn));
    } else {
        $q_sql = mysqli_query($conn, "INSERT INTO Calibration (ID, const1, const2, const3) VALUES ('" . $ID . "', '" . $const1 . "', '" . $const2 . "', '" . $const3 . "')") or die(mysqli_error($conn));
    }
    return 1;
}

// Function retrieves 'latest' SpindleID for Spindelname if available. ID is used to query calibration table for existing calibration
// If data is available, parameters will be send to form (calibration.php). If not, Calibration_exists is false and empty values will be returned
function getSpindleCalibration($conn, $iSpindleID = 'iSpindel000')
{
    $q_sql0 = mysqli_query($conn, "SELECT DISTINCT ID FROM Data WHERE Name = '" . $iSpindleID . "'AND (ID <>'' OR ID <>'0') ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
    if (!$q_sql0) {
        echo "Fehler beim Lesen der ID";
    }
    $valID = '0';
    $Calibration_exists = false;
    $valconst1 = '';
    $valconst2 = '';
    $valconst3 = '';
    $rows = mysqli_num_rows($q_sql0);
    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql0);
        $valID = $r_row['ID'];
        $q_sql1 = mysqli_query($conn, "SELECT const1, const2, const3
                               FROM Calibration WHERE ID = " . $valID) or die(mysqli_error($conn));
        $rows1 = mysqli_num_rows($q_sql1);
        if ($rows1 > 0) {
            $Calibration_exists = true;
            $r_row = mysqli_fetch_array($q_sql1);
            $valconst1 = $r_row['const1'];
            $valconst2 = $r_row['const2'];
            $valconst3 = $r_row['const3'];
        }
    }
    return array(
        $Calibration_exists,
        $valconst1,
        $valconst2,
        $valconst3,
        $valID
    );
}

// get current interval for Spindel to derive number of rows for moving average calculation with sql windows functions
function getCurrentInterval($conn, $iSpindleID)
{
    $q_sql = mysqli_query($conn, "SELECT Data.Interval as frequency
                FROM Data
                WHERE Name = '" . $iSpindleID . "'
                ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));

    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql);
        $valInterval = $r_row['frequency'];
        return $valInterval;
    }
}


// remove last character from a string
function delLastChar($string = "")
{
    $t = substr($string, 0, -1);
    return ($t);
}
//Returns name of Recipe for current fermentation - Name can be set with reset.
function getCurrentRecipeName($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    mysqli_set_charset($conn, "utf8mb4");
    $q_sql1 = mysqli_query($conn, "SELECT Data.Recipe, Data.Timestamp FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.Timestamp >= (SELECT max( Data.Timestamp )FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true) LIMIT 1") or die(mysqli_error($conn));


    $q_sql2 = mysqli_query($conn, "SELECT Data.Timestamp FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)                                                                                                                                                                          AND Timestamp <= NOW() LIMIT 1") or die(mysqli_error($conn));

    $rows = mysqli_num_rows($q_sql1);


    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql1);
        $t_row = mysqli_fetch_array($q_sql2);
        $RecipeName = '';
        $showCurrentRecipe = false;
        $TimeFrame = $t_row['Timestamp'];
        $ResetTime = $r_row['Timestamp'];
        if ($reset == true) {
            $RecipeName = $r_row['Recipe'];
            $showCurrentRecipe = true;
        } else {
            if ($ResetTime < $TimeFrame) {
                $RecipeName = $r_row['Recipe'];
                $showCurrentRecipe = true;
            }
        }
        return array(
            $RecipeName,
            $showCurrentRecipe
        );

    }
}

// Get calaculate initial gravity from database after last reset. First two hours after last reset will be used. 
// This can be used to calculate apparent attenuation in svg_ma.php

function getInitialGravity($conn, $iSpindleID = 'iSpindel000')
{
    $isCalibrated = 0; // is there a calbration record for this iSpindle?
    $valAngle = '';
    $valDens = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    $where = "WHERE Name = '" . $iSpindleID . "'
              AND Timestamp > (Select MAX(Data.Timestamp) FROM Data  WHERE Data.ResetFlag = true AND Data.Name = '" . $iSpindleID . "') 
              AND Timestamp < DATE_ADD((SELECT MAX(Data.Timestamp)FROM Data WHERE Data.Name = '" . $iSpindleID . "' 
              AND Data.ResetFlag = true), INTERVAL 2 HOUR)";

    $q_sql = mysqli_query($conn, "SELECT AVG(Data.Angle) as angle FROM Data " . $where ) or die(mysqli_error($conn));

    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // get unique hardware ID for calibration
        $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
        $rowsID = mysqli_num_rows($u_sql);
        if ($rowsID > 0) {
            // try to get calibration for iSpindle hardware ID
            $r_id = mysqli_fetch_array($u_sql);
            $uniqueID = $r_id['ID'];
            $f_sql = mysqli_query($conn, "SELECT const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
        $r_row = mysqli_fetch_array($q_sql);
            $angle = $r_row['angle'];
            $dens = round(($const1 * pow($angle, 2) + $const2 * $angle + $const3),2); // complete polynome from database
        return array(
            $isCalibrated,
            $dens
        );
    }
}

// Get values from database for selected spindle, between now and timeframe in hours ago  
function getChartValues($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "'                                                                                                                                                                                                    
                  AND Timestamp >= (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "'                                                                                                                                                                                                    
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)                                                                                                                                                              
            AND Timestamp <= NOW()";
    }
    mysqli_set_charset($conn, "utf8mb4");

    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, recipe, battery, rssi                                                                                                                                    
                         FROM Data " . $where . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
    
    // retrieve number of rows                                                                                                                                                                                                                  
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $valAngle = '';
        $valTemperature = '';
        $valBattery = '';
        $valRSSI = '';
        // retrieve and store the values as CSV lists for HighCharts                                                                                                                                                                              
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['angle'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valBattery .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['battery'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            if ($r_row['rssi']) {
                $valRSSI .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['rssi'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            }

        }


        return array(
            $valAngle,
            $valTemperature,
            $valBattery,
            $valRSSI
        );
    }
}
// Get values from database for selected spindle, between now and timeframe in hours ago and calculate Moving average 
function getChartValues_ma($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $movingtime, $reset = defaultReset)
{

// get interval and calculate number of rows for movig average calculation if windows function can be used    
    $Interval = (getCurrentInterval($conn, $iSpindleID));
    $Rows = round($movingtime / ($Interval / 60));


    $where_ma = '';
    if ($reset) {
        $where = "Data.Timestamp > (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp > (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_ma = "Data2.Timestamp > (Select max(Data2.Timestamp) FROM Data AS Data2  WHERE Data2.ResetFlag = true AND Data2.Name = '" . $iSpindleID . "') AND";


    } else {
        $where = "Data.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) AND Data.Timestamp <= NOW()";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)
                                                and Data1.Timestamp <= NOW()";

    }
// test if sql windows functions are working. Therefore newer sql server version is required
    mysqli_set_charset($conn, "utf8mb4");

    if (!$q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Data.Timestamp) as unixtime, Data.temperature, Data.angle, Data.recipe,
                                AVG(Data.Angle) OVER (ORDER BY Data.Timestamp ASC ROWS " . $Rows . " PRECEDING) AS mv_angle 
                FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND " . $where)) 
//if this is not working fall back to old calculation which is more cpu intensive
    {
        $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Data1.Timestamp) as unixtime, Data1.temperature, Data1.angle, Data1.recipe,
                                (SELECT SUM(Data2.Angle) / COUNT(Data2.Angle)
                                                                FROM Data AS Data2
                                WHERE Data2.Name = '" . $iSpindleID . "' AND " . $where_ma . " TIMESTAMPDIFF(MINUTE, Data2.Timestamp, Data1.Timestamp) BETWEEN 0 and " . $movingtime . " ) AS mv_angle
                                                                FROM Data AS Data1 " . $where_oldDB . " ORDER BY Data1.Timestamp ASC") or die(mysqli_error($conn));


    }
    
    // retrieve number of rows              
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $valAngle = '';
        $valTemperature = '';
        // retrieve and store the values as CSV lists for HighCharts          
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['mv_angle'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
        }
        return array(
            $valAngle,
            $valTemperature
        );
    }
}

// Get values from database including gravity (Fw 5.0.1 required) for selected spindle, between now and timeframe in hours ago
function getChartValuesPlato($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= (Select max(Timestamp) FROM Data WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) 
            AND Timestamp <= NOW()";
    }
    mysqli_set_charset($conn, "utf8mb4");

    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, gravity, recipe
                          FROM Data " . $where . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
    
    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $valAngle = '';
        $valTemperature = '';
        $valGravity = '';
        
        // retrieve and store the values as CSV lists for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $valAngle .= '[' . $jsTime . ', ' . $r_row['angle'] . '],';
            $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['gravity'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
        }

        return array(
            $valAngle,
            $valTemperature,
            $valGravity
        );
    }
}

// Get current values (angle, temperature, battery)
function getCurrentValues($conn, $iSpindleID = 'iSpindel000')
{
    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, battery
                FROM Data
                WHERE Name = '" . $iSpindleID . "'
                ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));

    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql);
        $valTime = $r_row['unixtime'];
        $valTemperature = $r_row['temperature'];
        $valAngle = $r_row['angle'];
        $valBattery = $r_row['battery'];
        return array(
            $valTime,
            $valTemperature,
            $valAngle,
            $valBattery
        );
    }
}

// Get current values (angle, temperature, battery, rssi)
// Keeping original function unchanged in order to preserve backwards compatibility.
// RSSI is brand new with this version and version 5.8.x of iSpindle Firmware.
function getCurrentValues2($conn, $iSpindleID = 'iSpindel000')
{
    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, battery, `interval`, rssi
                FROM Data                                                                                
                WHERE Name = '" . $iSpindleID . "'              
                ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));

    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql);
        $valTime = $r_row['unixtime'];
        $valTemperature = $r_row['temperature'];
        $valAngle = $r_row['angle'];
        $valBattery = $r_row['battery'];
        $valInterval = $r_row['interval'];
        $valRSSI = $r_row['rssi'];
        return array(
            $valTime,
            $valTemperature,
            $valAngle,
            $valBattery,
            $valInterval,
            $valRSSI
        );
    }
}

// Get calibrated values from database for selected spindle, between now and [number of hours] ago
// Old Method for Firmware before 5.x
function getChartValuesPlato4($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    $isCalibrated = 0; // is there a calbration record for this iSpindle?
    $valAngle = '';
    $valTemperature = '';
    $valDens = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) 
            AND Timestamp <= NOW()";
    }

    mysqli_set_charset($conn, "utf8mb4");

    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, recipe
                           FROM Data " . $where . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
    
    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // get unique hardware ID for calibration
        $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
        $rowsID = mysqli_num_rows($u_sql);
        if ($rowsID > 0) {
            // try to get calibration for iSpindle hardware ID
            $r_id = mysqli_fetch_array($u_sql);
            $uniqueID = $r_id['ID'];
            $f_sql = mysqli_query($conn, "SELECT const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
        // retrieve and store the values as CSV lists for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $angle = $r_row['angle'];
            $dens = $const1 * pow($angle, 2) + $const2 * $angle + $const3; // complete polynome from database

            $valAngle .= '[' . $jsTime . ', ' . $angle . '],';
            $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";



        }
        return array(
            $isCalibrated,
            $valDens,
            $valTemperature,
            $valAngle
        );
    }
}

function getChartValuesPlato4_delta($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $movingtime = 720, $reset = defaultReset)
{
    $Interval = (getCurrentInterval($conn, $iSpindleID));
    $Rows = round($movingtime / ($Interval / 60));
    
    $isCalibrated = 0; // is there a calbration record for this iSpindle?
    $valAngle = '';
    $valTemperature = '';
    $valDens = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    // get unique hardware ID for calibration
    $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
    $rowsID = mysqli_num_rows($u_sql);
        if ($rowsID > 0) {
            // try to get calibration for iSpindle hardware ID
            $r_id = mysqli_fetch_array($u_sql);
            $uniqueID = $r_id['ID'];
            $f_sql = mysqli_query($conn, "SELECT const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }

    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "'
            AND Timestamp > (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "'
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)
            AND Timestamp <= NOW()";
    }
    mysqli_set_charset($conn, "utf8mb4");

         $p_sql = mysqli_query($conn, "SET @x:=0") or die(mysqli_error($conn));
         if($q_sql = mysqli_query($conn, "SELECT * 
                                       FROM (SELECT (@x:=@x+1) AS x, 
                                       UNIX_TIMESTAMP(mt.Timestamp) as unixtime, 
                                       mt.name, 
                                       mt.recipe, 
                                       mt.temperature, 
                                       mt.angle, 
                                       mt.Angle*mt.Angle*" . $const1 . " + mt.Angle*" . $const2 . " + " . $const3 . " AS Calc_Plato, 
                                       mt.Angle*mt.Angle*" . $const1 . "+mt.Angle*" . $const2 . "+" . $const3 . " - lag(mt.Angle*mt.Angle*" . $const1 . "+mt.Angle*" . $const2 . "+" . $const3 . ", " . $Rows . ") 
                                       OVER (ORDER BY mt.Timestamp) DeltaPlato 
                                       FROM Data mt " .$where . " order by Timestamp) t WHERE x MOD " . $Rows . " = 0"))
         {

         // retrieve number of rows
         $rows = mysqli_num_rows($q_sql);
         while ($r_row = mysqli_fetch_array($q_sql)) {
             $jsTime = $r_row['unixtime'] * 1000;
             $angle = $r_row['angle'];
             $Ddens = $r_row['DeltaPlato'];
             if ($Ddens == '') {
                 $Ddens= 0;
             }
             $valAngle .= '[' . $jsTime . ', ' . $angle . '],';
             $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $Ddens . ", recipe: \"" . $r_row['recipe'] . "\"},";
             $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
             }
         return array(
             $isCalibrated,
             $valDens,
             $valTemperature,
             $valAngle
         );
         }
         else {
             echo "Select for this diagram is using 'SQL Windows functions'. Either your Data table is still empty, or your Database does not seem to support it. If you want to use these functions you need to upgrade to a newer version of your SQL installation.<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png></a>";
             exit;
         }
}




// Get calibrated values from database for selected spindle, between now and [number of hours] ago
// Old Method for Firmware before 5.x
function getChartValuesPlato4_ma($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $movingtime, $reset = defaultReset)
{
    $isCalibrated = 0; // is there a calbration record for this iSpindle?
    $valAngle = '';
    $valTemperature = '';
    $valDens = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    $where_ma = '';

    $Interval = (getCurrentInterval($conn, $iSpindleID));
    $Rows = round($movingtime / ($Interval / 60));



    if ($reset) {
        $where = "Data.Timestamp > (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp > (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_ma = "Data2.Timestamp > (Select max(Data2.Timestamp) FROM Data AS Data2  WHERE Data2.ResetFlag = true AND Data2.Name = '" . $iSpindleID . "') AND";
    } else {
        $where = "Data.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) AND Data.Timestamp <= NOW()";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)
                                                and Data1.Timestamp <= NOW()";
    }
    mysqli_set_charset($conn, "utf8mb4");
    if (!$q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Data.Timestamp) as unixtime, Data.temperature, Data.angle, Data.recipe,
                                AVG(Data.Angle) OVER (ORDER BY Data.Timestamp ASC ROWS " . $Rows . " PRECEDING) AS mv_angle
                                FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND " . $where)) {
        $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Data1.Timestamp) as unixtime, Data1.temperature, Data1.angle, Data1.recipe,
                                (SELECT SUM(Data2.Angle) / COUNT(Data2.Angle)
                                                                FROM Data AS Data2
                                WHERE Data2.Name = '" . $iSpindleID . "' AND " . $where_ma . " TIMESTAMPDIFF(MINUTE, Data2.Timestamp, Data1.Timestamp) BETWEEN 0 and " . $movingtime . " ) AS mv_angle
                                                                FROM Data AS Data1 " . $where_oldDB . " ORDER BY Data1.Timestamp ASC") or die(mysqli_error($conn));
    }
    
    
    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // get unique hardware ID for calibration
        $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
        $rowsID = mysqli_num_rows($u_sql);
        if ($rowsID > 0) {
            // try to get calibration for iSpindle hardware ID
            $r_id = mysqli_fetch_array($u_sql);
            $uniqueID = $r_id['ID'];
            $f_sql = mysqli_query($conn, "SELECT const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
        // retrieve and store the values as CSV lists for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $angle = $r_row['mv_angle'];
            $dens = $const1 * pow($angle, 2) + $const2 * $angle + $const3; // complete polynome from database

            $valAngle .= '[' . $jsTime . ', ' . $angle . '],';
            $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";


        }

        return array(
            $isCalibrated,
            $valDens,
            $valTemperature,
            $valAngle
        );
    }
}

// calculate apparent attenuation and alcohol by volume over time
// formulars were taken from http://fabier.de/biercalcs.html and https://brauerei.mueggelland.de/refracto.html
// only available with start from last reset
function getChartValuesSVG_ma($conn, $iSpindleID = 'iSpindel000', $movingtime)
{
    $isCalibrated = 0; // is there a calbration record for this iSpindle?
    $valAngle = '';
    $valTemperature = '';
    $valSVG = '';
    $valABV = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    $where_ma = '';

// get current interval for spindel to derive required rows for calculation if initial gravity
    $Interval = (getCurrentInterval($conn, $iSpindleID));
    $Rows = round($movingtime / ($Interval / 60));
    list($isCalibrated, $InitialGravity) = (getInitialGravity($conn, $iSpindleID));

// use moving average when retrieving data from database
        $where = "Data.Timestamp > (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp > (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_ma = "Data2.Timestamp > (Select max(Data2.Timestamp) FROM Data AS Data2  WHERE Data2.ResetFlag = true AND Data2.Name = '" . $iSpindleID . "') AND";
// test if windows functions are working (way faster but only available in newer SQL installations)
    mysqli_set_charset($conn, "utf8mb4");

    if (!$q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Data.Timestamp) as unixtime, Data.temperature, Data.angle, Data.recipe,
                                AVG(Data.Angle) OVER (ORDER BY Data.Timestamp ASC ROWS " . $Rows . " PRECEDING) AS mv_angle
                                FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND " . $where)) {
// if winows finctions are not working, use 'manual' calculation of moving average
        $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Data1.Timestamp) as unixtime, Data1.temperature, Data1.angle, Data1.recipe,
                                (SELECT SUM(Data2.Angle) / COUNT(Data2.Angle)
                                                                FROM Data AS Data2
                                WHERE Data2.Name = '" . $iSpindleID . "' AND " . $where_ma . " TIMESTAMPDIFF(MINUTE, Data2.Timestamp, Data1.Timestamp) BETWEEN 0 and " . $movingtime . " ) AS mv_angle
                                                                FROM Data AS Data1 " . $where_oldDB . " ORDER BY Data1.Timestamp ASC") or die(mysqli_error($conn));
    }


    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // get unique hardware ID for calibration
        $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
        $rowsID = mysqli_num_rows($u_sql);
        if ($rowsID > 0) {
            // try to get calibration for iSpindle hardware ID
            $r_id = mysqli_fetch_array($u_sql);
            $uniqueID = $r_id['ID'];
            $f_sql = mysqli_query($conn, "SELECT const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
        // retrieve and store the values as CSV lists for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $angle = $r_row['mv_angle'];
            // calulcation aparent density based on calibration data
            $dens = $const1 * pow($angle, 2) + $const2 * $angle + $const3; // complete polynome from database
            // real density differs fro aparent density
            $real_dens = 0.1808 * $InitialGravity + 0.8192 * $dens;
            // calculte apparent attenuation
            $SVG = ($InitialGravity-$dens)*100/$InitialGravity;   
            // calculate alcohol by weigth and by volume (fabbier calcfabbier calc for link see above)
            $alcohol_by_weight = ( 100 * ($real_dens - $InitialGravity) / (1.0665 * $InitialGravity - 206.65));
            $alcohol_by_volume = ($alcohol_by_weight / 0.795);
            // append values to csv list
            $valAngle .= '[' . $jsTime . ', ' . $angle . '],';
            $valSVG .= '{ timestamp: ' . $jsTime . ', value: ' . $SVG . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valABV .= '{ timestamp: ' . $jsTime . ', value: ' . $alcohol_by_volume . ", recipe: \"" . $r_row['recipe'] . "\"},";
            

        }

        return array(
            $isCalibrated,
            $valSVG,
            $valTemperature,
            $valAngle,
            $valABV
        );
    }
}



?>
