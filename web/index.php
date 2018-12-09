<?php
    
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
	// Database config parameters wiull be pulled from different directory. User can use personalized config file: common_db_config.php in config directory
	// If personalized file does not exist, default config will be loaded: common_db_default.php
	// Added function to display input field for Sudname on this page only if reset_now.php is selected
	// If Sudname is entered, it is transferred to the database
	// Added chart to display battery and Wifi strength trend
    
    // Self-called by submit button?
    if (isset($_POST['Go']))
    {
        // construct url
        // establish path by the current URL used to invoke this page
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= $_POST["chart_filename"];
        $url .="?name=".$_POST["ispindel_name"];
        $url .="&days=".$_POST["days"];
        $url .="&reset=".$_POST["fromreset"];
        $url .="&recipe=".$_POST["recipename"];

        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }
    
    // Called from browser, showing form
    if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
    
}

    
    // "Days Ago parameter set?
    if(!isset($_GET['days'])) $_GET['days'] = 0; else $_GET['days'] = $_GET['days'];
    $daysago = $_GET['days'];
    if($daysago == 0) $daysago = defaultDaysAgo;
    
    // query database for available (active) iSpindels
    $sql_q = "SELECT DISTINCT Name FROM Data
        WHERE Timestamp > date_sub(NOW(), INTERVAL ".$daysago." DAY)
        ORDER BY Name";
    $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
?>

<!DOCTYPE html>
<html>
<head>
    <title>RasPySpindel Homepage</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">

<script type="text/javascript">
    function einblenden(){
        var select = document.getElementById('chart_filename').selectedIndex;
        if(select == 8 ) document.getElementById('ResetNow').style.display = "block";
        else document.getElementById('ResetNow').style.display = "none";        
    }
</script>
</head>
<body bgcolor="#E6E6FA">
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
<h1>RasPySpindel</h1>
<h3>Diagramm Auswahl <?php echo($daysago)?> Tage</h3>

<select name = ispindel_name>
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

<select id="chart_filename" name='chart_filename' onchange="einblenden()">
        <option value="status.php" selected>Status (Batterie, Winkel, Temperatur)</option>
        <option value="battery.php">Batteriezustand</option>
        <option value="wifi.php">Netzwerk Empfangsqualität</option>
        <option value="plato4.php">Extrakt und Temperatur (RasPySpindel)</option>
        <option value="plato4_ma.php">Extrakt und Temperatur (RasPySpindel), Geglättet</option>
        <option value="angle.php">Tilt und Temperatur</option>
        <option value="angle_ma.php">Tilt und Temperatur, Geglättet</option>
        <option value="plato.php">Extrakt und Temperatur (iSpindel Polynom)</option>
        <option value="reset_now.php">Gärbeginn Zeitpunkt setzen</option>i
        <option value="batterytrend.php">Verlauf Batteriespannung/WiFi anzeigen</option>	
</select>

<br />
<br />

<!-- "hidden" checkbox to make sure we have a response here and not just send "null" -->
<input type = "hidden" name="fromreset" value="0">
<input type = "checkbox" name="fromreset" value="1">
Daten seit zuletzt gesetztem "Reset" Flag

<br />
oder:
<input type = "number" name = "days" min = "1" max = "365" step = "1" value = "<?php echo($daysago)?>">
Tage Historie
<br />
<br />

<div id="ResetNow" style="display: none;">
<p>Optional Sudnamen eingeben: <input type = "text" name = "recipename" /></p>
</div>

<input type = "submit" name = "Go" value = "Anzeigen">
<br />

        
        
</form>

</body>
</html>

