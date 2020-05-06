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
if(!isset($_GET['name'])) exit; else $_GET['name'] = $_GET['name'];

$Name = $_GET['name'];

// Get fields from database in language selected in settings
$file = "ferment_end";
$ferment_end_written = get_field_from_sql($conn,$file,"ferment_end_written");
$file = "settings";
$stop = get_field_from_sql($conn,$file,"stop");

// get recipe id and last timestamp for spindle
$get_Recipe_ID = "Select Recipe_ID,Timestamp from Data Where Name = '$Name' ORDER BY Timestamp DESC LIMIT 1";
$q_sql = mysqli_query($conn, $get_Recipe_ID) or die(mysqli_error($conn));
$result = mysqli_fetch_array($q_sql);
$recipe_ID = $result[0];
$timestamp_add = $result[1];

//check if flag for end of fermenation is already existing and remove it
$check_RID_END = "SELECT Timestamp FROM Data WHERE Recipe_ID = '$recipe_ID' AND Internal = 'RID_END'";
$q_sql = mysqli_query($conn, $check_RID_END) or die(mysqli_error($conn));
$rows = mysqli_num_rows($q_sql);

if ($rows <> 0)
{
$result = mysqli_fetch_array($q_sql);
$timestamp_del = $result[0];
$remove_recipe_ID="UPDATE Data Set Internal = NULL WHERE Recipe_ID = '$recipe_ID' AND Timestamp = '$timestamp_del'";
$q_sql = mysqli_query($conn, $remove_recipe_ID) or die(mysqli_error($conn));
}

//add Flag for end of fermentation for archive to last datapoint of current spindle
$add_recipe_ID="UPDATE Data Set Internal = 'RID_END' WHERE Recipe_ID = '$recipe_ID' AND Timestamp = '$timestamp_add'";
$q_sql = mysqli_query($conn, $add_recipe_ID) or die(mysqli_error($conn));

echo $ferment_end_written;

echo "<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png alt='$stop'></a>"
?>

