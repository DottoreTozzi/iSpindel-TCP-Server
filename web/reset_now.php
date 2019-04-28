<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);


// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file

if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
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
$enter_write = get_field_from_sql($conn,$file,"error_write");
$reset_written = get_field_from_sql($conn,$file,"reset_written");
$recipe_written = get_field_from_sql($conn,$file,"recipe_written");

//depending on mysql config e.g. strict mode, all values need to be transfered to DB and no empty values are allowed.
//unique spindle id is pulled from DB and transferred for reset timestamp
$q_sql0 = mysqli_query($conn, "SELECT DISTINCT ID FROM Data WHERE Name = '".$Name."'AND (ID <>'' OR ID <>'0') ORDER BY Timestamp DESC LIMIT 1") or die(mysqli_error($conn));  


if (! $q_sql0){ 
  echo $error_read_id;                                             
}
$valID='0';
// get ID for selected spindle name
  $rows = mysqli_num_rows($q_sql0);                                                                                                                                                                                                           
  if ($rows > 0)                                                                                                                                                                                                                              
  {                                                                                                                                                                                                                                           
    $r_row = mysqli_fetch_array($q_sql0);                                                                                                                                                                                                     
    $valID = $r_row['ID'];
  }     


// set reset flag for spindel and write recipe name , '0' values for other parameters as 'NULL' values may cause a problem for some database configurations (strict SQL mode)
$sql_select="INSERT INTO Data (Timestamp, Name, ID, Angle, Temperature, Battery, resetFlag, Recipe)VALUES (NOW(),'$Name', $valID, 0, 0, 0, true, '$Recipe')";
    mysqli_set_charset($conn, "utf8mb4");

 $q_sql = mysqli_query($conn, $sql_select)
                       or die(mysqli_error($conn));
                       
if (! $q_sql){
   echo $error_write;
}
else {
   echo $reset_written . " <br>";
if ($Recipe <>'')
   {
   echo $recipe_written . " <b>" . $Recipe . "</b>";
   }
}
?>

