<?php
// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file

if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
     }
    include_once("include/common_db_query.php");
    if (isset($_POST['Stop']))
    {
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "index.php";
        // open the page
        header("Location: ".$url);

    }

    if (isset($_POST['Go']))
    {
        $valconst1= $_POST["const1"];
        $valconst2= $_POST["const2"];
        $valconst3= $_POST["const3"];
        $calibrated= $_POST["Is_Calib"];
        $valID= $_POST["ID"];
        $valName= $_POST["Name"];
    
        $calibrate_now = setSpindleCalibration($conn, $valID, $calibrated, $valconst1, $valconst2, $valconst3);

        if (!$calibrate_now){
            echo 'Fehler beim Schreiben der Daten an die Datenbank';
            exit;
           }
        else
            {
            $url="http://";
            $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
            $url .= "calibration.php";
            $url .= "?name=" . $_POST["Name"]; 
            // open the page
            header("Location: ".$url);

        }
 
    }

// Check GET parametersi
// Added parameter recipe to set recipe name at reset point. Recipe nam will be displayed in diagrams as header and in tooltip
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];

$iSpindleID = $_GET['name'];

$valCalib = getSpindleCalibration($conn, $iSpindleID );

$file = "calibration";
$window_alert_update = get_field_from_sql($conn,$file,"window_alert_update");
$enter_constants = get_field_from_sql($conn,$file,"enter_constants");
$constant1 = get_field_from_sql($conn,$file,"constant1");
$constant2 = get_field_from_sql($conn,$file,"constant2");
$constant3 = get_field_from_sql($conn,$file,"constant3");
$header = get_field_from_sql($conn,$file,"header");
$send = get_field_from_sql($conn,$file,"send");
$stop = get_field_from_sql($conn,$file,"stop");
 

?>

<!DOCTYPE html>
<html>
<head>

    <title>RasPySpindel Kalibrierung</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">

<script type="text/javascript">
    function target_popup(form) {
        window.alert('<?php echo $window_alert_update; ?>');
    }
</script>

</head>

<body bgcolor="#E6E6FA">
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
<h1><?php echo $header.' '.$iSpindleID."_".$valCalib[4] ?></h1>

<div id="Calibrate">
<?php
$const1='0.000000001';
$const2='0.000000001';
$const3='0.000000001';

$valCalib = getSpindleCalibration($conn, $iSpindleID );

if ($valCalib[0])
{
$const1=$valCalib[1];
$const2=$valCalib[2];
$const3=$valCalib[3];
}
?>

<p><b><?php echo $enter_constants ?></b><br/>
<br/>
<?php echo $constant1 ?> <input type = "number" name = "const1" step = "0.000000001" value = <?php echo $const1 ?> />
<?php echo $constant2 ?> <input type = "number" name = "const2" step = "0.000000001" value = <?php echo $const2 ?> />
<?php echo $constant3 ?> <input type = "number" name = "const3" step = "0.000000001" value = <?php echo $const3 ?> />
<input type = "hidden" name="Is_Calib" value= <?php echo $valCalib[0] ?>>
<input type = "hidden" name="ID" value= <?php echo $valCalib[4] ?>>
<input type = "hidden" name="Name" value= <?php echo $iSpindleID ?>>

<br/>
</p>
</div>

<input type = "submit" name = "Go" value = "<?php echo $send ?>" onclick="target_popup(this)">
<input type = "submit" name = "Stop" value = "<?php echo $stop ?>">
<br />
