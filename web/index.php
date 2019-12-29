<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
    
    // Landing page (Homepage) for RasPySpindel Project
    // Selecting chart, iSpindel name, timeframe and other parameters here
    // For now, this is in German. Help porting it to other languages appreciated.
    //
    // Future enhancements could/should include:
    // - remote configuration
    // - calibration
    // - data management (delete old stuff)
    // - configure timezone, units (F/C, SG/%ww)
    // - localization of charts (and this page) generally
    // - make the whole thing look prettier
    //
    // GET parameter:
    // days = number of days in the past we should look for active iSpindels for
    // default 7 days is configured in include/common_db.php
    //
	// December 2018:
	// Database config parameters will be pulled from different directory. User can use personalized config file: common_db_config.php in config directory
	// If personalized file does not exist, default config will be loaded: common_db_default.php
	// Added function to display input field for Sudname on this page only if reset_now.php is selected
	// If Sudname is entered, it is transferred to the database
	// Added chart to display battery and Wifi strength trend
    //
    // January 2019
    // Added chart for apparent attenuation and delta trend for plato4
    // Added support for different languages. Fields stored in strings table in databse
    //
    // April 2019
    // Added support for Umlauts or other characters in recipe name.  
    // --> requires change for recipe culomn in 'data' table of database -> encoding: utf8mb4
    // Added several buttons to the page and removed spindle calibrarion from the select fields
    // Added more java functionality to display only the fileds required for the corresponding selection 
    // Changed back the select for spindesl to be displayed: Only spindles that have sent data the past x days ago will be shown on this page.
    // Calibration calls a separate script where the user has to select the spindel that should be calibrated. All spindles are shown there
    
    

    // Self-called by submit button?
    if (isset($_POST['Go']))
    {

        // construct url
        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= $_POST["chart_filename"];
        $url .="?name=".$_POST["ispindel_name"];
        if ($_POST["fromreset"]<>'1'){
        $url .="&days=".$_POST["days"];}
        if ($_POST["fromreset"]<>'0'){
	$url .="&reset=".$_POST["fromreset"];}
        if ($_POST["recipename"]<>''){
        $url .="&recipe=".$_POST["recipename"];}

        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }

// Self-called by settings button
// calls php script to change TCP-Server settings

    if (isset($_POST['Set']))
    {

        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= 'settings.php';
        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }

// Self-called by changedayshistory button
// calls index page with different daysago settings

    if (isset($_POST['Change']))
    {

        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= 'index.php';
        $url .="?days=".$_POST["changedefaultdays"];
        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }

// Self-called by calibrate button
// calls php script to calibrate Spindle in  TCP-Server

    if (isset($_POST['Cal']))
    {

        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= 'calibration.php';
        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }
    
    // Loads personal config file for db connection details. If not found, default file will be used
    if ((include_once './config/common_db_config.php') == FALSE){
       include_once("./config/common_db_default.php");
    }
//  Loads db query functions
include_once("./include/common_db_query.php");

// sql queries to get language dependent fields to be displayed
    $file = "index";
    $chart_filename_01 = get_field_from_sql($conn,$file,"chart_filename_01");
    $chart_filename_02 = get_field_from_sql($conn,$file,"chart_filename_02");
    $chart_filename_03 = get_field_from_sql($conn,$file,"chart_filename_03");
    $chart_filename_04 = get_field_from_sql($conn,$file,"chart_filename_04");
    $chart_filename_05 = get_field_from_sql($conn,$file,"chart_filename_05");
    $chart_filename_06 = get_field_from_sql($conn,$file,"chart_filename_06");
    $chart_filename_07 = get_field_from_sql($conn,$file,"chart_filename_07");
    $chart_filename_08 = get_field_from_sql($conn,$file,"chart_filename_08");
    $chart_filename_09 = get_field_from_sql($conn,$file,"chart_filename_09");
    $chart_filename_10 = get_field_from_sql($conn,$file,"chart_filename_10");
    $chart_filename_11 = get_field_from_sql($conn,$file,"chart_filename_11");
    $chart_filename_12 = get_field_from_sql($conn,$file,"chart_filename_12");
    $chart_filename_13 = get_field_from_sql($conn,$file,"chart_filename_13");
    $show_diagram = get_field_from_sql($conn,$file,"show_diagram");
    $calibrate_spindle = get_field_from_sql($conn,$file,"calibrate_spindle");
    $server_settings = get_field_from_sql($conn,$file,"server_settings");
    $server_running = get_field_from_sql($conn,$file,"server_running");
    $server_not_running = get_field_from_sql($conn,$file,"server_not_running");
    $reset_flag = get_field_from_sql($conn,$file,"reset_flag");
    $diagram_selection = get_field_from_sql($conn,$file,"diagram_selection");
    $recipe_name = get_field_from_sql($conn,$file,"recipe_name");
    $days_history = get_field_from_sql($conn,$file,"days_history");
    $or = get_field_from_sql($conn,$file,"or");
    $send_reset = get_field_from_sql($conn,$file,"send_reset"); 
    $no_data = get_field_from_sql($conn,$file,"no_data"); 
    $header_initialgravity = get_field_from_sql($conn,$file,"header_initialgravity");
    $change_history = get_field_from_sql($conn,$file,"change_history");
    $help = get_field_from_sql($conn,$file,"help");


    $header_recipe = get_field_from_sql($conn,'diagram',"recipe_name");

    $file = "status";
    $header_battery = get_field_from_sql($conn,$file,"header_battery");
    $header_temperature = get_field_from_sql($conn,$file,"header_temperature");
    $header_angle = get_field_from_sql($conn,$file,"header_angle");

    $file = "wifi";
    $header_wifi = get_field_from_sql($conn,$file,"header");

    $file = "plato4";
    $header_density = get_field_from_sql($conn,$file,"first_y");
    $header_time = get_field_from_sql($conn,$file,"x_axis");

    $file = "svg_ma";
    $header_svg = get_field_from_sql($conn,$file,"first_y");
    $header_alcohol = get_field_from_sql($conn,$file,"third_y");


    // "Days Ago parameter set?
    if(!isset($_GET['days'])) $_GET['days'] = 0; else $_GET['days'] = $_GET['days'];
    $daysago = $_GET['days'];
    if($daysago == 0) $daysago = defaultDaysAgo;

// get information if TCP server is running
    $pids=''; 
    $running=false;
    $stat = exec("systemctl is-active ispindle-srv");
    if (file_exists( "/var/run/ispindle-srv.pid" )) {
        $pid= (shell_exec("cat /var/run/ispindle-srv.pid"));
        $running = posix_getpgid(intval($pid));
    }
    if ($running) {
        $iSpindleServerRunning = $server_running . $pid;
    } elseif ($stat == "activating") {
              $iSpindleServerRunning = $server_running;
    } else {
        $iSpindleServerRunning = $server_not_running;
    }

// get all spindle names to be displayed in form that have submitted data within the timeframe of $daysago
    $sql_q = "SELECT DISTINCT Name FROM Data WHERE Timestamp > date_sub(NOW(), INTERVAL ".$daysago." DAY) ORDER BY Name";
    $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
    $len = mysqli_num_rows($result);
    $result1=mysqli_query($conn, $sql_q) or die(mysqli_error($conn)); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>RasPySpindel Homepage</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"> 
    <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">
<script type="text/javascript">
// Function to hide or display elements. Used for recipe name. Only displayed if reset_now is selected in options
    function einblenden(){
        var select = document.getElementById('chart_filename').selectedIndex;
        if(select == 11 ){
           document.getElementById('ResetNow').style.display = "block";
           document.getElementById('send').style.display = "block";
           document.getElementById('show').style.display = "none";
           document.getElementById('diagrams').style.display = "none";

        }
        else {
            document.getElementById('ResetNow').style.display = "none";        
            document.getElementById('send').style.display = "none";
            document.getElementById('show').style.display = "block";
            document.getElementById('diagrams').style.display = "block";

        }
    }
</script>

</head>
<body>
<!-- <body bgcolor="#E6E6FA"> -->
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
<h1>RasPySpindel</h1>
<h3><?php echo($diagram_selection .' '. $daysago)?></h3>

<!-- select options for spindle names -->
<?php
    if ($len != 0){
?>
<select id="ispindel_name" name = 'ispindel_name'>
        <?php
            while($row = mysqli_fetch_assoc($result) )
            {
                ?>
                <option value = "<?php echo($row['Name'])?>">
                <?php echo($row['Name']) ?>
        <?php
            }
        ?>
        </option>
</select>

<!-- select options for diagrams to be loaded -->
<select id="chart_filename" name='chart_filename' onchange="einblenden()">;
        <option value="status.php" selected><?php echo $chart_filename_01 ?></option>
        <option value="battery.php"><?php echo $chart_filename_02 ?></option>
        <option value="wifi.php"><?php echo $chart_filename_03 ?></option>
        <option value="plato4.php"><?php echo $chart_filename_04 ?></option>
        <option value="plato4_ma.php"><?php echo $chart_filename_05 ?></option>
        <option value="angle.php"><?php echo $chart_filename_06 ?></option>
        <option value="angle_ma.php"><?php echo $chart_filename_07 ?></option>
        <option value="plato.php"><?php echo $chart_filename_08 ?></option>
        <option value="batterytrend.php"><?php echo $chart_filename_12 ?></option>>
        <option value="svg_ma.php"><?php echo $chart_filename_10 ?></option>
        <option value="plato4_delta.php"><?php echo $chart_filename_11 ?></option>	
        <option value="reset_now.php"><?php echo $chart_filename_09 ?></option>
<!--        <option value="calibration.php"><?php echo $chart_filename_13 ?></option> -->
</select>

<br />
<br />

<!-- "hidden" checkbox to make sure we have a response here and not just send "null" -->
<input type = "hidden" name="fromreset" value="0">

<div id="diagrams" style="display: block;">
<input type = "checkbox" name="fromreset" value="1">
<?php echo($reset_flag)?>

<br />
<?php echo($or)?>
<input type = "number" name = "days" min = "1" max = "365" step = "1" value = "<?php echo($daysago)?>">
<?php echo($days_history)?>
<br />
<br />
</div>

<div id="ResetNow" style="display: none;">
<p><?php echo($recipe_name)?>
<input type = "text" name = "recipename" /> </p>
</div>

<div id="show" style="display: block;">
<span title="<?php echo($show_diagram)?>"><input type = "submit" id='diagram' name = "Go" value = "<?php echo($show_diagram)?>"></span>
</div>
<div id="send" style="display: none;">
<span title="<?php echo($send_reset)?>"><input type = "submit" id='reset' name = "Go" value = "<?php echo($send_reset)?>"></span>
</div>

<?php
# endif len !=0
}
else {
    echo sprintf($no_data, $daysago);
    echo "<br />";
    echo"<input type = 'number' name = 'changedefaultdays' min = '1' max = '365' step = '1' value = '$daysago'>";
    echo($days_history);
    echo "<br /><br />";

    echo "<div id='change' style='display: block;'>";
    echo "<span title='$change_history'><input type = 'submit' id='changehistory' name = 'Change' value = '$change_history'></span>";
    echo "</div>";

}

?>
<br />
<br />

<span title="<?php echo($calibrate_spindle)?>"> <input type = "submit" id='calibrate' name = "Cal" value="<?php echo($calibrate_spindle)?>"></span>
<span title="<?php echo($server_settings)?>"><input type = "submit" id='settings' name = "Set" value = "<?php echo($server_settings)?>"></span>

<br />



<br/><br/><?php echo($iSpindleServerRunning)?> <br/></br></br>

<?php

// if a section is selected (default is 0), table will be defined
// Database entries for parameter, value and description of defined language will be displayed for selected section
// name of input field gets unique id (combination of section and parameter). This is used to identify parameter value during _POST['GO']
if ($len !=0 ){
    echo "<table border='1'>";
    echo "<tr>";
    echo "<td><b>Device</b></td>";
    echo "<td><b>$header_time</b></td>";
    echo "<td><b>$header_recipe</b></td>";
    echo "<td><b>$header_angle [°]</b></td>";
    echo "<td><b>$header_temperature [°C]</b></td>";
    echo "<td><b>$header_initialgravity</b></td>";
    echo "<td><b>$header_density</b></td>";
    echo "<td><b>$header_svg</b></td>";
    echo "<td><b>$header_alcohol</b></td>";
    echo "<td><b>$header_battery [Volt]</b></td>";
    echo "<td><b>$header_wifi [dB]</b></td>";
    echo "</tr>";
    while($row = mysqli_fetch_assoc($result1) ) {
        $show_device=get_settings_from_sql($conn, 'GENERAL', $row['Name'],'SHOWSUMMARY'); 
        if ($show_device == 1){
        list($iscalibrated, $time, $temperature, $angle, $battery, $recipe, $dens, $RSSI) = getlastValuesPlato4($conn, $row['Name']);

        $gravity=getInitialGravity($conn, $row['Name']);
        if ($gravity[0]==1){
        $initialgravity=$gravity[1];
        #calculate apparent attenuation
        $SVG=($initialgravity - $dens)*100/$initialgravity;
        #real density differs from aparent density
        $realdens = 0.1808 * $initialgravity + 0.8192 * $dens;
        # calculate alcohol by weigth and by volume (fabbier calcfabbier calc for link see above)
        $ABV = (( 100 * ($realdens - $initialgravity) / (1.0665 * $initialgravity- 206.65))/0.795);

        }
        else {
        $initialgravity=0;
        $SVG=0;
        }
        echo "<tr>";
        echo "<td><b>" . $row['Name'] . "</b></td>";
        echo "<td>" . date("Y-m-d\ H:i:s\ ",$time) . "</td>";
        echo "<td>" .  $recipe . "</td>";
        echo "<td style='text-align:center'>" . number_format($angle,1) . "</td>";
        echo "<td style='text-align:center'>" . number_format($temperature,1) . "</td>";
        echo "<td style='text-align:center'>" . number_format($initialgravity,1) . "</td>";
        echo "<td style='text-align:center'>" . number_format($dens,1) . "</td>";
        echo "<td style='text-align:center'>" . number_format($SVG,1) . "</td>";
        echo "<td style='text-align:center'>" . number_format($ABV,1) . "</td>";
        echo "<td style='text-align:center'>" . number_format($battery,2) . "</td>";
        echo "<td style='text-align:center'>" . number_format($RSSI,0) . "</td>";
        echo "</tr>\n";
        }
    }
echo "</table>";
}
?>
 

<footer>
<?php echo"<div><a href='help.php' title='$help'>$help</a></div>"; ?>
<!-- <div>Icons made by <a href="https://www.flaticon.com/authors/prosymbols" title="Prosymbols">Prosymbols</a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a></div> -->
</footer>

</body>
</form>
