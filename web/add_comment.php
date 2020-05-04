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
if(!isset($_GET['name'])) $_GET['name'] = ''; else $_GET['name'] = $_GET['name'];
if(!isset($_GET['comment'])) $_GET['comment'] = ''; else $_GET['comment'] = $_GET['comment'];

$Name = $_GET['name'];
$Comment = $_GET['comment'];

// Get fields from database in language selected in settings
$file = "add_comment";
$error_write = get_field_from_sql($conn,$file,"error_write");
$comment_written = get_field_from_sql($conn,$file,"comment_written");
$file = "settings";
$stop = get_field_from_sql($conn,$file,"stop");

$max_timestamp_sql="SELECT max(Timestamp) FROM Data where Name='".$Name."'";
$q_sql = mysqli_query($conn, $max_timestamp_sql) or die(mysqli_error($conn));
$result = mysqli_fetch_array($q_sql);  
$timestamp = $result[0];

mysqli_set_charset($conn, "utf8mb4");
$add_comment_sql = "UPDATE Data Set Comment = '$Comment' WHERE Name = '$Name' AND Timestamp = '$timestamp'";

$q_sql = mysqli_query($conn, $add_comment_sql) or die(mysqli_error($conn));
    
       
if (! $q_sql){
   echo $error_write;
}
else {
   echo $comment_written . "<b>" . $Comment . "</b>";" <br>";
}
echo "<br/><br/><a href=/iSpindle/index.php><img src=include/icons8-home-26.png alt='$stop'></a>"
?>

