<?php
// error handling
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);


// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file

if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
     }
    include_once("include/common_db_query.php");

// Check GET parameters
// Parameter recipe to set recipe name at reset point. Recipe name will be displayed in diagrams as header and in tooltip for selected spindle 
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];
if(!isset($_GET['recipe'])) $_GET['recipe'] = ''; else $_GET['recipe'] = $_GET['recipe'];
if(!isset($_GET['batch'])) $_GET['batch'] = ''; else $_GET['batch'] = $_GET['batch'];


$Name = $_GET['name'];
$Recipe = $_GET['recipe'];
$Batch = $_GET['batch'];

// Get fields from database in language selected in settings
$file = "reset_now";
$error_read_id = get_field_from_sql($conn,$file,"error_read_id");
$error_write = get_field_from_sql($conn,$file,"error_write");
$reset_written = get_field_from_sql($conn,$file,"reset_written");
$recipe_written = get_field_from_sql($conn,$file,"recipe_written");
$file = "settings";
$stop = get_field_from_sql($conn,$file,"stop");

//depending on mysql config e.g. strict mode, all values need to be transfered to DB and no empty values are allowed.
//unique spindle id is pulled from DB and transferred for reset timestamp
$q_sql0 = mysqli_query($conn, "SELECT DISTINCT ID FROM Data WHERE Name = '".$Name."'AND (ID <>'' OR ID <>'0') ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));  
if (! $q_sql0){ 
    echo $error_read_id;                                             
    }

// set default values for variables
$valID='0';
$const0=NULL;
$const1=NULL;
$const2=NULL;
$const3=NULL;

// get ID for selected spindle name
$rows = mysqli_num_rows($q_sql0);
// check if spindle has an ID
if ($rows > 0) {                                                                                                                                                                                                                 
    $r_row = mysqli_fetch_array($q_sql0);                                                                                                                                                                                                        $valID = $r_row['ID'];
// get calibration values from calibration table for selected spindle
    $valCalib = getSpindleCalibration($conn, $Name );
    if ($valCalib[0])
        {
            $const0=$valCalib[1];
            $const1=$valCalib[2];
            $const2=$valCalib[3];
            $const3=$valCalib[4];
        }


    }     
// get latest recipe_id for selected spindle
$update_archive_sql="SELECT max(Recipe_ID) FROM Archive where Name='".$Name."'";
$q_sql = mysqli_query($conn, $update_archive_sql) or die(mysqli_error($conn));
$ID = mysqli_fetch_array($q_sql);  
// if spindle has already an entry in the archive table, update the end_date of the former batch and update the current calibration data if canged during batch
if($ID[0]){
    $timestamp_2 = date("Y-m-d H:i:s"); 
    if ($const1 != NULL){
        $update_archive_table = "UPDATE Archive Set End_date = '".$timestamp_2."', const0 = '" .$const0. "',const1 = '".$const1."', const2 = '".$const2."', const3 = '".$const3."' WHERE Recipe_ID = '".$ID[0]."'";
        }
    else {
        $update_archive_table = "UPDATE Archive Set End_date = '".$timestamp_2."', const0 = NULL, const1 = NULL, const2 = NULL, const3 = NULL WHERE Recipe_ID = '".$ID[0]."'";
        }
        write_log($update_archive_table);
        $q_sql = mysqli_query($conn, $update_archive_table) or die(mysqli_error($conn));    
  }
//now add a new entry to the archive table with information on the new recipe, start date  Spindle ID and current calibration data
$timestamp = date("Y-m-d H:i:s");
if ($const1 != NULL){
$entry_recipe_table_sql = "INSERT INTO `Archive`
                           (`Recipe_ID`, `Name`, `ID`, `Recipe`, `Batch`, `Start_date`, `End_date`, `const0`, `const1`, `const2`, `const3`)
                           VALUES (NULL, '".$Name."', '".$valID."', '".$Recipe."', '".$Batch."', '".$timestamp."', NULL, '$const0', '$const1', '$const2', '$const3')";
}
else {
$entry_recipe_table_sql = "INSERT INTO `Archive`
                           (`Recipe_ID`, `Name`, `ID`, `Recipe`, `Batch`, `Start_date`, `End_date`, `const0`,`const1`, `const2`, `const3`)
                           VALUES (NULL, '".$Name."', '".$valID."', '".$Recipe."', '".$Batch."', '".$timestamp."', NULL, NULL, NULL, NULL, NULL)";
}
write_log($entry_recipe_table_sql);
mysqli_set_charset($conn, "utf8mb4");
$entry_result = mysqli_query($conn, $entry_recipe_table_sql) or die(mysqli_error($conn));

// now get the newly created Recipe_ID
$get_latest_archive_id_sql="SELECT max(Recipe_ID) FROM Archive where Name='".$Name."'";
$q_sql = mysqli_query($conn, $get_latest_archive_id_sql) or die(mysqli_error($conn));
$ID = mysqli_fetch_array($q_sql);

// set reset flag for spindel and write recipe name , '0' values for other parameters as 'NULL' values may cause a problem for some database configurations (strict SQL mode)
$sql_select="INSERT INTO Data (Timestamp, Name, ID, Angle, Temperature, Battery, resetFlag, RSSI, Recipe, Recipe_ID)VALUES ('$timestamp','$Name', $valID, 0, 0, 0, true, 0, '$Recipe','$ID[0]')";
// set utf8mb4 for better compatibility with characters such as ä,ö,ü,..
mysqli_set_charset($conn, "utf8mb4");
$q_sql = mysqli_query($conn, $sql_select) or die(mysqli_error($conn));

// report error if last select id not work
if (! $q_sql){
   echo $error_write;
}
else {
   echo $reset_written . " <br>";
if ($Recipe <>'')
   {
   echo $recipe_written . " <b>" . $Recipe . "</b>";
   }
// remove also the 'mail sent flag' for several alarms
$del_low = delete_mail_sent($conn,"SentAlarmLow",$valID);
$del_svg = delete_mail_sent($conn,"SentAlarmSVG",$valID);

}
echo "<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png alt='$stop' width='50' height='50'></a>"
?>

