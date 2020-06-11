<?php
// April 2019
// Added selection for spindel that has to be calibrated to this script
//
// December 2018
// Initial script

// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file

if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
     }
    include_once("include/common_db_query.php");

//go back to index page if back button is selected    
if (isset($_POST['Stop']))
    {
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "index.php";
        // open the page
        header("Location: ".$url);

    }

// if send button is selected send calibration values to database and reload page
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
            $url .= "?name=" . $_POST["current_id"]; 
            // open the page
            header("Location: ".$url);

        }
 
    }

// Check GET parameters
// _GET['name'] is a number and represents an index of an array.
// if parameter is not set, first entry of array will be used (==0)
if(!isset($_GET['name'])) $_GET['name'] = '0'; else $_GET['name'] = $_GET['name'];
$current_spindle = $_GET['name'];
// select spindle names and add them to an array
$sql_q = "SELECT max(Timestamp), Name FROM Data GROUP BY Name";
    $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));

    $spindle_list = array();
    while($row_s = mysqli_fetch_assoc($result) ) {
    $spindle_list[] = $row_s['Name'];
    }

// convert array ID to Spindle Name -> iSpindleID
$iSpindleID=$spindle_list[$_GET['name']];


//get current calibration values for iSpindelID
$valCalib = getSpindleCalibration($conn, $iSpindleID );

// set default values for constans if spinlde has no calibration entry yet
$const1='0.000000001';
$const2='0.000000001';
$const3='0.000000001';

// if spindle is calibrated, use the values from the database
if ($valCalib[0])
{
    $const1=$valCalib[1];
    $const2=$valCalib[2];
    $const3=$valCalib[3];
}

// get CSS layout for page from settings table
$document_class = get_color_scheme($conn);

// Get fields from database in language selected in settings
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

    <meta charset="utf-8">
    <title>RasPySpindel Kalibrierung</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">
    <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">
<a href=/iSpindle/index.php><img src=include/icons8-home-26.png alt="<?php echo $stop; ?>"></a>

<script type="text/javascript">
    function target_popup(form) {
        window.alert('<?php echo $window_alert_update; ?>');
    }

// function to reload page when spindle is changed -> different constants will be shown
    function reload_page() {
        var iSpindleID = document.getElementById('ispindel_name').selectedIndex;
        var variable = '?name='.concat(iSpindleID);
        var url = "http://";
        var server = window.location.hostname;
        var path = window.location.pathname;
        var full_path = url.concat(server).concat(path).concat(variable);
        window.open(full_path,"_self");
    }


</script>

</head>

<body class='<?php echo $document_class ?>'>
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
<h1><?php echo $header.' '.$iSpindleID."_".$valCalib[4] ?></h1>

<div id="Calibrate">
<!-- select options for spindle names -->
<select id="ispindel_name" name = 'ispindel_name' onchange="reload_page()">
        <?php
            $i = 0;
            $max = count ($spindle_list);
            while($i < $max ) {
                if ($i <> $_GET['name']) {
                    echo'<option value = "' . $spindle_list[$i].'" name = "' . $spindle_list[$i].'">';
                    echo($spindle_list[$i]);
                    echo"</option>\n";
                }
                else {
                    echo'<option value = "' . $spindle_list[$i] .'" selected name = "' . $spindle_list[$i].'">';
                    echo($spindle_list[$i]);
                    echo"</option>\n";
                }
            $i = ++$i;
            }
        ?>


        </option>
</select>

<!-- number fields for constants -->
<p><b><?php echo $enter_constants ?></b><br/>
<br/>
<?php echo $constant1 ?> <input type = "number" name = "const1" step = "0.000000001" value = <?php echo $const1 ?> />
<br/>
<?php echo $constant2 ?> <input type = "number" name = "const2" step = "0.000000001" value = <?php echo $const2 ?> />
<br/>
<?php echo $constant3 ?> <input type = "number" name = "const3" step = "0.000000001" value = <?php echo $const3 ?> />
<br/>

<!-- hidden fields. Information required to write back calibration data for corresponding spindel-->
<input type = "hidden" name="Is_Calib" value= <?php echo $valCalib[0] ?>>
<input type = "hidden" name="ID" value= <?php echo $valCalib[4] ?>>
<input type = "hidden" name="Name" value= <?php echo $iSpindleID ?>>
<input type = "hidden" name="current_spindle" value="<?php echo $spindle_list[$_GET['name']]; ?>">
<input type = "hidden" name="current_id" value="<?php echo $_GET['name']; ?>">


<br/>
</p>
</div>

<input type = "submit" name = "Go" value = "<?php echo $send ?>" onclick="target_popup(this)">
<input type = "submit" name = "Stop" value = "<?php echo $stop ?>">
<br />


