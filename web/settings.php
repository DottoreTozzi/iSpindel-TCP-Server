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

// load information for sql connection
if ((include_once './config/common_db_config.php') == FALSE){
       include_once("./config/common_db_default.php");
    }
// load db query functions
include_once("include/common_db_query.php");

// Get fields from database in language selected in settings
$file = "settings";
$window_alert_update = get_field_from_sql($conn,$file,"window_alert_update");
$select_section = get_field_from_sql($conn,$file,"select_section");
$header = get_field_from_sql($conn,$file,"header");
$send = get_field_from_sql($conn,$file,"send");
$stop = get_field_from_sql($conn,$file,"stop");
$description = get_field_from_sql($conn,$file,"description");
$problem = get_field_from_sql($conn,$file,"problem");

// if parameter section not set, '0' for first section in config is default to be displayed
if(!isset($_GET['section'])) $_GET['section'] = '0'; else $_GET['section'] = $_GET['section'];

$current_section=$_GET['section'];

// self called: if back button is selected, landing page is loaded
if (isset($_POST['Stop']))
    {
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "index.php";
        // open the page
        header("Location: ".$url);

    }

// self caled function: if send button is selected, values will be written to database
if (isset($_POST['Go']))
    {
        $current_section = $_POST['current_section'];
        $current_id = $_POST['current_id'];
        // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
        mysqli_set_charset($conn, "utf8");
        // select only db parameters for corresponding section, where no german description is available (such parameters are used for internal purposes e.g. sendmail)
        $sql_q = "SELECT * FROM Settings WHERE Description_DE <> '' AND Section = '" . $current_section . "' ORDER BY Parameter";
        $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
        // go through every parameter for dselected section
        // combination of Section and parameter is used as unique index for _POST values from table in html section
        while($row = mysqli_fetch_assoc($result) ) {
            $section = $row['Section'];
            $parameter = $row['Parameter'];
            $value = $_POST[$row['Section'] . "_" . $row['Parameter']];
            $Update=UpdateSettings($conn, $section, $parameter, $value);
            // in case of problem with database update, diyplay corresponding section and parameter
            if(!$Update) {
                echo $problem . " " . $section . ": " . $parameter . ": " . $value;
                exit;
            }
        }
        unset($result, $sql_q);
        // reload page with current section information
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "settings.php?section=".$current_id;
        // open the page
        header("Location: ".$url);
    }
    // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
    mysqli_set_charset($conn, "utf8");
    // get language setting from database to define the description field displayed in the table
    $sql_language = mysqli_query($conn, "SELECT value FROM Settings WHERE Section = 'GENERAL' AND Parameter = 'LANGUAGE'") or die(mysqli_error($conn));
    $LANGUAGE = mysqli_fetch_array($sql_language);
    $DESCRIPTION = "Description_".$LANGUAGE[0]; 
    
    // Load all parameers and descriptions for rows where german description is not empty
    // rows with empty description are for internal use (e.g. used by sendmail)
    $sql_q = "SELECT * FROM Settings WHERE Description_DE <> '' ORDER BY Section, Parameter";
    // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
    mysqli_set_charset($conn, "utf8");
    $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
    
    // Load all sections to be displayed in the selection field of the table
    $sql_q1 = "SELECT DISTINCT Section FROM Settings WHERE Description_DE <> ''";
    // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
    mysqli_set_charset($conn, "utf8");
    $result1 = mysqli_query($conn, $sql_q1) or die(mysqli_error($conn));
    
    // Define array for sections to be displayed in the select field 
    $sections = array(); 
    while($row_s = mysqli_fetch_assoc($result1) ) {
    $sections[] = $row_s['Section'];
    }

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RasPySpindel Settings</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">

<script type="text/javascript">
    // alert window will be displayed when values are submitted to database
    function target_popup(form) {
        window.alert('<?php echo $window_alert_update; ?>');
    }
    
    // function to reload page when section is changed -> different section parameters will be displayed and can be changed
    function reload_page() {
        var section = document.getElementById('section_name').selectedIndex;
        var variable = '?section='.concat(section);
        var url = "http://";
        var server = window.location.hostname;
        var path = window.location.pathname;
        var full_path = url.concat(server).concat(path).concat(variable);
        window.open(full_path,"_self");
    }
</script>

</head>
<body bgcolor="#E6E6FA">
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
<a href=/iSpindle/index.php><img src=include/icons8-home-26.png></a>
<h1>RasPySpindel Settings</h1>
<h3><?php echo $select_section; ?></h3>

<!-- 
    All sections from array are listed in a select box
    If selection i changed, reload_page function is called to display parameters for selected section    
-->
<p>Section:
<select id = 'section_name' name = 'section_name' onchange="reload_page(this)">
        <?php
            $i = 0;
            $max = count ($sections);
            while($i < $max ) {
                if ($i <> $_GET['section']) {
                    echo'<option value = "' . $sections[$i].'" name = "' . $sections[$i].'">';
                    echo($sections[$i]);
                    echo"</option>\n";
                }
                else {
                    echo'<option value = "' . $sections[$i] .'" selected name = "' . $sections[$i].'">';
                    echo($sections[$i]);
                    echo"</option>\n";
                }
            $i = ++$i;
            }
        ?>
</select>
</p>

<?php 

// if a section is selected (default is 0), table will be defined
// Database entries for parameter, value and description of defined language will be displayed for selected section
// name of input field gets unique id (combination of section and parameter). This is used to identify parameter value during _POST['GO']
if ($_GET['section']<>''){ 

echo "<table border='0'>";
echo "<tr>";
echo "<td><b>Parameter</b></td>";
echo "<td><b>Value</b></td>";
echo "<td><b>$description</b></td>";
echo "</tr>";
    $InputWidth = 15;
    while($row = mysqli_fetch_assoc($result) ) {
        if ($row['Section'] == $sections[$_GET['section']]){
        echo "<tr>";
        echo "<td>" . $row['Parameter'] . "</td>";
        echo "<td><input type='text' name = '" . $row['Section'] . "_" . $row['Parameter'] . "' size='" . $InputWidth . "' required='required' value='" . $row['value']  . "'></td>";
        echo "<td>" . $row[$DESCRIPTION] . "</td>";
        echo "</tr>\n";
    }}
echo "</table>";
}
?>

<br />
<br />
<!--
    hidden fields to define parameters that are used when submit button is selected to write data to database
-->

<input type = "hidden" name="current_section" value="<?php echo $sections[$_GET['section']]; ?>">
<input type = "hidden" name="current_id" value="<?php echo $_GET['section']; ?>">

<input type = "submit" name = "Go" value = "<?php echo $send; ?>" onclick="target_popup(this)">
<input type = "submit" name = "Stop" value = "<?php echo $stop; ?>">

<br />
        
</form>

