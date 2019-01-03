<?php

/* 
Visualizer for iSpindle using genericTCP with mySQL
Shows mySQL iSpindle data on the browser as a graph via Highcharts:
http://www.highcharts.com

Data access via mySQL for the charts is defined in here.

For the original project itself, see: https://github.com/universam1/iSpindel

Got rid of deprecated stuff, ready for Debian Stretch now.

Tozzi (stephan@sschreiber.de), Nov 25 2017

Oct 14 2018:
Added Moving Average Selects, thanks to nice job by mrhyde
Minor fixes

Nov 04 2018:
Update of SQL queries for moving average calculations as usage of multiples spindles at the same time caused an issue and resulted in a mixed value of both spindle data 

Nov 16 2018
Function getcurrentrecipe rewritten: Recipe Name will be only returned if reset= true or timeframe < timeframe of last reset
Return of variables changed for all functions that return x/y diagram data. Recipe name is added in array and returned to php script

 */
// Function to write iSpindel Server settings back to sql database. Function is used by settings.php
function UpdateSettings($conn, $Section, $Parameter, $value)
{
    $value= str_replace('\\', '\\\\', $value);
    $q_sql = mysqli_query($conn, "UPDATE Settings SET value = '" . $value . "' WHERE Section = '" . $Section . "' AND Parameter = '" . $Parameter . "'") or die(mysqli_error($conn));
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
        $where = "Data.Timestamp >= (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp >= (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_ma = "Data2.Timestamp >= (Select max(Data2.Timestamp) FROM Data AS Data2  WHERE Data2.ResetFlag = true AND Data2.Name = '" . $iSpindleID . "') AND";


    } else {
        $where = "Data.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) AND Data.Timestamp <= NOW()";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)
                                                and Data1.Timestamp <= NOW()";

    }
// test if sql windows functions are working. Therefore newer sql server version is required
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
            AND Timestamp >= (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "'
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)
            AND Timestamp <= NOW()";
    }

         $p_sql = mysqli_query($conn, "SET @x:=0") or die(mysqli_error($conn));
         $q_sql = mysqli_query($conn, "SELECT * 
                                       FROM (SELECT (@x:=@x+1) AS x, 
                                       UNIX_TIMESTAMP(mt.Timestamp) as unixtime, 
                                       mt.name, 
                                       mt.recipe, 
                                       mt.temperature, 
                                       mt.angle, 
                                       mt.Angle*mt.Angle*" . $const1 . " + mt.Angle*" . $const2 . " + " . $const3 . " AS Calc_Plato, 
                                       mt.Angle*mt.Angle*" . $const1 . "+mt.Angle*" . $const2 . "+" . $const3 . " - lag(mt.Angle*mt.Angle*" . $const1 . "+mt.Angle*" . $const2 . "+" . $const3 . ", " . $Rows . ") 
                                       OVER (ORDER BY mt.Timestamp) DeltaPlato 
                                       FROM Data mt " .$where . " order by Timestamp) t WHERE x MOD " . $Rows . " = 0") or die(mysqli_error($conn));



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
        $where = "Data.Timestamp >= (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp >= (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_ma = "Data2.Timestamp >= (Select max(Data2.Timestamp) FROM Data AS Data2  WHERE Data2.ResetFlag = true AND Data2.Name = '" . $iSpindleID . "') AND";
    } else {
        $where = "Data.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) AND Data.Timestamp <= NOW()";
        $where_oldDB = "WHERE Data1.Name = '" . $iSpindleID . "'
                                                AND Data1.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)
                                                and Data1.Timestamp <= NOW()";
    }
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

?>
