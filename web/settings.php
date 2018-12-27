<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
    
// Settings Page to update iSpindel-TCP server settings in SQL Database
// - Email alarm settings for instance have to be updated by each user (email,server, port, password)
// - For now, this is in German. The settings table is prepared for comments in english langage
// - Script pulls available settings from Database and displys them. 
// - User can modify the individual settings and write them back to the database
// - Settings database is also used for status of sent email.
// - In this case no Description is used and these values are not shown on the settings front end
//
// Future enhancements could/should include:
// - Selection of Parameters by Section (most likely with Java Script Function)
//
// Self-called by submit button. Calls landing page on stop

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
        $sql_q = "SELECT * FROM Settings WHERE Description_DE <> ''";
        $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
        while($row = mysqli_fetch_assoc($result) ) {
            $section = $row['Section'];
            $parameter = $row['Parameter'];
            $value = $_POST[$row['Section'] . "_" . $row['Parameter']];
            $Update=UpdateSettings($conn, $section, $parameter, $value);
            if(!$Update) {
                echo"Probleme beim Schreiben der Settings " . $section . ": " . $parameter . ": " . $value;
                exit;
            }
        }
        unset($result, $sql_q);
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "settings.php";
        // open the page
        header("Location: ".$url);
        // exit;
    }


    $sql_q = "SELECT * FROM Settings WHERE Description_DE <> '' ORDER BY Section, Parameter";
    $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));

    $sql_q1 = "SELECT DISTINCT Section FROM Settings WHERE Description_DE <> ''";
    $result1=mysqli_query($conn, $sql_q1) or die(mysqli_error($conn));


?>

<!DOCTYPE html>
<html>
<head>
    <title>RasPySpindel Homepage</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">

<script type="text/javascript">
    function einblenden() {
        var select = document.getElementById('chart_filename').selectedIndex;
        if (select == 8) {
            document.getElementById('ResetNow').style.display = "block";
        } else {
            document.getElementById('ResetNow').style.display = "none";
        }
    }

    function target_popup(form) {
        window.alert('Aktualisiere Settings in Datenbank');
    }

</script>
</head>

<body bgcolor="#E6E6FA">
<h1>RasPySpindel</h1>
<h3>Settings Section Auswahl</h3>
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">


<!-- Preparation for later selection of sectionswise change of settings
<select id="section_name" name = 'section_name'>
        <?php
            echo'<option value="" selected disabled hidden>Choose here</option>';
            while($row_s = mysqli_fetch_assoc($result1)) {
                echo'<option value = "' . $row_s['Section'] .'" >';
                echo($row_s['Section']);
                echo"</option>";
            }
        ?>
</select>
-->


<table border="0">
<tr>
<td><b>Section</b></td>
<td><b>Parameter</b></td>
<td><b>Value</b></td>
<td><b>Beschreibung</b></td>
</tr>
<?php
    $InputWidth = 80;
    while($row = mysqli_fetch_assoc($result) ) {
        echo "<tr>";
        echo "<td>" . $row['Section'] . "</td>";
        echo "<td>" . $row['Parameter'] . "</td>";
        echo "<td><input type='text' name = '" . $row['Section'] . "_" . $row['Parameter'] . "' size='" . $InputWidth . "' value='" . $row['value']  . "'></td>";
        echo "<td>" . $row['Description_DE'] . "</td>";
        echo "</tr>\n";
    }
?>
</table>

<br />
<br />

<div id="ResetNow" style="display: none;">
</div>

<input type = "submit" name = "Go" value = "In DB schreiben" onclick="target_popup(this)">
<input type = "submit" name = "Stop" value = "Abbrechen">

<br />
        
</form>

