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

        // open the page
        header("Location: ".$url);
        unset($result, $sql_q);
        exit;
    }
    
    // Called from browser, showing form
    include_once("include/common_db.php");
    
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

<select name = chart_filename>
        <option value="status.php" selected>Status (Batterie, Winkel, Temperatur)</option>
        <option value="battery.php">Batteriezustand</option>
        <option value="wifi.php">Netzwerk Empfangsqualität</option>
        <option value="plato4.php">Extrakt und Temperatur (RasPySpindel)</option>
        <option value="plato4_ma.php">Extrakt und Temperatur (RasPySpindel), Geglättet</option>
        <option value="angle.php">Tilt und Temperatur</option>
        <option value="angle_ma.php">Tilt und Temperatur, Geglättet</option>
        <option value="plato.php">Extrakt und Temperatur (iSpindel Polynom)</option>
        <option value="reset_now.php">Gärbeginn Zeitpunkt setzen</option>
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

<input type = "submit" name = "Go" value = "Anzeigen">
<br />
</form>
</body>
</html>

