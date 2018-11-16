<?php
include_once("include/common_db.php");
include_once("include/common_db_query.php");



// Check GET parametersi
// Added parameter recipe to set recipe name at reset point. Recipe nam will be displayed in diagrams as header and in tooltip
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];
if(!isset($_GET['recipe'])) $_GET['recipe'] = ''; else $_GET['recipe'] = $_GET['recipe'];

$Name = $_GET['name'];
$Recipe = $_GET['recipe'];

$q_sql = mysqli_query($conn, "INSERT INTO Data (Timestamp, Name, resetFlag, Recipe)
                      VALUES (NOW(),'$Name', true, '$Recipe')")
                       or die(mysqli_error($conn));
                       
if (! $q_sql){
   echo "Fehler beim Insert";
}
else {
   echo "Reset-Timestamp in Datenbank eingetragen <br>";
   echo "Sudname <b>$Recipe</b> in Datenbank eingetragen";
}
?>

