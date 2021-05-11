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

May 2020
- Version 3.0 with some major changes
- combined some functions one.  
- --> one function for all regular trend diagrams
- --> one function for all moving average diagrams and the attenuation alcohol diagram
- added function to migrate database automatically to use archive functionality
- added functions to show archive diagrams for each fermentation
- archives (old fermentation data) can be removed from the database individually
- added function to export data from archive as CSV file
- added functions to export and import data and archive table
- added functiones to upgrade settings and strings table via web interface. This makes the correspo0nding sql files for update via phpmyadmin obsolete
- added function to export and import individual settings
- added fucntions to select and use css layouts for appearance of wab pages
- added iGauge functionality (thansk to JackFrost))
- Updated and added comments on the code


 */

// get despription fields from strings table in database.
// Language setting from settings database is used to return field in corresponding language
// e.e. Language = DE --> Description_DE column is selected
// can be extended w/o change of php code. to add for instance french, add column Description_FR to settings and strings tables.
// Add desriptions and set LANGUAGE in settings Database to FR
// File is the file which is calling the function (has to be also used in the strings table)
// field is the field for hich the description will be returned 

// include configuration fils in web/config directory

//    if ((include_once './config/common_db_config.php') == FALSE){
//       include_once("./config/common_db_default.php");
//    }
//       include_once("./config/tables.php");

// function to write debug messages to the console of the web browser
// CONSOLE_LOG parameter has to be set to 1 in common_db_.....php config file
function write_log($data)
{
    try {
        $log_to_console = CONSOLE_LOG;
    }
    catch (Exception $e) {
        $log_to_console = 0;
    } 
    if ($log_to_console == 1 ) {
        echo '<script>';
        echo 'console.log('. json_encode( $data ) .')';
        echo '</script>'; 
    }
}

// function to prepare data for CSV export
function cleanData(&$str)
  {
    if($str == 't') $str = 'TRUE';
    if($str == 'f') $str = 'FALSE';
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

// function to migrate database for archive usage (introduced with vcersion 3.0 of server)
function upgrade_data_table($conn)
{
// get max execition time from ini file
$max_time = ini_get("max_execution_time");

// set max php execution time to 1 hr for this task
set_time_limit (3600);

// Create Archive Table
$create_recipe_table = "CREATE TABLE `Archive` ( `Recipe_ID` INT NOT NULL AUTO_INCREMENT , 
                                         `Name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                                         `ID` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, 
                                         `Recipe` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, 
                                         `Start_date` DATETIME NOT NULL , 
                                         `End_date` DATETIME NULL DEFAULT NULL, 
                                         `const0` DOUBLE NULL DEFAULT NULL,
                                         `const1` DOUBLE NULL DEFAULT NULL, 
                                         `const2` DOUBLE NULL DEFAULT NULL, 
                                         `const3` DOUBLE NULL DEFAULT NULL, 
                                         PRIMARY KEY (`Recipe_ID`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";
$result = mysqli_query($conn, $create_recipe_table) or die(mysqli_error($conn));

// Add Recipe_ID , Internal and Comment columns to Data table
// Internal column is currently used for RID_END flag, but can be used later for other pruposes in addition
$q_sql = "SHOW COLUMNS FROM `Calibration` LIKE 'const0'";
$result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
$rows = mysqli_fetch_array($result);
if ($rows[0] != 'const0'){
    $q_sql="ALTER TABLE `Calibration` ADD COLUMN `const0` DOUBLE DEFAULT (0) AFTER `ID`";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
    }
$q_sql="ALTER TABLE `Data` ADD COLUMN `Recipe_ID` INT NOT NULL AFTER `Recipe`";
$result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
$q_sql="ALTER TABLE `Data` ADD `Internal` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `Recipe_ID`";
$result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
$q_sql="ALTER TABLE `Data` ADD `Comment` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `Internal`";
$result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));


// Select all entries with resetflag and sort them with ascending data to write initial recipe_ID
$q_sql="Select * FROM Data WHERE ResetFlag = '1' ORDER BY Timestamp ASC";
$result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
$rows = mysqli_num_rows($result);
if($rows > 0) {
    // start with recipe ID 1
    $recipe_id = 1;
    while ($row = mysqli_fetch_array($result)){
        // Get Start Timestamp, Spindle Name, Spindle ID and Recipe Name
        $timestamp = $row['Timestamp'];
        $name = $row['Name'];
        $ID = $row['Recipe_ID'];
        $recipe = $row['Recipe'];
        // Write Recipe ID each of the selected lines with reset flag = 1
        $update_sql = "UPDATE Data SET Recipe_ID = '".$recipe_id."' WHERE Timestamp = '".$timestamp."' AND Name = '".$name."';";
        write_log($update_sql);
        $update = mysqli_query($conn, $update_sql) or die(mysqli_error($conn));

        // set default value for calibration constants
        $const0=NULL;
        $const1=NULL;
        $const2=NULL;
        $const3=NULL;

        // get current constants for spindle from current sql row
        $valCalib = getSpindleCalibration($conn, $name );

        // assign constants from calibration table, if available
        if ($valCalib[0])
        {
            $const0=$valCalib[1];
            $const1=$valCalib[2];
            $const2=$valCalib[3];
            $const3=$valCalib[4];
        }
        // Add entr to Archive table for corrent sql row with corresponding Recipe_ID, Spindle Name, Recipe Name and calibratrion constants
        $entry_recipe_table_sql = "INSERT INTO `Archive` 
                                 (`Recipe_ID`, `Name`, `ID`, `Recipe`, `Start_date`, `End_date`, `const0`, `const1`, `const2`, `const3`) 
                                 VALUES (NULL, '".$name."', '".$ID."', '".$recipe."', '".$timestamp."', NULL, '".$const0."', '".$const1."', '".$const2."', '".$const3."')";
        write_log($entry_recipe_table_sql);
        $entry_result = mysqli_query($conn, $entry_recipe_table_sql) or die(mysqli_error($conn));
        // increase recipe_id number for next entry
        $recipe_id++;
        }
    //now select all entries with resetflag again
    $q_sql = "Select Timestamp,Name,Recipe_ID FROM Data WHERE ResetFlag = '1' ORDER BY Timestamp ASC";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
    //and work on these entries one by one
    while ($row = mysqli_fetch_array($result)){
        // receive start timestamp, Spindle Name and Spindle ID for each row with resetflag = 1
        $Timestamp = $row['Timestamp'];
        $Name = $row['Name'];
        $Recipe_ID = $row['Recipe_ID'];
        // select the current entry and the next for this particular Spindle with a reset 
        $timestamp_sql="SELECT Timestamp,Recipe FROM Data WHERE Name= '".$Name."' AND Timestamp >= '$Timestamp' AND ResetFlag = '1' ORDER BY Timestamp ASC limit 2";
        $timestamp_result = mysqli_query($conn, $timestamp_sql) or die(mysqli_error($conn));
        $timestamp_rows = mysqli_num_rows($timestamp_result);
        $timestamp_array = mysqli_fetch_array($timestamp_result);
        //define start time to write Recipe_ID
        $timestamp_1 = $timestamp_array[0];
        $recipe = $timestamp_array[1];
        //define end time for Recipe_ID if not last entry in database for this spindle
        if ($timestamp_rows == 2 ){
            $timestamp_array = mysqli_fetch_array($timestamp_result);
            $timestamp_2 = $timestamp_array[0];
        }
        // if no further reset flag available for this spindle, use current time
        else {
            $timestamp_2 = date("Y-m-d H:i:s");
        }
        // Update column  recipe_id for selected dataset and assign recipe_id for all entries between start time and end time
        $rolloutID_SQL = "UPDATE Data Set Recipe_ID = '".$Recipe_ID."' WHERE NAME = '".$Name."' AND Timestamp BETWEEN '".$timestamp_1."' AND '".$timestamp_2."'";
        $rolloutID_result = mysqli_query($conn, $rolloutID_SQL) or die(mysqli_error($conn));
        // Update archive table with end time of the current fermentation
        $update_archive_table = "UPDATE Archive Set End_date = '".$timestamp_2."' WHERE Recipe_ID = '".$Recipe_ID."'";
        $update_archive_result = mysqli_query($conn, $update_archive_table) or die(mysqli_error($conn));

    }
        echo "Table modified";
    }
// set max php execution time back to standard
    set_time_limit($max_time);

}

// function to upgrade strings table in case it was modified for a new version
function upgrade_strings_table($conn)
{
    $upgrade                    = false;
// latest table version is maintained in tables.php
    $file_version               = LATEST_STRINGS_TABLE;
// filename is provided in the iSpindle-Srv directory (e.g. Strings_008 for version 008 of table)
    $file_name                  = "../Strings_".$file_version.".sql";

// check version of existig strings table in database
    $q_sql="Select Field from Strings WHERE File = 'Version'";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn)); 
    $rows = mysqli_num_rows($result);
    if($rows > 0) {
        $row = mysqli_fetch_array($result);
        $value = $row[0];
// check if latest version is already installed
        if (intval($value) == intval($file_version)) {
            echo 'Latest Version installed:'.intval($value);
            exit;
        }
// if installed version is below new version, new table can be installed
        if (($value == '') || (intval($value) < intval($file_version))){
            $upgrade = true;
        }
    }
    else {
        // No Parameter in Database -> upgrade to newer version. Only older versions do have no version informatrion
        $upgrade = true;
    }
    if ($upgrade == true){
// call function to import table 'Strings' 
    import_table($conn,'Strings',$file_name);
    }
}

// function to upgrade settings table in case it was modified for a new version
function upgrade_settings_table($conn)
{
    $upgrade                    = false;
// latest table version is maintained in tables.php
    $file_version               = LATEST_SETTINGS_TABLE;
// filename is provided in the iSpindle-Srv directory (e.g. Settings_003 for version 003 of table)
    $file_name                  = "../Settings_".$file_version.".sql";

// check version of existig strings table in database
    $q_sql="Select value from Settings WHERE Section = 'VERSION'";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
    $rows = mysqli_num_rows($result);
    if($rows > 0) {
// if installed table has version
        $row = mysqli_fetch_array($result);
        $value = $row[0];
// no update if installe dversion matches version in directory
        if (intval($value) == intval($file_version)) {
            echo 'Latest Version installed:'.intval($value);
            exit;
        }
//if installed version is below new version, new table can be installed
        if (($value == '') || (intval($value) < intval($file_version))){
            $upgrade = true;
        }
    }
    else {
        // No Parameter in Database -> upgrade to newer version. Only older versions do have no version informatrion
        $upgrade = true;
    }
    if ($upgrade == true){
// call function to import table 'Settings'
    import_table($conn,'Settings',$file_name);
    }
}

// reset settings table and revert back to DEFAULT Settings
function reset_settings_table($conn)
{
// check version of existig strings table in database
    $q_sql="UPDATE Settings SET value = DEFAULT_value";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
}

// check database tables on start of index script
function check_database($conn)
{
	// Test if constants in calibration table have default values
        $database=DB_NAME;
	$q_sql = "Select COLUMN_DEFAULT
   		  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA='$database' and TABLE_NAME='Calibration' and COLUMN_NAME='const0'";
		$result = mysqli_query($conn, $q_sql);
	        $row = mysqli_fetch_array($result);
		if ($row[0] == "") {
			$change_sql="ALTER TABLE `Calibration` 
				     CHANGE `const0` `const0` DOUBLE NOT NULL DEFAULT '0', 
				     CHANGE `const1` `const1` DOUBLE NOT NULL DEFAULT '0', 
                                     CHANGE `const2` `const2` DOUBLE NOT NULL DEFAULT '0', 
                                     CHANGE `const3` `const3` DOUBLE NOT NULL DEFAULT '0';";
                        $result = mysqli_query($conn, $change_sql) or die(mysqli_error($conn)); 
	}	
}
	
// function to get selected css layout for webpages
function get_color_scheme($conn)
{
    $colorscheme_query = "Select Parameter FROM Settings WHERE Parameter LIKE 'COLORSCHEME_%' AND Value = '1'";
    $result = mysqli_query($conn, $colorscheme_query) or die(mysqli_error($conn));
    $row = mysqli_fetch_array($result);
//    write_log("Row from colorscheme_query: ");
//    write_log($row);
    $colorscheme=$row[0];
//    write_log("Colorscheme: ".$colorscheme);
// colorscheme looks like COLORSCHEME_color
// only 'color' is required for css layout
    if($colorscheme != null){    
        $color=substr_replace($colorscheme,'',0,12);
//        write_log("Color: ".$color);
        return $color; 
    }
// if colorscheme is not set, fall back to blue scheme as default
    else {
        return 'blue';
    }
}

// RID_END flag is used for display of archive data. If flag is set, archive data will only be displyed to this flag
// Data behind the flag won't be shown for this particular archive
// This function removes the flag for the selected archive
function delete_rid_flag_from_archive($conn,$selected_recipe)
{
    $delete_query = "UPDATE Data Set Internal = NULL WHERE Recipe_ID = '$selected_recipe' AND Internal = 'RID_END'";
    write_log("SELECT to delete RID_END flag:" . $delete_query);
    $result = mysqli_query($conn, $delete_query) or die(mysqli_error($conn));
}

// Data for selected recipe will be deleted from archive and data table
// This cannot be undone
function  delete_recipe_from_archive($conn,$selected_recipe)
{
    $delete_query1 = "DELETE FROM Archive WHERE Recipe_ID = '$selected_recipe'";
    write_log("SELECT to delete recipe from archive table" . $delete_query1);
    $result = mysqli_query($conn, $delete_query1) or die(mysqli_error($conn));
    $delete_query2 = "DELETE FROM Data WHERE Recipe_ID = '$selected_recipe'";
    write_log("SELECT to delete recipe from data table" . $delete_query2);
    $result = mysqli_query($conn, $delete_query2) or die(mysqli_error($conn));
}

// Function to export the data and archive table as backup
// Function is called from settings script and filename includes current date
// sql file can be imported at a later point of time
function export_data_table($table,$file="iSpindle_Backup.sql")
{
//Relevant info for sql connection is retrieved from definitionsin the php config file
    $user               = DB_USER;
    $pass               = DB_PASSWORD;
    $host               = DB_SERVER; 
    $name               = DB_NAME;
    $port               = DB_PORT;
    $backup_name        = $file;
// $table can be an array of tabes or a single table
    if(count($table) == 1){
    $tables              = array($table,"none");
    }  
    else {
    $tables             = $table;
    }

// connect to database and set utf8 for special character handling
    {
        $mysqli = new mysqli($host,$user,$pass,$name,$port); 
        $mysqli->select_db($name); 
        $mysqli->query("SET NAMES 'utf8'");

// query the existing tables in the database
        $queryTables    = $mysqli->query('SHOW TABLES'); 
        while($row = $queryTables->fetch_row()) 
        { 
            $target_tables[] = $row[0]; 
        }   
        if($tables !== false) 
        { 
// compare existing tables with tables to be exported and return match of both arrays
            $target_tables = array_intersect( $target_tables, $tables); 
        }
// start export for each individual table
        foreach($target_tables as $table)
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table);  
            $fields_amount  =   $result->field_count;  
            $rows_num=$mysqli->affected_rows;     
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
            {
                while($row = $result->fetch_row())  
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)  
                    { 
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ; 
                        }
                        else 
                        {   
                            $content .= '""';
                        }     
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }      
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                    {   
                        $content .= ";";
                    } 
                    else 
                    {
                        $content .= ",";
                    } 
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
        $backup_name = $backup_name ? $backup_name : $name.".sql";
        header('Content-Type: application/octet-stream');   
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"".$backup_name."\"");  
        echo $content; exit;
    }
}

// function to import settings and strings tables
function import_table($conn,$table,$filename)
{
// older version did not export Calibration table
// needs to be checked that calibration is in sql file
// otherwise table should not be dropped
$pos = strpos($table,", Calibration");

if ($pos !== false) {
if (strpos(file_get_contents($filename),"CREATE TABLE `Calibration` (") != FALSE)
    {
    write_log("Calibration Table included in uploaded file"); 
    }
else {
    $table = str_replace(', Calibration','',$table);
    write_log($table);
}
}

// Drop tables first
$drop_table="DROP TABLE IF EXISTS ".$table;
$result = mysqli_query($conn, $drop_table) or die(mysqli_error($conn));

// use this mode to prevent errors
$auto_increment="SET sql_mode='NO_AUTO_VALUE_ON_ZERO'";
$result = mysqli_query($conn, $auto_increment) or die(mysqli_error($conn));

// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$lines = file($filename);
// Loop through each line
foreach ($lines as $line)
{
// Skip it if it's a comment
if (substr($line, 0, 2) == '--' || $line == '')
    continue;

// Add this line to the current segment
$templine .= $line;
// If it has a semicolon at the end, it's the end of the query
if (substr(trim($line), -1, 1) == ';')
{
    // Perform the query
    mysqli_query($conn,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
    // Reset temp variable to empty
    $templine = '';
}
}
 echo "Tables imported successfully";
}

// Function to Export individual settings as CSV file
// Global settings but also settings for individual spindles will be exported 
// Settings can be also imported at a later point of time
function export_settings($conn,$table,$filename)
{
// select all parameters expect table version and flags for sent emails
    $q_sql="Select Section, Parameter, value, DeviceName from $table WHERE Parameter NOT LIKE 'Sent%' AND Section NOT LIKE 'VERSION' ORDER by DeviceName";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
// prepare file creation
    $fp = fopen('php://output', 'w');
// store values as csv file
    if ($fp && $result) 
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Pragma: no-cache');
        header('Expires: 0');
        while ($row = $result->fetch_array(MYSQLI_NUM)) 
        {
            fputcsv($fp, array_values($row));
        }
    die;
    }   

}

// function to import settings from csv file
function import_settings($conn,$table,$filename)
{
$Devices='';
//check if settings talbe does exist
$settings_table_exists="SHOW TABLES LIKE '".$table."'";
$result = mysqli_query($conn, $settings_table_exists) or die(mysqli_error($conn));

// start and delete all individual settings
$delete_other_devices="DELETE FROM Settings WHERE DeviceName <> 'GLOBAL' AND DeviceName <> '_DEFAULT'";
$result = mysqli_query($conn, $delete_other_devices) or die(mysqli_error($conn));

// open csv file start with the import of GLOBAL and _DEFAULT Parameters
// if installed settings table is newer and has more parameters, they won't be changed or overwritten
$file = fopen($filename, "r");
$i=0;
    while (($column = fgetcsv($file, 10000, ",")) !== FALSE) 
        {
        if ($column[0]<>"")
            {
            if ($column[3]=='GLOBAL' || $column[3] == '_DEFAULT')
                {
                $column[2]= str_replace('\\', '\\\\', $column[2]);
                $sqlUpdate = "UPDATE $table SET value = '$column[2]' WHERE DeviceName = '$column[3]' AND Section = '$column[0]' AND Parameter = '$column[1]'";
                $result = mysqli_query($conn, $sqlUpdate) or die(mysqli_error($conn));
                }
// check line setting is for an individual device instead of GLOBAL or _DEFAULT
            if ($column[3] != 'GLOBAL' && $column[3] != '_DEFAULT')
                {
                if ($i==0)
                    {
// if it is the first individual device, create an array
                    $Devices=array($column[3]);
                    $i++;
                    }
                else 
                    {
// if this particular device name is not already in the array, add it to the array
                    if (!in_array($column[3],$Devices))
                        {
                        array_push($Devices,$column[3]);
                        }
                    }
                }
            }
        
        }
// if settings for individual devices are in the import file, move on
    if($Devices !='') {
// copy the _DEFAULT settings to each individual device from the array which was in the CSV file
        foreach($Devices as $Device)
        {
            CopySettingsToDevice($conn, $Device);
        } 
// now open the csv file again and import all settings for the individual devices but not for GLOBAL and _DEFAULT
        $file = fopen($filename, "r");
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE)
        { 
            if ($column[0]<>"")
            {
                if ($column[3] != 'GLOBAL' && $column[3] != '_DEFAULT')
                {
                    $sqlUpdate = "UPDATE $table SET value = '$column[2]' WHERE DeviceName = '$column[3]' AND Section = '$column[0]' AND Parameter = '$column[1]'";
                    $result = mysqli_query($conn, $sqlUpdate) or die(mysqli_error($conn));
                }
            }
        }
    }
 echo "Settings imported successfully";
}

// function to get labels for buttons, headers or other text for the web pages in the selected language
function get_field_from_sql($conn, $file, $field)
{
// set connection to utf-8 to display characters like umlauts correctly    
    mysqli_set_charset($conn, "utf8mb4");
// query to get language setting. e.g. DE for German or EN for english
    $sql_language = mysqli_query($conn, "SELECT value FROM Settings WHERE Section = 'GENERAL' AND Parameter = 'LANGUAGE'") or die(mysqli_error($conn));
    $LANGUAGE = mysqli_fetch_array($sql_language);
// choose corresponding description column for selected language: e.g. 'Decription_DE' for german.
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

// function to get settings from the settings table
function get_settings_from_sql($conn, $section, $device, $parameter)
{
// set connection to utf-8 to display characters like umlauts correctly
    mysqli_set_charset($conn, "utf8mb4");
    $q_sql = "SELECT value FROM Settings WHERE Section = '" . $section. "' and Parameter = '" . $parameter . "' and ( DeviceName = '_DEFAULT' or DeviceName = '" . $device . "' ) ORDER BY DeviceName DESC LIMIT 1;";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
    $rows = mysqli_num_rows($result);
    if($rows > 0) {
        $r_row = mysqli_fetch_array($result);
        $return_value = $r_row[0];
            }
        return $return_value;
}


// Function to write iSpindel Server settings back to sql database. Function is used by settings.php
function UpdateSettings($conn, $Section, $Device, $Parameter, $value)
{
// added to wite newline for csv file correctly to database    
    $value= str_replace('\\', '\\\\', $value);
    $q_sql = mysqli_query($conn, "UPDATE Settings SET value = '" . $value . "' WHERE Section = '" . $Section . "' AND Parameter = '" . $Parameter . "'" . " AND DeviceName = '" . $Device . "'") or die(mysqli_error($conn));
    return 1;
}

// function to copy current _DEFAULT settings to a device. Settings can be changed individually for the device afterwards
function CopySettingsToDevice($conn, $device)
{
    $sql_select="INSERT INTO Settings(Section,Parameter,value,DEFAULT_value,Description_DE,Description_EN,Description_IT,DeviceName) SELECT Section,Parameter,value,DEFAULT_value,Description_DE,Description_EN,Description_IT,'" . $device . "' FROM Settings WHERE DeviceName ='_DEFAULT'";

   $q_sql = mysqli_query($conn, $sql_select) or die(mysqli_error($conn));
    return 1;
}


// Retrieves timestamp of last dataset for corresponding Spindle. If timestamp is older than timeframehours, false will be returned
// Difference between last available data and selected timeframe is calculated and displayed in diagram to go more days back  
function isDataAvailable($conn, $iSpindleID, $Timeframehours)
{
    $q_sql = mysqli_query($conn, "SELECT MAX(UNIX_TIMESTAMP(Timestamp)) AS Timestamp FROM Data WHERE Name ='" . $iSpindleID . "'") or die(mysqli_error($conn));
// get current timestamp
    $now = time();
// calculate timestamp in tha past (timeframhoursago in seconds) 
    $startdate = $now - $Timeframehours * 3600;
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql);
        $valTimestamp = $r_row['Timestamp'];
// calculate difference between timstamp of last dataset and startdate
        $TimeDiff = $startdate - $valTimestamp;
// calculate days that need to go back to see last dataset (in case data is older than timframehoursago)
        $go_back = round(($TimeDiff / (3600 * 24)) + 0.5);
// if data is younger than timeframhoursago return true
        if ($TimeDiff < 0) {
            $DataAvailable = true;
        } 
// if data is older, return false
        else {
            $DataAvailable = false;
        }
    }
    return array($DataAvailable, $go_back);
}

// Used in calibration.php Values for corresponding SpindleID will be either updated (if already in database) or added to table calibration in SQL database
function setSpindleCalibration($conn, $ID, $Calibrated, $const0, $const1, $const2, $const3)
{
// if spindle is calibrated, fields only need to be updated. If not, we need to insert a new row to the calibration database
    if ($Calibrated) {
        $q_sql = mysqli_query($conn, "UPDATE Calibration SET const0 = '" . $const0 . "', const1 = '" . $const1 . "', const2 = '" . $const2 . "', const3 = '" . $const3 . "' WHERE ID = '" . $ID . "'") or die(mysqli_error($conn));
    } 
// if spindle is not yet calibrated, new dataset needs to be created in calibration table
    else {
        $q_sql = mysqli_query($conn, "INSERT INTO Calibration (ID, const0, const1, const2, const3) VALUES ('" . $ID . "', '" . $const0 . "', '" . $const1 . "', '" . $const2 . "', '" . $const3 . "')") or die(mysqli_error($conn));
    }
    return 1;
}

// Function retrieves 'latest' SpindleID for Spindelname if available. ID is used to query calibration table for existing calibration
// If data is available, parameters will be send to form (calibration.php). If not, Calibration_exists is false and empty values will be returned
function getSpindleCalibration($conn, $iSpindleID = 'iSpindel000')
{
// query ID for $iSpindleID
    $q_sql0 = mysqli_query($conn, "SELECT DISTINCT ID FROM Data WHERE Name = '" . $iSpindleID . "'AND (ID <>'' OR ID <>'0') ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
// exit if spindle has no ID
    if (!$q_sql0) {
        echo "Fehler beim Lesen der ID";
    }
// define variables
    $valID = '0';
    $Calibration_exists = false;
    $valconst0 = '';
    $valconst1 = '';
    $valconst2 = '';
    $valconst3 = '';
    $rows = mysqli_num_rows($q_sql0);
// if spindle has an ID move on
    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql0);
        $valID = $r_row['ID'];
// get current calibration values for spindle with ID from calibration table
        $q_sql1 = mysqli_query($conn, "SELECT const0, const1, const2, const3
                               FROM Calibration WHERE ID = " . $valID) or die(mysqli_error($conn));
        $rows1 = mysqli_num_rows($q_sql1);
        if ($rows1 > 0) {
            $Calibration_exists = true;
            $r_row = mysqli_fetch_array($q_sql1);
            $valconst0 = $r_row['const0'];
            $valconst1 = $r_row['const1'];
            $valconst2 = $r_row['const2'];
            $valconst3 = $r_row['const3'];
        }
    }
// return calibration constants
    return array(
        $Calibration_exists,
        $valconst0,
        $valconst1,
        $valconst2,
        $valconst3,
        $valID
    );
}

// get current interval for Spindel to derive number of rows for moving average calculation with sql windows functions
function getCurrentInterval($conn, $iSpindleID)
{
// get interval in seconds from data table for $iSpindleID from last data set. Has to be done as frequency as Interval is a function in sql
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
// set utf8mb4 to deal with special characters in recipe names
    mysqli_set_charset($conn, "utf8mb4");
// select to get timestamp of last reset
    $q_sql1 = mysqli_query($conn, "SELECT Data.Recipe, Data.Timestamp FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.Timestamp >= (SELECT max( Data.Timestamp )FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true) LIMIT 1") or die(mysqli_error($conn));

// select to get timestamp of $timeframehours.
    $q_sql2 = mysqli_query($conn, "SELECT Data.Timestamp FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)                                                                                                                                                                          AND Timestamp <= NOW() LIMIT 1") or die(mysqli_error($conn));

    $rows = mysqli_num_rows($q_sql1);


    if ($rows > 0) {
        $r_row = mysqli_fetch_array($q_sql1);
        $t_row = mysqli_fetch_array($q_sql2);
        $RecipeName = '';
        $showCurrentRecipe = false;
        $TimeFrame = $t_row['Timestamp'];
        $ResetTime = $r_row['Timestamp'];
// if function was called with reset = true, return recipe name
        if ($reset == true) {
            $RecipeName = $r_row['Recipe'];
            $showCurrentRecipe = true;
        } 
// if function was called with reset = false, it is checked if timeframe is shorter than reset timframe. In this case, recipe name is returned
// if not, empty recipe name is returned as longer timeframe would result in two recipes (before and after reset)
        else {
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

function getCurrentRecipeName_iGauge($conn, $iSpindleID = 'iGauge000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    $q_sql1 = mysqli_query($conn, "SELECT iGauge.Recipe, iGauge.Timestamp FROM iGauge WHERE iGauge.Name = '" . $iSpindleID . "' AND iGauge.Timestamp >= (SELECT max( iGauge.Timestamp )FROM iGauge WHERE iGauge.Name = '" . $iSpindleID . "' AND iGauge.ResetFlag = true) LIMIT 1") or die(mysqli_error($conn));


    $q_sql2 = mysqli_query($conn, "SELECT iGauge.Timestamp FROM iGauge WHERE iGauge.Name = '" . $iSpindleID . "' AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)                                                                                                                                                                          AND Timestamp <= NOW() LIMIT 1") or die(mysqli_error($conn));

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

//Returns name of Recipe for current fermentation - Name can be set with reset.
function getCurrentRecipeName_ids2($conn, $iSpindleID = 'IDS000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    $q_sql1 = mysqli_query($conn, "SELECT heizen.Recipe, heizen.Timestamp FROM heizen WHERE heizen.Name = '" . $iSpindleID . "' AND heizen.Timestamp >= (SELECT max( heizen.Timestamp )FROM heizen WHERE heizen.Name = '" . $iSpindleID . "' AND heizen.ResetFlag = true) LIMIT 1") or die(mysqli_error($conn));


    $q_sql2 = mysqli_query($conn, "SELECT heizen.Timestamp FROM heizen WHERE heizen.Name = '" . $iSpindleID . "' AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)                                                                                                                                                                          AND Timestamp <= NOW() LIMIT 1") or die(mysqli_error($conn));

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

// Calaculate initial gravity from database for archive. First hour after last reset will be used for calculation.
// This can be used to calculate apparent attenuation
function getArchiveInitialGravity($conn, $recipe_id)
{
// define variables and they should not be empty
    $isCalibrated = 0;
    $valAngle = '';
    $valDens = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
// where clause for sql select to define timeframe for given recipe_id of one hour after reset ('windows functions' in mysql are required for select)
    $where = "WHERE Recipe_ID = $recipe_id AND Timestamp > (Select MAX(Data.Timestamp) FROM Data WHERE Data.ResetFlag = true AND Recipe_id = $recipe_id) 
              AND Timestamp < DATE_ADD((SELECT MAX(Data.Timestamp)FROM Data WHERE Recipe_ID = $recipe_id AND Data.ResetFlag = true), INTERVAL 1 HOUR)";
// query to calculate average angle for this recipe_id and timeframe
    $q_sql = mysqli_query($conn, "SELECT AVG(Data.Angle) as angle FROM Data " . $where ) or die(mysqli_error($conn));

    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // try to get calibration for recipe_id from archive
        $cal_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Archive WHERE Recipe_ID = $recipe_id") or die(mysqli_error($conn));
        $rows_cal = mysqli_num_rows($cal_sql);
        if ($rows_cal > 0) {
            $isCalibrated = 1;
            $r_cal = mysqli_fetch_array($cal_sql);
            $const0 = $r_cal['const0'];            
            $const1 = $r_cal['const1'];
            $const2 = $r_cal['const2'];
            $const3 = $r_cal['const3'];
        }
    }
    $r_row = mysqli_fetch_array($q_sql);
    $angle = $r_row['angle']; // average angle
// calculate gravity
    $dens = round(($const0 * pow($angle, 3) + $const1 * pow($angle, 2) + $const2 * $angle + $const3),2); // complete polynome from database
    return array(
        $isCalibrated,
        $dens,
        $const0,
        $const1,
        $const2,
        $const3
    );
}

// Calaculate final gravity from database for archive. last hour will be used.
// This can be used to calculate apparent attenuation
function getArchiveFinalGravity($conn, $recipe_id, $end_date)
{
// define variables and they should not be empty
    $isCalibrated = 0;
    $valAngle = '';
    $valDens = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;

// where clause for sql select to define timeframe for given recipe_id of last hour before end_date ('windows functions' in mysql are required for select)
    $where = "WHERE Recipe_id = $recipe_id and Timestamp < '$end_date' and Recipe_id = $recipe_id AND Timestamp > DATE_SUB('$end_date', INTERVAL 1 HOUR)";

// query to calculate average angle for this recipe_id and timeframe
    $q_sql = mysqli_query($conn, "SELECT AVG(Data.Angle) as angle FROM Data " . $where ) or die(mysqli_error($conn));

    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // try to get calibration for recipe_id from archive
        $cal_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Archive WHERE Recipe_ID = $recipe_id") or die(mysqli_error($conn));
        $rows_cal = mysqli_num_rows($cal_sql);
        if ($rows_cal > 0) {
            $isCalibrated = 1;
            $r_cal = mysqli_fetch_array($cal_sql);
            $const0 = $r_cal['const0'];
            $const1 = $r_cal['const1'];
            $const2 = $r_cal['const2'];
            $const3 = $r_cal['const3'];
        }
    }
    $r_row = mysqli_fetch_array($q_sql);
    $angle = $r_row['angle']; //average angle
// calculate gravity
    $dens = round(($const0 * pow($angle, 3) + $const1 * pow($angle, 2) + $const2 * $angle + $const3),2); // complete polynome from database
    return array(
        $isCalibrated,
        $dens
    );
}


// Calaculate initial gravity from database after last reset. First two hours after last reset will be used. 
// This can be used to calculate apparent attenuation in svg_ma.php
function getInitialGravity($conn, $iSpindleID = 'iSpindel000')
{
// define variables and they should not be empty
    $isCalibrated = 0;
    $valAngle = '';
    $valDens = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
// where clause for sql select to define timeframe for the Spindle of one hour after last reset ('windows functions' in mysql are required for select)
    $where = "WHERE Name = '" . $iSpindleID . "'
              AND Timestamp > (Select MAX(Data.Timestamp) FROM Data  WHERE Data.ResetFlag = true AND Data.Name = '" . $iSpindleID . "') 
              AND Timestamp < DATE_ADD((SELECT MAX(Data.Timestamp)FROM Data WHERE Data.Name = '" . $iSpindleID . "' 
              AND Data.ResetFlag = true), INTERVAL 1 HOUR)";

// query to calculate average angle for this recipe_id and timeframe
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
            $f_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const0 = $r_cal['const0'];
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
        $r_row = mysqli_fetch_array($q_sql);
            $angle = $r_row['angle']; //average angle
// calculate gravity
            $dens = round(($const0 * pow($angle, 3) + $const1 * pow($angle, 2) + $const2 * $angle + $const3),2); // complete polynome from database
        return array(
            $isCalibrated,
            $dens
        );
    }
}

// Check if alarm mail has been sent for $alarm and $iSpindleID
function check_mail_sent($conn, $alarm, $iSpindel)
{
        $sqlselect = "Select value from Settings where Section ='EMAIL' and Parameter = '" . $alarm . "' AND value = '" . $iSpindel . "' ;";
        $q_sql = mysqli_query($conn, $sqlselect) or die(mysqli_error($conn));
// if flag is not in settings table for $alarm and $iSpindleID return 0
        if (! $q_sql)
	    {
            return 0;
            } 
// if flag is in settings table for $alarm and $iSpindleID return 1
        else
            {
            return 1;
            }
}

// delete flag for sent email with corresponding alarm ($alarm)
function delete_mail_sent($conn, $alarm, $iSpindel)
{
        $sqlselect = "DELETE FROM Settings where Section ='EMAIL' and Parameter = '" . $alarm . "' AND value = '" . $iSpindel . "' ;";
        $q_sql = mysqli_query($conn, $sqlselect) or die(mysqli_error($conn));
        if (! $q_sql)
            {
            return 0;
            }
        else
            {
            return 1;
            }
}

// Export values from database for selected recipe_id from Archive
// Parameters that are available in the script that is calling this function will be submitted while calling this function
function ExportArchiveValues($conn, $recipe_ID, $txt_recipe_name, $txt_end, $txt_initial_gravity, $initial_gravity, $txt_final_gravity, $final_gravity, $txt_attenuation, $attenuation, $txt_alcohol, $alcohol, $txt_calibration, $csv_type)
{
// define empty variables
    $valAngle = '';
    $valTemperature = '';
    $valDens = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    $AND_RID = '';
// get all information for selected recipe_id from archive table
    $archive_sql = "Select * FROM Archive WHERE Recipe_ID = '$recipe_ID'";
// set connection to utf8mb4 for special characters (e.g. in recipe name)
    mysqli_set_charset($conn, "utf8mb4");
    $result = mysqli_query($conn, $archive_sql) or die(mysqli_error($conn));
    $archive_result = mysqli_fetch_array($result);
    $spindle_name = $archive_result['Name'];
    $recipe_name = $archive_result['Recipe'];
    $start_date = $archive_result['Start_date'];
    $end_date = $archive_result['End_date'];
    $const0 = $archive_result['const0'];
    $const1 = $archive_result['const1'];
    $const2 = $archive_result['const2'];
    $const3 = $archive_result['const3'];
// convert initial gravity to float
    $sql_IG=floatval($initial_gravity);

// get current interval in second the spindle is sending data
   $Interval = (getCurrentInterval($conn, $spindle_name));
// Calculate distance between datasets in rows that are required to have $filtering minutes intervals in the select
// Used for beersmith/kbh export
    $filtering = 240;
    $Rows = round( $filtering / ($Interval / 60));

// if no entry for end date in archive table, get last timestamp of last dataset for selected recipe from data table
    if($end_date == NULL){
        $get_end_date = "SELECT max(Timestamp) FROM Data WHERE Recipe_ID = '$recipe_ID'";
        $q_sql = mysqli_query($conn, $get_end_date) or die(mysqli_error($conn));
        $result = mysqli_fetch_array($q_sql);
    // update end_dat in case of existing RID_END flag
        $end_date = $result[0];
    }
// check, if RID_END flag is set for selected recipe_ID. If so, data will be exported only to this flag but not for points with a timestamp after this flag
    $check_RID_END = "SELECT * FROM Data WHERE Recipe_ID = '$recipe_ID' AND Internal = 'RID_END'";
    $q_sql = mysqli_query($conn, $check_RID_END) or die(mysqli_error($conn));
    $rows = mysqli_fetch_array($q_sql);
    if ($rows <> 0)
    {
    $end_date = $rows['Timestamp'];
// add condition to select if RID_END flag is set
    $AND_RID = " AND Timestamp <= (Select max(Timestamp) FROM Data WHERE Recipe_ID='$recipe_ID' AND Internal = 'RID_END')";
    }
    // if regular csv export is selected
    if ($csv_type == "csv1") {
    // select that calculates all parameters to be exported while pulling data from the database
        $q_sql = mysqli_query($conn, "SELECT Timestamp, Name, ID, Angle, Temperature, Battery, Gravity AS Spindle_Gravity, ($const0*Angle*Angle*Angle + $const1*Angle*Angle + $const2*Angle + $const3) AS Calculated_Gravity, 
                                      (($sql_IG-($const0*Angle*Angle*Angle + $const1*Angle*Angle + $const2*Angle + $const3))*100 / $sql_IG) AS Attenuation, RSSI, Recipe, Comment
                                      FROM Data WHERE Recipe_ID = '$recipe_ID'" . $AND_RID . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
    // filename for download
        $filename = $recipe_ID . "_" . date_format(date_create($start_date),'Y_m_d') ."_" . $spindle_name . "_" . $recipe_name . ".txt";
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        ob_clean();
        $flag = false;
    // define fromat for date
        $start_date=date_format(date_create($start_date),'Y-m-d');
        $end_date=date_format(date_create($end_date),'Y-m-d');
    // write summary header to file
        echo "Device: $spindle_name | $txt_recipe_name $recipe_name | Start: $start_date | $txt_end : $end_date \r\n";
        echo "$txt_initial_gravity : $initial_gravity °P | $txt_final_gravity : $final_gravity °P | $txt_attenuation : $attenuation % | $txt_alcohol : $alcohol Vol% \r\n";
        printf("$txt_calibration :  %01.5f * tilt^3 %+01.5f * tilt^2 %+01.5f * tilt %+01.5f \r\n",$const0,$const1,$const2,$const3);
        echo "\r\n";
        // retrieve and store the values comma separated
        while ($row = mysqli_fetch_assoc($q_sql)) {
            if(!$flag) {
                // display field/column names as first row
                echo implode(",", array_keys($row)) . "\r\n";
                $flag = true;
            }
        // starting with the second row, data values will be written to file
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode(",", array_values($row)) . "\r\n";
        }
     exit;
     }
    //  if beersmith csv formt is selected
    if ($csv_type == "csv2") {
    // start query and set @x=0
         $p_sql = mysqli_query($conn, "SET @x:=0") or die(mysqli_error($conn));
    // select that calculates all parameters for beersmith fermentation csv file to be exported while pulling data from the database
        $SQL_select = "SELECT *
                        FROM (SELECT mt.Timestamp AS Date, mt.Temperature, ($const0*mt.Angle*mt.Angle*mt.Angle + $const1*mt.Angle*mt.Angle + $const2*mt.Angle + $const3) AS Gravity, TIMESTAMPDIFF(DAY, '$start_date', mt.Timestamp) AS Day, (@x:=@x+1) AS x
                        FROM Data mt WHERE Recipe_ID = '$recipe_ID' AND Timestamp > (Select min(Timestamp) FROM Data WHERE Recipe_ID='$recipe_ID')" . $AND_RID . " ORDER BY Timestamp ASC) t WHERE x MOD $Rows = 0";
        $q_sql = mysqli_query($conn, $SQL_select) or die(mysqli_error($conn));
    // filename for download
        $filename = $recipe_ID . "_" . date_format(date_create($start_date),'Y_m_d') ."_" . $spindle_name . "_" . $recipe_name . ".csv";
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        ob_clean();
        $fh = fopen( 'php://output', 'w' );
        $flag = false;
        // retrieve and store the values comma separated
        while ($row = mysqli_fetch_assoc($q_sql)) {
            if(!$flag) {
                // display field/column names as first row
                fputcsv($fh, array_keys($row));
                $flag = true;
            }
        // starting with the second row, data values will be written to file
            array_walk($row, __NAMESPACE__ . '\cleanData');
            fputcsv($fh, array_values($row)); 
        }
    exit;
    }
    // if kbh csv format is selected
    if ($csv_type == "csv3") {
    // start query and set @x=0
         $p_sql = mysqli_query($conn, "SET @x:=0") or die(mysqli_error($conn));
    // select that calculates all parameters for beersmith fermentation csv file to be exported while pulling data from the database
        $SQL_select = "SELECT *
                        FROM (SELECT mt.Timestamp AS Date, ($const0*mt.Angle*mt.Angle*mt.Angle + $const1*mt.Angle*mt.Angle + $const2*mt.Angle + $const3) AS Gravity,mt.Temperature, mt.Comment, (@x:=@x+1) AS x
                        FROM Data mt WHERE Recipe_ID = '$recipe_ID' AND Timestamp > (Select min(Timestamp) FROM Data WHERE Recipe_ID='$recipe_ID')" . $AND_RID . " ORDER BY Timestamp ASC) t WHERE x MOD $Rows = 0";
        $q_sql = mysqli_query($conn, $SQL_select) or die(mysqli_error($conn));
    // filename for download
        $filename = $recipe_ID . "_" . date_format(date_create($start_date),'Y_m_d') ."_" . $spindle_name . "_" . $recipe_name . ".txt";
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        ob_clean();
        $fh = fopen( 'php://output', 'w' );
        $flag = false;
        // retrieve and store the values comma separated
        while ($row = mysqli_fetch_assoc($q_sql)) {
        // starting with the second row, data values will be written to file
            array_walk($row, __NAMESPACE__ . '\cleanData');
            $array=array_values($row);
            fputs($fh, implode(';', $array)."\n");
        }
    exit;
    }

}

// Get archive values from database for selected recipe_ID. 
// Parameters that are available in the script that is calling this function will be submitted while calling this function
function getArchiveValues($conn, $recipe_ID, $initial_gravity)
{
// define empty variables
    $valAngle = '';
    $valTemperature = '';
    $valDens = '';
    $valGravity = '';
    $valRSSI = '';
    $valBattery = '';
    $valSVG ='';
    $valABV ='';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    $AND_RID = ''; 
// conveqli_errort initial gravity to float
    $sql_IG=floatval($initial_gravity);
// get all information for selected recipe_id from archive table
    $archive_sql = "Select * FROM Archive WHERE Recipe_ID = '$recipe_ID'";
// set connection to utf8mb4 for special characters (e.g. in recipe name)
    mysqli_set_charset($conn, "utf8mb4");
    $result = mysqli_query($conn, $archive_sql) or die(mysqli_error($conn));
    $archive_result = mysqli_fetch_array($result);
    $spindle_name = $archive_result['Name'];
    $recipe_name = $archive_result['Recipe'];
    $start_date = $archive_result['Start_date'];
    $end_date = $archive_result['End_date'];
    $const0 = $archive_result['const0'];
    $const1 = $archive_result['const1'];
    $const2 = $archive_result['const2'];
    $const3 = $archive_result['const3'];
    write_log($end_date);
// if no entry for end date in archive table, get last timestamp of last dataset for selected recipe from data table
    if($end_date == NULL){
    $get_end_date = "SELECT max(Timestamp) FROM Data WHERE Recipe_ID = '$recipe_ID'";
    $q_sql = mysqli_query($conn, $get_end_date) or die(mysqli_error($conn));
    $result = mysqli_fetch_array($q_sql);
    $end_date = $result[0];
    }

// check, if RID_END flag is set for selected recipe_ID. If so, data will be exported only to this flag but not for points with a timestamp after this flag
    $check_RID_END = "SELECT * FROM Data WHERE Recipe_ID = '$recipe_ID' AND Internal = 'RID_END'";
    $q_sql = mysqli_query($conn, $check_RID_END) or die(mysqli_error($conn));
    $rows = mysqli_fetch_array($q_sql);
    if ($rows <> 0)    
    {
// update end_date in case of existing RID_END flag
    $end_date = $rows['Timestamp'];
// add condition to select if RID_END flag is set
    $AND_RID = " AND Timestamp <= (Select max(Timestamp) FROM Data WHERE Recipe_ID='$recipe_ID' AND Internal = 'RID_END')";
    }

// select that pulls all parameters to be displayed in the archive.php diagram types
    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, gravity, battery, rssi, recipe, comment
                           FROM Data WHERE Recipe_ID = '$recipe_ID'" . $AND_RID . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
// variable to help positioning comments as label above or below the data line (alternating)
	$label_position = 1;
        // retrieve the values as CSV arrays for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
// convert time to unixtime in milliseconds for JavaScript
            $jsTime = $r_row['unixtime'] * 1000;
            $angle = $r_row['angle'];
// calculate current density based on calibration values
            $dens = $const0 * pow($angle, 3) + $const1 * pow($angle, 2) + $const2 * $angle + $const3; // complete polynome from database
// calculate current attenuation based on initial gravity and current gravity
            $SVG = ($initial_gravity-$dens)/$initial_gravity*100;
// calculate real gravity based on initial gravity and current gravity (required for ABV calculation)
            $real_dens = 0.1808 * $initial_gravity + 0.8192 * $dens;
// calculate alcohol by weigth and by volume (fabbier calcfabbier calc for link see above)
            $alcohol_by_weight = ( 100 * ($real_dens - $initial_gravity) / (1.0665 * $initial_gravity - 206.65));
            $alcohol_by_volume = ($alcohol_by_weight / 0.795);

            $gravity = $r_row['gravity'];
            $rssi = $r_row['rssi'];
            $battery = $r_row['battery'];
// if comment is available for current datapoint, add it to the array
            if ($r_row['comment']){
// if label position is positive, add it with text_up as variable to place it above data line
                if($label_position == 1){
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valBattery .= '{ timestamp: ' . $jsTime . ', value: ' . $battery . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valSVG .= '{ timestamp: ' . $jsTime . ', value: ' . $SVG . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
// change lable position flag for next comment to negative value 
                    $label_position = $label_position * -1;
                }
// if label position is negative, add it with text_down as variable to place it below data line
                else{
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valBattery .= '{ timestamp: ' . $jsTime . ', value: ' . $battery . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valSVG .= '{ timestamp: ' . $jsTime . ', value: ' . $SVG . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
// change lable position flag for next comment to positive value
                    $label_position = $label_position * -1;
                }
  
            } 
// if comment is not available for current datapoint, add datapoint w/o comment to array -> no empty comment is displayed
            else{
            $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valBattery .= '{ timestamp: ' . $jsTime . ', value: ' . $battery . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valSVG .= '{ timestamp: ' . $jsTime . ', value: ' . $SVG . ", recipe: \"" . $r_row['recipe'] . "\"},";
            }
// arrays where no comment is displayed at all
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valRSSI .= '{ timestamp: ' . $jsTime . ', value: ' . $rssi . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valABV .= '{ timestamp: ' . $jsTime . ', value: ' . $alcohol_by_volume . ", recipe: \"" . $r_row['recipe'] . "\"},";


        }
        return array(
            $spindle_name,
            $recipe_name,
            $start_date,
            $end_date,
            $valDens,
            $valTemperature,
            $valAngle,
            $valGravity,
            $valBattery,
            $valRSSI,
            $valSVG,
            $valABV
        );
  
}


// Get values from database for selected spindle. Used for all trend charts
// If reset is true, data until last reset is pulled
// otherwise specified timeframe is pulled from database
function getChartValues($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
// define empty variables 
    $isCalibrated = 0;
    $valAngle = '';
    $valTemperature = '';
    $valDens = '';
    $valGravity = '';
    $valRSSI = '';
    $valBattery = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;

// define WHERE condition dependent on reset flag 
    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) 
            AND Timestamp <= NOW()";
    }

// set connection to utf8mb4 for special characters (e.g. in recipe name)
    mysqli_set_charset($conn, "utf8mb4");

// sql query to pull all relevant data from data table for trend charts
    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, recipe, battery, rssi, gravity, comment
                           FROM Data " . $where . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
    
// retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // get unique hardware ID for calibration data
        $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
        $rowsID = mysqli_num_rows($u_sql);
        if ($rowsID > 0) {

// try to get calibration for iSpindle hardware ID
            $r_id = mysqli_fetch_array($u_sql);
            $uniqueID = $r_id['ID'];
            $f_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const0 = $r_cal['const0'];
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }

// variable to help positioning comments as label above or below the data line (alternating)
        $label_position = 1;

// retrieve the values as CSV arrays for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {

// convert time to unixtime in milliseconds for JavaScript
            $jsTime = $r_row['unixtime'] * 1000;
            $angle = $r_row['angle'];

// calculate current density based on calibration values
            $dens = $const0 * pow($angle, 3) + $const1 * pow($angle, 2) + $const2 * $angle + $const3; // complete polynome from database
            $gravity = $r_row['gravity']; // desity values from spindle
            $rssi = $r_row['rssi'];
            $battery = $r_row['battery'];

// if comment is available for current datapoint, add it to the array
            if ($r_row['comment']){
// if label position is positive, add it with text_up as variable to place it above data line
                if($label_position == 1){
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valBattery .= '{ timestamp: ' . $jsTime . ', value: ' . $battery . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
// change label position flag for next comment to negative value
                    $label_position = $label_position * -1;
                }
// if label position is negative, add it with text_down as variable to place it below data line
                else{
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valBattery .= '{ timestamp: ' . $jsTime . ', value: ' . $battery . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
// change label position flag for next comment to positive value
                    $label_position = $label_position * -1;
                }

            }
// if comment is not available for current datapoint, add datapoint w/o comment to array -> no empty comment is displayed
            else{
            $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valBattery .= '{ timestamp: ' . $jsTime . ', value: ' . $battery . ", recipe: \"" . $r_row['recipe'] . "\"},";
            }
// arrays where no comment is displayed at all
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valRSSI .= '{ timestamp: ' . $jsTime . ', value: ' . $rssi . ", recipe: \"" . $r_row['recipe'] . "\"},";

        }
        return array(
            $isCalibrated,
            $valDens,
            $valTemperature,
            $valAngle,
            $valGravity,
            $valBattery,
            $valRSSI
        );
    }
}

function getChartValuesiGauge($conn, $iSpindleID = 'iGauge000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    $isCalibrated = 1; // is there a calbration record for this iSpindle?
    $valCarbondioxide = '';
    $valTemperature = '';
    $valpressure = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= (Select max(Timestamp) FROM iGauge  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) 
            AND Timestamp <= NOW()";
    }
	//$q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, Temperature, Pressure , Carbondioxid, recipe
                           //FROM iGauge " . $where . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
 	
	$q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, Temperature, Pressure , Carbondioxid, recipe
                           FROM iGauge " . $where . " ORDER BY Timestamp ASC");
    
    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // get unique hardware ID for calibration
        $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
        $rowsID = mysqli_num_rows($u_sql);
        // retrieve and store the values as CSV lists for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $carbondioxixde = $r_row['Carbondioxid'];
            $pressure = $r_row['Pressure']; 

            $valCarbondioxide .= '{ timestamp: ' . $jsTime . ', value: ' . $carbondioxixde . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valpressure .= '{ timestamp: ' . $jsTime . ', value: ' . $pressure . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['Temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";



        }
        return array(
            $isCalibrated,
            $valpressure,
            $valTemperature,
            $valCarbondioxide,
        );
    }
}

function getChartValuesids2($conn, $iSpindleID = 'IDS000', $timeFrameHours = defaultTimePeriod, $reset = defaultReset)
{
    $isCalibrated = 1; // is there a calbration record for this iSpindle?
    $valCarbondioxide = '';
    $valTemperature = '';
    $valpressure = '';
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= (Select max(Timestamp) FROM heizen  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "' 
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) 
            AND Timestamp <= NOW()";
    }

    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, Temperature, Stellgrad , Sollwert, Gradient,Restzeit, recipe
                           FROM heizen " . $where . " ORDER BY Timestamp ASC") or die(mysqli_error($conn));
    
    // retrieve number of rows
    $rows = mysqli_num_rows($q_sql);
    if ($rows > 0) {
        // get unique hardware ID for calibration
        $u_sql = mysqli_query($conn, "SELECT ID FROM Data WHERE Name = '" . $iSpindleID . "' ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));
        $rowsID = mysqli_num_rows($u_sql);
        // retrieve and store the values as CSV lists for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
            $jsTime = $r_row['unixtime'] * 1000;
            $gradient = $r_row['Gradient'];
            $Sollwert = $r_row['Sollwert']; 

            $valgradient .= '{ timestamp: ' . $jsTime . ', value: ' . $gradient . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valsollwert .= '{ timestamp: ' . $jsTime . ', value: ' . $Sollwert . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['Temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valStellgrad .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['Stellgrad'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valRestzeit .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['Restzeit'] . ", recipe: \"" . $r_row['recipe'] . "\"},";



        }
        return array(
            $isCalibrated,
            $valTemperature,
            $valsollwert,
            $valStellgrad,
            $valgradient,
			$valRestzeit        
        );
    }
}

// Get last values from database for selected spindle
function getlastValuesPlato4($conn, $iSpindleID = 'iSpindel000')
{
// define empty variables
    $isCalibrated = 0;
    $valDens = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;

// set connection to utf8mb4 for special characters (e.g. in recipe name)
    mysqli_set_charset($conn, "utf8mb4");

// sql query to pull last dataset for selected spindle from database
    $q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, recipe, battery, 'interval', rssi, gravity
                FROM Data
                WHERE Name = '" . $iSpindleID . "'
                ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));


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
            $f_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
// sql query to pull last dataset for selected spindle from database
                $const0 = $r_cal['const0'];
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
// now fetch the values from the data table and do some calculations
        $r_row = mysqli_fetch_array($q_sql);
        $valTime = $r_row['unixtime'];
        $valTemperature = $r_row['temperature'];
        $valAngle = $r_row['angle'];
// calculate gravity based on angle and spinlde calibration
        $valDens = $const0 * pow($valAngle, 3) + $const1 * pow($valAngle, 2) + $const2 * $valAngle + $const3; // complete polynome from database
        $valRecipe = $r_row['recipe'];
        $valInterval = $r_row['interval'];
        $valBattery = $r_row['battery'];
        $valRSSI = $r_row['rssi'];
        $valGravity = $r_row['gravity'];
        return array(
            $isCalibrated,
            $valTime,
            $valTemperature,
            $valAngle,
            $valBattery,
            $valRecipe,
            $valDens,
            $valRSSI,
            $valInterval,
            $valGravity
        );
    }
}

// get data from databse for selected spindle that is from $hours before $lasttime
function getValuesHoursAgoPlato4($conn, $iSpindleID, $lasttime, $hours)
{
// define empty variables
    $isCalibrated = 0;
    $valDens = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;

// set connection to utf8mb4 for special characters (e.g. in recipe name)
    mysqli_set_charset($conn, "utf8mb4");

// sql query to pull dataset for selected spindle from database
    $select="SELECT UNIX_TIMESTAMP(Timestamp) as unixtime, temperature, angle, recipe, battery, 'interval', rssi, gravity 
             FROM Data 
             WHERE Name = '" . $iSpindleID . "' AND Timestamp > DATE_SUB(FROM_UNIXTIME($lasttime), INTERVAL $hours HOUR) limit 1";

    $q_sql = mysqli_query($conn, $select) or die(mysqli_error($conn));

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
            $f_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const0 = $r_cal['const0'];
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
// now fetch the values from the data table and do some calculations
        $r_row = mysqli_fetch_array($q_sql);
        $valTime = $r_row['unixtime'];
        $valTemperature = $r_row['temperature'];
        $valAngle = $r_row['angle'];
// calculate gravity based on angle and spinlde calibration
        $valDens = $const0 * pow($valAngle, 3) + $const1 * pow($valAngle, 2) + $const2 * $valAngle + $const3; // complete polynome from database
        $valRecipe = $r_row['recipe'];
        $valInterval = $r_row['interval'];
        $valBattery = $r_row['battery'];
        $valRSSI = $r_row['rssi'];
        $valGravity = $r_row['gravity'];
        return array(
            $isCalibrated,
            $valTime,
            $valTemperature,
            $valAngle,
            $valBattery,
            $valRecipe,
            $valDens,
            $valRSSI,
            $valInterval,
            $valGravity
        );
    }
}

// function to pull data from the database that represents the gravity difference between two datapoints where $movingtime is the time between these two points
// Data is used for a trend diagram
// If reset is tru, data will be pulled until last reset flag. 
// otherwise data will be uplled back by $timeframehours
function getChartValuesPlato4_delta($conn, $iSpindleID = 'iSpindel000', $timeFrameHours = defaultTimePeriod, $movingtime = 720, $reset = defaultReset)
{

// get current interval in second the spindle is sending data 
    $Interval = (getCurrentInterval($conn, $iSpindleID));

// Calculate distance between datasets in rwos that are required to have $movingtime intervals in the select
    $Rows = round($movingtime / ($Interval / 60));

// define empty variables
    $isCalibrated = 0;
    $valTemperature = '';
    $valDens = '';
    $const0 = 0;
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
            $f_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const0 = $r_cal['const0'];
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }
// variable to help positioning comments as label above or below the data line (alternating)
        $label_position = 1;

// define WHERE condition dependent on reset flag
    if ($reset) {
        $where = "WHERE Name = '" . $iSpindleID . "'
            AND Timestamp > (Select max(Timestamp) FROM Data  WHERE ResetFlag = true AND Name = '" . $iSpindleID . "')";
    } else {
        $where = "WHERE Name = '" . $iSpindleID . "'
            AND Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR)
            AND Timestamp <= NOW()";
    }
// set connection to utf8mb4 for special characters (e.g. in recipe name)
    mysqli_set_charset($conn, "utf8mb4");

// start query and set @x=0 
         $p_sql = mysqli_query($conn, "SET @x:=0") or die(mysqli_error($conn));
// select data and add column x with x increasing by 1 each column
// select calculates gravity for timestamp difference between givrn timestamp and timstamp $movingtime before
// mod function basically allows the selection of every $Rows row
         if($q_sql = mysqli_query($conn, "SELECT * 
                                       FROM (SELECT (@x:=@x+1) AS x, 
                                       UNIX_TIMESTAMP(mt.Timestamp) as unixtime, 
                                       mt.name, 
                                       mt.recipe, 
                                       mt.temperature, 
                                       mt.angle, 
                                       mt.Angle*mt.Angle*mt.Angle*" . $const0. " + mt.Angle*mt.Angle*" . $const1 . " + mt.Angle*" . $const2 . " + " . $const3 . " AS Calc_Plato, 
                                       mt.Angle*mt.Angle*mt.Angle*" . $const0. " + mt.Angle*mt.Angle*" . $const1 . "+mt.Angle*" . $const2 . "+" . $const3 . " - lag(mt.Angle*mt.Angle*mt.Angle*".$const0." +mt.Angle*mt.Angle*" . $const1 . "+mt.Angle*" . $const2 . "+" . $const3 . ", " . $Rows . ") 
                                       OVER (ORDER BY mt.Timestamp) DeltaPlato 
                                       FROM Data mt " .$where . " order by Timestamp) t WHERE x MOD " . $Rows . " = 0"))
         {

// retrieve number of rows
         $rows = mysqli_num_rows($q_sql);
         while ($r_row = mysqli_fetch_array($q_sql)) {
// convert time to unixtime in milliseconds for JavaScript
             $jsTime = $r_row['unixtime'] * 1000;
             $Ddens = $r_row['DeltaPlato'];
// if DeltaPlato has no value U(e.g. fermentation is not longer than $movingtime) set it to 0
             if ($Ddens == '') {
                 $Ddens= 0;
             }

// if comment is available for current datapoint, add it to the array
             if ($r_row['comment']){
// if label position is positive, add it with text_up as variable to place it above data line
                if($label_position == 1){
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $Ddens . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
// change label position flag for next comment to negative value
                    $label_position = $label_position * -1;
                }
// if label position is negative, add it with text_down as variable to place it below data line
                else{
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $Ddens . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
// change label position flag for next comment to positive value
                    $label_position = $label_position * -1;
                }
             }
// if comment is not available for current datapoint, add datapoint w/o comment to array -> no empty comment is displayed
             else{
             $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $Ddens . ", recipe: \"" . $r_row['recipe'] . "\"},";
             }
// arrays where no comment is displayed at all
             $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
             }
         return array(
             $isCalibrated,
             $valDens,
             $valTemperature
         );
         }
         else {
             echo "Select for this diagram is using 'SQL Windows functions'. Either your Data table is still empty, or your Database does not seem to support it. If you want to use these functions you need to upgrade to a newer version of your SQL installation.<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png></a>";
             exit;
         }
}




// function to pull data for trend diagrams with moving average calculation of angle and gravity values
// attenuation and alcohol content is also calculated
// $movingtime defines the timefrime for the average calculation
function getChartValues_ma($conn, $iSpindleID, $timeFrameHours = defaultTimePeriod, $movingtime=120, $reset = defaultReset)
{
// define empty variables
    $isCalibrated = 0;
    $valAngle = '';
    $valTemperature = '';
    $valGravity = '';
    $valDens = '';
    $valSVG = '';
    $valABV = '';
    $const0 = 0;
    $const1 = 0;
    $const2 = 0;
    $const3 = 0;
    $where_ma = '';

// get current interval in second the spindle is sending data
    $Interval = (getCurrentInterval($conn, $iSpindleID));

// Calculate distance between datasets in rwos that are required to have $movingtime intervals in the select
    $Rows = round($movingtime / ($Interval / 60));

// get initial gravity for this fermentation for calculation of attenuation and alcohol content
    list($isCalibrated, $InitialGravity) = (getInitialGravity($conn, $iSpindleID));

// define WHERE condition dependent on reset flag
    if ($reset) {
        $where = "Data.Timestamp > (Select max(Timestamp) FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND Data.ResetFlag = true)";
        $where_ma = "Data2.Timestamp > (Select max(Data2.Timestamp) FROM Data AS Data2  WHERE Data2.ResetFlag = true AND Data2.Name = '" . $iSpindleID . "') AND";
    } else {
        $where = "Data.Timestamp >= date_sub(NOW(), INTERVAL " . $timeFrameHours . " HOUR) AND Data.Timestamp <= NOW()";
    }

// set connection to utf8mb4 for special characters (e.g. in recipe name)
    mysqli_set_charset($conn, "utf8mb4");

// query for moving average calculation that is using sql windows functions for the calculation
// $Rows is used to define the amount of rows that is used for the average calculation
    if (!$q_sql = mysqli_query($conn, "SELECT UNIX_TIMESTAMP(Data.Timestamp) as unixtime, Data.temperature, Data.angle, Data.recipe, Data.comment,
                                AVG(Data.Angle) OVER (ORDER BY Data.Timestamp ASC ROWS " . $Rows . " PRECEDING) AS mv_angle, 
                                AVG(Data.gravity) OVER (ORDER BY Data.Timestamp ASC ROWS " . $Rows . " PRECEDING) AS mv_gravity
                                FROM Data WHERE Data.Name = '" . $iSpindleID . "' AND " . $where)) {

// if query does not work, database is too old as it does not support the windows function
             echo "Select for this diagram is using 'SQL Windows functions'. Either your Data table is still empty, or your Database does not seem to support it. If you want to use these functions you need to upgrade to a newer version of your SQL installation.<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png></a>";
             exit;
    
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
            $f_sql = mysqli_query($conn, "SELECT const0, const1, const2, const3 FROM Calibration WHERE ID = '$uniqueID' ") or die(mysqli_error($conn));
            $rows_cal = mysqli_num_rows($f_sql);
            if ($rows_cal > 0) {
                $isCalibrated = 1;
                $r_cal = mysqli_fetch_array($f_sql);
                $const0 = $r_cal['const0'];
                $const1 = $r_cal['const1'];
                $const2 = $r_cal['const2'];
                $const3 = $r_cal['const3'];
            }
        }

// variable to help positioning comments as label above or below the data line (alternating)
        $label_position = 1;

// retrieve and store the values as CSV lists for HighCharts
        while ($r_row = mysqli_fetch_array($q_sql)) {
// convert time to unixtime in milliseconds for JavaScript
            $jsTime = $r_row['unixtime'] * 1000;
            $angle = $r_row['mv_angle'];
            $gravity = $r_row['mv_gravity'];
// calculate desnity from angle and calibration constants for selected spindle
            $dens = $const0 * pow($angle, 3) + $const1 * pow($angle, 2) + $const2 * $angle + $const3; // complete polynome from database
// real density differs fro aparent density
            $real_dens = 0.1808 * $InitialGravity + 0.8192 * $dens;
// calculte apparent attenuation
            $SVG = ($InitialGravity-$dens)*100/$InitialGravity;
// calculate alcohol by weigth and by volume (fabbier calcfabbier calc for link see above)
            $alcohol_by_weight = ( 100 * ($real_dens - $InitialGravity) / (1.0665 * $InitialGravity - 206.65));
            $alcohol_by_volume = ($alcohol_by_weight / 0.795);

// if comment is available for current datapoint, add it to the array
            if ($r_row['comment']){
// if label position is positive, add it with text_up as variable to place it above data line
                if($label_position == 1){
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
                    $valSVG .= '{ timestamp: ' . $jsTime . ', value: ' . $SVG . ", recipe: \"" . $r_row['recipe'] . "\", text_up: '" . $r_row['comment'] . "'},";
// change label position flag for next comment to negative value
                    $label_position = $label_position * -1;
                }
// if label position is negative, add it with text_down as variable to place it below data line
                else{
                    $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
                    $valSVG .= '{ timestamp: ' . $jsTime . ', value: ' . $SVG . ", recipe: \"" . $r_row['recipe'] . "\", text_down: '" . $r_row['comment'] . "'},";
// change label position flag for next comment to positive value
                    $label_position = $label_position * -1;
                }

            }
// if comment is not available for current datapoint, add datapoint w/o comment to array -> no empty comment is displayed
            else{
            $valDens .= '{ timestamp: ' . $jsTime . ', value: ' . $dens . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valAngle .= '{ timestamp: ' . $jsTime . ', value: ' . $angle . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valGravity .= '{ timestamp: ' . $jsTime . ', value: ' . $gravity . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valSVG .= '{ timestamp: ' . $jsTime . ', value: ' . $SVG . ", recipe: \"" . $r_row['recipe'] . "\"},";
            }
// arrays where no comment is displayed at all
            $valTemperature .= '{ timestamp: ' . $jsTime . ', value: ' . $r_row['temperature'] . ", recipe: \"" . $r_row['recipe'] . "\"},";
            $valABV .= '{ timestamp: ' . $jsTime . ', value: ' . $alcohol_by_volume . ", recipe: \"" . $r_row['recipe'] . "\"},";

        }

        return array(
            $isCalibrated,
            $valDens,
            $valTemperature,
            $valAngle,
            $valGravity,
            $valSVG,
            $valABV
        );
    }
}

?>
