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
// Spindle name is required as input parameter
if(!isset($_GET['name'])) exit; else $_GET['name'] = $_GET['name'];

$Name = $_GET['name'];

// Get fields from database in language selected in settings
$file = "ferment_end";
$ferment_end_written = get_field_from_sql($conn,$file,"ferment_end_written");
$file = "settings";
$stop = get_field_from_sql($conn,$file,"stop");
$file = "reset_now";
$error_read_id = get_field_from_sql($conn,$file,"error_read_id");
$error_write = get_field_from_sql($conn,$file,"error_write");

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


// get recipe id and last timestamp for spindle where flag will be added
$get_Recipe_ID = "Select Recipe_ID,Timestamp from Data Where Name = '$Name' ORDER BY Timestamp DESC LIMIT 1";
$q_sql = mysqli_query($conn, $get_Recipe_ID) or die(mysqli_error($conn));
$result = mysqli_fetch_array($q_sql);
$recipe_ID = $result[0];
$timestamp_add = $result[1];

//check if flag for end of fermenation is already existing 
$check_RID_END = "SELECT Timestamp FROM Data WHERE Recipe_ID = '$recipe_ID' AND Internal = 'RID_END'";
$q_sql = mysqli_query($conn, $check_RID_END) or die(mysqli_error($conn));
$rows = mysqli_num_rows($q_sql);

// remove flag if it is already existing for the selected recipe_ID
if ($rows <> 0)
{
$result = mysqli_fetch_array($q_sql);
$timestamp_del = $result[0];
$remove_recipe_ID="UPDATE Data Set Internal = NULL WHERE Recipe_ID = '$recipe_ID' AND Timestamp = '$timestamp_del'";
$q_sql = mysqli_query($conn, $remove_recipe_ID) or die(mysqli_error($conn));
}

//add Flag for end of fermentation for archive to last timestamp datapoint of current spindle
$add_recipe_ID="UPDATE Data Set Internal = 'RID_END' WHERE Recipe_ID = '$recipe_ID' AND Timestamp = '$timestamp_add'";
$q_sql = mysqli_query($conn, $add_recipe_ID) or die(mysqli_error($conn));

echo $ferment_end_written;

echo "<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png alt='$stop'></a>"
?>

