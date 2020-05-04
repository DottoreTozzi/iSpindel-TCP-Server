<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);


// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file

if ((include_once './config/common_db_config.php') == FALSE){
       include_once("./config/common_db_default.php");
     }
    include_once("include/common_db_query.php");


// Check GET parameters
// Added parameter recipe to set recipe name at reset point. Recipe nam will be displayed in diagrams as header and in tooltip
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];
if(!isset($_GET['recipe'])) $_GET['recipe'] = ''; else $_GET['recipe'] = $_GET['recipe'];

$Name = $_GET['name'];
$Recipe = $_GET['recipe'];

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
$valID='0';

$const1=NULL;
$const2=NULL;
$const3=NULL;

// get ID for selected spindle name
$rows = mysqli_num_rows($q_sql0);
if ($rows > 0) {                                                                                                                                                                                                                 
    $r_row = mysqli_fetch_array($q_sql0);                                                                                                                                                                                                        $valID = $r_row['ID'];

    $valCalib = getSpindleCalibration($conn, $Name );
    if ($valCalib[0])
        {
            $const1=$valCalib[1];
            $const2=$valCalib[2];
            $const3=$valCalib[3];
        }


    }     

$update_archive_sql="SELECT max(Recipe_ID) FROM Archive where Name='".$Name."'";
$q_sql = mysqli_query($conn, $update_archive_sql) or die(mysqli_error($conn));
$ID = mysqli_fetch_array($q_sql);  
// if spindle has already an entry in the archive table, update the end_date of the former batch and update the current calibration data if canged during batch
if($ID[0]){
//    echo "Recipe_ID: ".$ID[0];
    $timestamp_2 = date("Y-m-d H:i:s"); 
    $update_archive_table = "UPDATE Archive Set End_date = '".$timestamp_2."', const1 = '".$const1."', const2 = '".$const2."', const3 = '".$const3."' WHERE Recipe_ID = '".$ID[0]."'";
    $q_sql = mysqli_query($conn, $update_archive_table) or die(mysqli_error($conn));
    
  }
//now add a new entry to the archive tabli with information on the new recipe, start date  Spindle ID and current calibration data
$timestamp = date("Y-m-d H:i:s");
$entry_recipe_table_sql = "INSERT INTO `Archive`
                           (`Recipe_ID`, `Name`, `ID`, `Recipe`, `Start_date`, `End_date`, `const1`, `const2`, `const3`)
                           VALUES (NULL, '".$Name."', '".$valID."', '".$Recipe."', '".$timestamp."', NULL, '".$const1."', '".$const2."', '".$const3."')";
mysqli_set_charset($conn, "utf8mb4");
$entry_result = mysqli_query($conn, $entry_recipe_table_sql) or die(mysqli_error($conn));

// now get the newly created Recipe_ID
$get_latest_archive_id_sql="SELECT max(Recipe_ID) FROM Archive where Name='".$Name."'";
$q_sql = mysqli_query($conn, $get_latest_archive_id_sql) or die(mysqli_error($conn));
$ID = mysqli_fetch_array($q_sql);

// set reset flag for spindel and write recipe name , '0' values for other parameters as 'NULL' values may cause a problem for some database configurations (strict SQL mode)
$sql_select="INSERT INTO Data (Timestamp, Name, ID, Angle, Temperature, Battery, resetFlag, Recipe, Recipe_ID)VALUES ('$timestamp','$Name', $valID, 0, 0, 0, true, '$Recipe','$ID[0]')";
mysqli_set_charset($conn, "utf8mb4");
$q_sql = mysqli_query($conn, $sql_select) or die(mysqli_error($conn));
       
if (! $q_sql){
   echo $error_write;
}
else {
   echo $reset_written . " <br>";
if ($Recipe <>'')
   {
   echo $recipe_written . " <b>" . $Recipe . "</b>";
   }
$del_low = delete_mail_sent($conn,"SentAlarmLow",$valID);
$del_svg = delete_mail_sent($conn,"SentAlarmSVG",$valID);

}
echo "<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png alt='$stop'></a>"
?>

