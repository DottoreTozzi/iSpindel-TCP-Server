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
    
// Loads personal config file for db connection details. If not found, default file will be used
if ((include_once './config/common_db_config.php') == FALSE){
       include_once("./config/common_db_default.php");
    }
//  Loads db query functions
include_once("./include/common_db_query.php");
include_once("./config/tables.php");
 

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
        if ($_POST["comment_text"]<>''){
        $url .="&comment=".$_POST["comment_text"];}

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

// Self-called by archive button
// calls php script to load archive data in TCP-Server

    if (isset($_POST['archive']))
    {

        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= 'archive.php';
        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }


// Self-called by update_strings button
// updates strings table with latest version and calls index page

    if (isset($_POST['Up_Str']))
    {
        upgrade_strings_table($conn);
        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= 'index.php';
        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }

// Self-called by update_settings button
// updates settings table with latest version and calls index page

    if (isset($_POST['Up_Set']))
    {
        upgrade_settings_table($conn);
        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= 'index.php';
        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }

    if (isset($_POST['Up_DataTab']))
    {
      upgrade_data_table($conn);
        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= 'index.php';
        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }

$document_class = get_color_scheme($conn);    

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
    $chart_filename_08_1 = get_field_from_sql($conn,$file,"chart_filename_08_1");
    $chart_filename_09 = get_field_from_sql($conn,$file,"chart_filename_09");
    $chart_filename_10 = get_field_from_sql($conn,$file,"chart_filename_10");
    $chart_filename_11 = get_field_from_sql($conn,$file,"chart_filename_11");
    $chart_filename_12 = get_field_from_sql($conn,$file,"chart_filename_12");
    $chart_filename_13 = get_field_from_sql($conn,$file,"chart_filename_13");
    $chart_filename_14 = get_field_from_sql($conn,$file,"chart_filename_14");
    $chart_filename_15 = get_field_from_sql($conn,$file,"chart_filename_15");
    $chart_filename_16 = get_field_from_sql($conn,$file,"chart_filename_16");
    $chart_filename_17 = get_field_from_sql($conn,$file,"chart_filename_17");
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
    $send_rdi_end = get_field_from_sql($conn,$file,"send_rdi_end");
    $no_data = get_field_from_sql($conn,$file,"no_data"); 
    $header_initialgravity = get_field_from_sql($conn,$file,"header_initialgravity");
    $change_history = get_field_from_sql($conn,$file,"change_history");
    $help = get_field_from_sql($conn,$file,"help");
    $settings_header = get_field_from_sql($conn,$file,"settings_header");
    $current_data = get_field_from_sql($conn,$file,"current_data");
    $expert_settings = get_field_from_sql($conn,$file,"expert_settings");
    $upgrade_settings = get_field_from_sql($conn,$file,"upgrade_settings");
    $upgrade_strings = get_field_from_sql($conn,$file,"upgrade_strings");
    $upgrade_warning = get_field_from_sql($conn,$file,"upgrade_warning");
    $installed_version = get_field_from_sql($conn,$file,"installed_version");
    $available_version = get_field_from_sql($conn,$file,"available_version");
    $upgrade_data_table = get_field_from_sql($conn,$file,"upgrade_data_table");
    $show_archive = get_field_from_sql($conn,$file,"show_archive");
    $send_comment = get_field_from_sql($conn,$file,"send_comment");
    $comment_text = get_field_from_sql($conn,$file,"comment_text");
    $header_deltagravity = get_field_from_sql($conn,$file,"header_deltagravity");


    $hours_ago = 12;

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

// get information if TCP server is running (if pid file is written)
    $pids=''; 
    $running=false;
    $stat = exec("systemctl is-active ispindle-srv");
    if (file_exists( "/var/run/ispindle-srv.pid" )) {
        $pid= (shell_exec("cat /var/run/ispindle-srv.pid"));
        $running = posix_getpgid(intval($pid));
    }
// get information if TCP server is running (if no pid file is written)
    else {
         $running=true;
         $pid= (shell_exec(" ps axf | grep iSpindle.py | grep -v grep| awk '{print $1}'"));
         if($pid == '') {
             $running = false;
         }
    }
    if ($running) {
        $iSpindleServerRunning = $server_running . $pid;
    } elseif ($stat == "activating") {
              $iSpindleServerRunning = $server_running;
    } else {
        $iSpindleServerRunning = $server_not_running;
    }

// get installed strings table version
    $installed_strings_version="N/A";
    $q_sql="Select Field from Strings WHERE File = 'Version'";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
    $rows = mysqli_num_rows($result);
    if($rows > 0) {
        $row = mysqli_fetch_array($result);
        $installed_strings_version= $row[0];
    }
// get installed settings table version
    $installed_settings_version="N/A";
    $q_sql="Select value from Settings WHERE Section = 'VERSION'";
    $result = mysqli_query($conn, $q_sql) or die(mysqli_error($conn));
    $rows = mysqli_num_rows($result);
    if($rows > 0) {
        $row = mysqli_fetch_array($result);
        $installed_settings_version = $row[0];
    }

// get all spindle names to be displayed in form that have submitted data within the timeframe of $daysago
    $sql_q = "SELECT DISTINCT Name FROM Data WHERE Timestamp > date_sub(NOW(), INTERVAL ".$daysago." DAY) ORDER BY Name";
    $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
    $len = mysqli_num_rows($result);
    $result1=mysqli_query($conn, $sql_q) or die(mysqli_error($conn)); 

//  check if data table has column recipe_id
    $q_sql1 = "SHOW COLUMNS FROM Data WHERE FIELD LIKE 'Recipe_ID'";
    $lines = mysqli_query($conn, $q_sql1) or die(mysqli_error($conn));
    $exists = mysqli_num_rows($lines);

write_log('Recipe_ID Column exixst: '. $exists);

//  check if data table has column recipe_id
    $q_sql1 = "SHOW TABLES LIKE 'iGauge'";
    $lines = mysqli_query($conn, $q_sql1) or die(mysqli_error($conn));
    $iGauge_exists = mysqli_num_rows($lines);

write_log('iGauge Table exixst: '. $iGauge_exists);

?>

<!DOCTYPE html>
<html>
<head>
    <title>RasPySpindel Homepage</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"> 
    <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">
</head>
<script type="text/javascript">
// Function to hide or display elements. Used for recipe name. Only displayed if reset_now is selected in options
    function einblenden(){
        var select = document.getElementById('chart_filename').selectedIndex;
        if(select == 12){
           document.getElementById('ResetNow').style.display = "block"; // Name for recipe
           document.getElementById('send').style.display = "block"; //send_reset
           document.getElementById('show').style.display = "none"; // show diagram button
           document.getElementById('diagrams').style.display = "none";
           document.getElementById('archive').style.display = "none";
           document.getElementById('end').style.display = "none";
           document.getElementById('commentfield').style.display = "none";
           document.getElementById('comment').style.display = "none";


        }
        else if(select == 13){
           document.getElementById('ResetNow').style.display = "none"; // Name for recipe
           document.getElementById('send').style.display = "none"; //send_reset
           document.getElementById('show').style.display = "none"; // show diagram button
           document.getElementById('diagrams').style.display = "none";
           document.getElementById('archive').style.display = "none";
           document.getElementById('end').style.display = "block";
           document.getElementById('commentfield').style.display = "none";
           document.getElementById('comment').style.display = "none";


        }
        else if(select == 14){
           document.getElementById('ResetNow').style.display = "none"; // Name for recipe
           document.getElementById('send').style.display = "none"; //send_reset
           document.getElementById('show').style.display = "none"; // show diagram button
           document.getElementById('diagrams').style.display = "none";
           document.getElementById('archive').style.display = "none";
           document.getElementById('end').style.display = "none";
           document.getElementById('commentfield').style.display = "block";
           document.getElementById('comment').style.display = "block";


        }


        else {
            document.getElementById('ResetNow').style.display = "none";        
            document.getElementById('send').style.display = "none";
            document.getElementById('show').style.display = "block";
            document.getElementById('diagrams').style.display = "block";
            document.getElementById('archive').style.display = "block";
            document.getElementById('end').style.display = "none";
            document.getElementById('commentfield').style.display = "none";
            document.getElementById('comment').style.display = "none";

        }
    }

    function activate_expert(){
        var checkBox = document.getElementById("expert");
        var settings = document.getElementById("expert_settings");

        if (checkBox.checked == true){
            settings.style.display = "block";
            } else {
               settings.style.display = "none";
              }
        }    

</script>


<?php
echo "<body class='$document_class'>";
$action=htmlentities($_SERVER['PHP_SELF']);
echo "<form name='main' action='$action' method='post'>";
echo "<h1>RasPySpindel</h1>";
echo "<h3>$diagram_selection  $daysago</h3>";
    if ($len != 0){
        echo "<select id='ispindel_name' name = 'ispindel_name'>";
        while($row = mysqli_fetch_assoc($result))
            {
                $iSpindle_Name=$row['Name'];
                echo "<option value = '$iSpindle_Name'>$iSpindle_Name";
            }
        echo "</option>";
    if ($iGauge_exists != 0) {
        $sql_q = "SELECT DISTINCT Name FROM iGauge
        WHERE Timestamp > date_sub(NOW(), INTERVAL ".$daysago." DAY)
        ORDER BY Name";
        $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
        while($row = mysqli_fetch_assoc($result)){
            $iGauge_name=$row['Name'];
            echo"<option value = '$iGauge_Name'>$iGauge_Name";
        }
    }
    echo"</option>";
    echo"</select>";

//select options for diagrams to be loaded 
echo "<select id='chart_filename' name='chart_filename' onchange='einblenden()'>";
echo "<option value='status.php' selected>$chart_filename_01</option>";
echo "<option value='battery.php'>$chart_filename_02</option>";
echo "<option value='wifi.php'>$chart_filename_03</option>";
echo "<option value='plato4.php'>$chart_filename_04</option>";
echo "<option value='plato4_ma.php'>$chart_filename_05</option>";
echo "<option value='angle.php'>$chart_filename_06</option>";
echo "<option value='angle_ma.php'>$chart_filename_07</option>";
echo "<option value='plato.php'>$chart_filename_08</option>";
echo "<option value='plato_ma.php'>$chart_filename_08_1</option>";
echo "<option value='batterytrend.php'>$chart_filename_12</option>";
echo "<option value='svg_ma.php'>$chart_filename_10</option>";
echo "<option value='plato4_delta.php'>$chart_filename_11</option>";
//echo "<option value='plato6.php'>Easy delta</option>";
echo "<option value='reset_now.php'>$chart_filename_09</option>";
echo "<option value='ferment_end.php'>$chart_filename_14</option>";
echo "<option value='add_comment.php'>$chart_filename_15</option>";
if ($iGauge_exists != 0) {
    echo "<option value='iGauge.php'>$chart_filename_16</option>";
    echo "<option value='reset_now_igauge.php'>$chart_filename_17</option>";
}
?>
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

<div id="commentfield" style="display: none;">
<p><?php echo($comment_text)?>
<input type = "text" name = "comment_text" /> </p>
</div>


<div id="show" style="display: block;">
<span title="<?php echo($show_diagram)?>"><input type = "submit" id='diagram' name = "Go" value = "<?php echo($show_diagram)?>"></span>
</div>
<div id="send" style="display: none;">
<span title="<?php echo($send_reset)?>"><input type = "submit" id='reset' name = "Go" value = "<?php echo($send_reset)?>"></span>
</div>
<div id="end" style="display: none;">
<span title="<?php echo($send_rdi_end)?>"><input type = "submit" id='rdi_end' name = "Go" value = "<?php echo($send_rdi_end)?>"></span>
</div>
<div id="comment" style="display: none;">
<span title="<?php echo($send_comment)?>"><input type = "submit" id='send_comment' name = "Go" value = "<?php echo($send_comment)?>"></span>
</div>


<?php
# endif len !=0
}
else {
    echo sprintf($no_data, $daysago);
    echo "<br/>";
    echo"<input type = 'number' name = 'changedefaultdays' min = '1' max = '365' step = '1' value = '$daysago'>";
    echo($days_history);
    echo "<br/><br/>";

    echo "<div id='change' style='display: block;'>";
    echo "<span title='$change_history'><input type = 'submit' id='changehistory' name = 'Change' value = '$change_history'></span>";
    echo "</div>";

}
?>
<br/>
<div id="archive" style="display: block;">
<span title="<?php echo($show_archive)?>"><input type = "submit" id='archive' name = "archive" value = "<?php echo($show_archive)?>"></span>
</div>
<br/>
<br/>
<?php

// if a section is selected (default is 0), table will be defined
// Database entries for parameter, value and description of defined language will be displayed for selected section
// name of input field gets unique id (combination of section and parameter). This is used to identify parameter value during _POST['GO']
if ($len !=0 ){
    echo "<h2>$current_data</h2>"; 
    echo "<table class='$document_class'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Device</th>";
    echo "<th>$header_time</th>";
    echo "<th>$header_recipe</th>";
    echo "<th>$header_angle [°]</th>";
    echo "<th>$header_temperature [°C]</th>";
    echo "<th>$header_initialgravity</th>";
    echo "<th>$header_density</th>";
    echo "<th>$header_deltagravity ($hours_ago h)</th>";
    echo "<th>$header_svg</th>";
    echo "<th>$header_alcohol</th>";
    echo "<th>$header_battery [Volt]</th>";
    echo "<th>$header_wifi [dB]</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    while($row = mysqli_fetch_assoc($result1) ) {
        $show_device=get_settings_from_sql($conn, 'GENERAL', $row['Name'],'SHOWSUMMARY'); 
        if ($show_device == 1){
        list($iscalibrated, $time, $temperature, $angle, $battery, $recipe, $dens, $RSSI) = getlastValuesPlato4($conn, $row['Name']);
        list($iscalibrated, $time_ago, $temperature_ago, $angle_ago, $battery_ago, $recipe_ago, $dens_ago, $RSSI_ago) = getValuesHoursAgoPlato4($conn, $row['Name'], $time, $hours_ago);
        $gravity=getInitialGravity($conn, $row['Name']);
        if ($gravity[0]==1){
        $initialgravity=$gravity[1];
        #calculate apparent attenuation
        $SVG=($initialgravity - $dens)*100/$initialgravity;
        #real density differs from aparent density
        $realdens = 0.1808 * $initialgravity + 0.8192 * $dens;
        # calculate alcohol by weigth and by volume (fabbier calcfabbier calc for link see above)
        $ABV = (( 100 * ($realdens - $initialgravity) / (1.0665 * $initialgravity- 206.65))/0.795);
        $Ddens = $dens-$dens_ago;
        }
        else {
        $initialgravity=0;
        $SVG=0;
        }
        echo "<tr>";
        echo "<td style='text-align: left'><b>" . $row['Name'] . "</b></td>";
        echo "<td style='text-align: left'>" . date("Y-m-d\ H:i:s\ ", $time) . "</td>";
        echo "<td style='text-align: left'>" .  $recipe . "</td>";
        echo "<td>" . number_format($angle,1) . "</td>";
        echo "<td>" . number_format($temperature,1) . "</td>";
        echo "<td>" . number_format($initialgravity,1) . "</td>";
        echo "<td>" . number_format($dens,1) . "</td>";
        echo "<td>" . number_format($Ddens,1) . "</td>";
        echo "<td>" . number_format($SVG,1) . "</td>";
        echo "<td>" . number_format($ABV,1) . "</td>";
        echo "<td>" . number_format($battery,2) . "</td>";
        echo "<td>" . number_format($RSSI,0) . "</td>";
        echo "</tr>";
        
        }
    }
echo "</tbody>";
echo "</table>";
}
?>
<br/>
<br/>
<h2><?php echo $settings_header; ?></h2>


<span title="<?php echo($calibrate_spindle)?>"> <input type = "submit" id='calibrate' name = "Cal" value="<?php echo($calibrate_spindle)?>"></span>
<span title="<?php echo($server_settings)?>"><input type = "submit" id='settings' name = "Set" value = "<?php echo($server_settings)?>"></span>
<br/>
<br/>
<input type = "checkbox" id="expert" value="0"  onchange="activate_expert()">
<?php echo $expert_settings; ?> 
<br/>
<br/>
<div id="expert_settings" style="display: none;">
<span title="<?php echo($upgrade_strings)?>"> <input type = "submit" id='up_strings' name = "Up_Str" value="<?php echo($upgrade_strings)?>"></span>
<?php echo($installed_version)?> <?php echo($installed_strings_version)?> | <?php echo($available_version)?> <?php echo(LATEST_STRINGS_TABLE)?>
<br/>
<br/>
<br/> <b><?php echo($upgrade_warning)?></b><br/><br/>
<span title="<?php echo($upgrade_settings)?>"><input type = "submit" id='up_settings' name = "Up_Set" value = "<?php echo($upgrade_settings)?>"></span>
<?php echo($installed_version)?> <?php echo($installed_settings_version)?> | <?php echo($available_version)?> <?php echo(LATEST_SETTINGS_TABLE)?>

<?php if ($exists == 0) { ?>
<br/>
<br/>
<span title="<?php echo($upgrade_data_table)?>"><input type = "submit" id='up_data_table' name = "Up_DataTab" value = "<?php echo($upgrade_data_table)?>"></span>
<?php } ?>
</div>



<footer>
<?php echo"<div><a href='help.php' title='$help'>$help</a></div>"; ?>
<br/><?php echo($iSpindleServerRunning)?>
<!-- <div>Icons made by <a href="https://www.flaticon.com/authors/prosymbols" title="Prosymbols">Prosymbols</a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a></div> -->
</footer>
</form>
</body>
</html>
