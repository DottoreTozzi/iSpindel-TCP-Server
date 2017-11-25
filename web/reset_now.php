<?php
include_once("include/common_db.php");
include_once("include/common_db_query.php");

// Check GET parameters 
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];


$Name = $_GET['name'];

$q_sql = mysqli_query($conn, "INSERT INTO Data (Timestamp, Name, resetFlag)
                      VALUES (NOW(),'$Name', true)")
                       or die(mysqli_error($conn));
                       
if (! $q_sql){
   echo "Fehler beim Insert";
}
else {
   echo "Reset-Timestamp in Datenbank eingetragen";
}
?>

