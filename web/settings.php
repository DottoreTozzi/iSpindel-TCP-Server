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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../PHPMailer/src/Exception.php";
require "../PHPMailer/src/PHPMailer.php";
require "../PHPMailer/src/SMTP.php";

// Get fields from database in language selected in settings
$file = "settings";
$window_alert_update = get_field_from_sql($conn,$file,"window_alert_update");
$select_section = get_field_from_sql($conn,$file,"select_section");
$header = get_field_from_sql($conn,$file,"header");
$send = get_field_from_sql($conn,$file,"send");
$stop = get_field_from_sql($conn,$file,"stop");
$description = get_field_from_sql($conn,$file,"description");
$problem = get_field_from_sql($conn,$file,"problem");
$delete_device = get_field_from_sql($conn,$file,"delete_device");
$add_device = get_field_from_sql($conn,$file,"add_device");
$testmail = get_field_from_sql($conn,$file,"testmail");
$export_data = get_field_from_sql($conn,$file,"export_data");

// if parameter section not set, '0' for first section in config is default to be displayed
if(!isset($_GET['section'])) $_GET['section'] = '0'; else $_GET['section'] = $_GET['section'];
if(!isset($_GET['device'])) $_GET['device'] = '0'; else $_GET['device'] = $_GET['device'];

$current_section=$_GET['section'];
$current_device=$_GET['device'];

// self called: if back button is selected, landing page is loaded
if (isset($_POST['Stop']))
    {
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "index.php";
        // open the page
        header("Location: ".$url);

    }

if (isset($_POST['Export_Data']))
    {
        export_data_table("Data","iSpindle_Data.sql");
    }


// Function to send testmail
if (isset($_POST['Testmail']))
    {
# retrieve email settings from Database (Global and not per device)
$fromaddr = get_settings_from_sql($conn, 'EMAIL','GLOBAL','FROMADDR');
$toaddr = get_settings_from_sql($conn, 'EMAIL','GLOBAL','TOADDR');
$passwd = get_settings_from_sql($conn, 'EMAIL','GLOBAL','PASSWD');
$smtpserver = get_settings_from_sql($conn, 'EMAIL','GLOBAL','SMTPSERVER');
$smtpport = get_settings_from_sql($conn, 'EMAIL','GLOBAL','SMTPPORT');
$debug = get_settings_from_sql($conn, 'EMAIL','GLOBAL','ENABLEDEBUG');

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    //Server settings
    $mail->SMTPDebug = $debug;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $smtpserver;                   // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $fromaddr;              // SMTP username
    $mail->Password = $passwd;                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = $smtpport;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom($fromaddr);          //This is the email your form sends From
    $mail->addAddress($toaddr); // Add a recipient address

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Testmail from iSpindle TCP Server';
    $mail->Body    = 'Testmail has been sent from your iSpindle Server';

    $mail->send();
//    echo 'Message has been sent';

    } 
catch (Exception $e) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}


// self caled function: if delete button is selected, individual settings for selected device will be deleted
if (isset($_POST['Delete']))
    {
        $current_section = $_POST['current_section'];
        $current_Sid = $_POST['current_Sid'];
        $current_device = $_POST['current_device'];
        $current_Did = $_POST['current_Did'];
        // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
        mysqli_set_charset($conn, "utf8");
        // delete selected device from individual settings
        $sql_q = "DELETE FROM Settings WHERE DeviceName = '". $current_device . "'";
        $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
        unset($result, $sql_q);
        // reload page with current section information
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "settings.php?section=".$current_Sid . "&device=0"; 
        // open the page
        header("Location: ".$url);
    }

// self caled function: if add button is selected, default settings for selected device will be copied and can be modified later individually
if (isset($_POST['Add']))
    {
        $current_section = $_POST['current_section'];
        $current_Sid = $_POST['current_Sid'];
        $current_device = $_POST['current_device'];
        $current_Did = $_POST['current_Did'];
        $add_device = $_POST['add_device'];
        // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
        mysqli_set_charset($conn, "utf8");
        // copy default settings for selected device and replace _DEFAULT with device name
        $Add=CopySettingsToDevice($conn, $add_device);
        unset($Add);
        // reload page with current section information
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "settings.php?section=".$current_Sid . "&device=" . $current_Did;
        // open the page
        header("Location: ".$url);
    }


// self caled function: if send button is selected, values will be written to database
if (isset($_POST['Go']))
    {
        $current_section = $_POST['current_section'];
        $current_Sid = $_POST['current_Sid'];
        $current_device = $_POST['current_device'];
        $current_Did = $_POST['current_Did'];
        // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
        mysqli_set_charset($conn, "utf8");
        // select only db parameters for corresponding section, where no german description is available (such parameters are used for internal purposes e.g. sendmail)
        $sql_q = "SELECT * FROM Settings WHERE Description_DE <> '' AND Section = '" . $current_section . "' AND DeviceName = '" . $current_device . "' ORDER BY Parameter";
        $result=mysqli_query($conn, $sql_q) or die(mysqli_error($conn));
        // go through every parameter for dselected section
        // combination of Section and parameter is used as unique index for _POST values from table in html section
        while($row = mysqli_fetch_assoc($result) ) {
            $section = $row['Section'];
            $device = $row['DeviceName'];
            $parameter = $row['Parameter'];
            $value = $_POST[$row['Section'] . "_" . $row['Parameter']];
            $Update=UpdateSettings($conn, $section, $device, $parameter, $value);
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
        $url .= "settings.php?section=".$current_Sid . "&device=" . $current_Did;
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
    $sql_q = "SELECT * FROM Settings WHERE Description_DE <> '' ORDER BY DeviceName, Section, Parameter";
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

    // Load all devices from settings table to be displayed in the device field of the table
    $sql_q2 = "SELECT DISTINCT DeviceName FROM Settings WHERE Description_DE <> ''";
    // set utf-8 charset for DB connection to ensure correct display of special characters like umlauts
    mysqli_set_charset($conn, "utf8");
    $result2 = mysqli_query($conn, $sql_q2) or die(mysqli_error($conn));

    // Define array for sections to be displayed in the select field
    $devices = array();
    while($row_d = mysqli_fetch_assoc($result2) ) {
        $devices[] = $row_d['DeviceName'];
    }

    // Load List of all availble devices from Database
    $sql_q3 = "SELECT max(Timestamp), Name FROM Data WHERE NOT Name IN (SELECT DISTINCT DeviceName FROM Settings WHERE Description_DE <> '') GROUP BY Name";
    $result3 = mysqli_query($conn, $sql_q3) or die(mysqli_error($conn));

    $spindle_list = array();
    while($row_s = mysqli_fetch_assoc($result3) ) {
        $spindle_list[] = $row_s['Name'];
    }


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RasPySpindel Settings</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">
    <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">

<script type="text/javascript">
    // alert window will be displayed when values are submitted to database
    function target_popup(form) {
        window.alert('<?php echo $window_alert_update; ?>');
    }

    
    // function to reload page when section is changed -> different section parameters will be displayed and can be changed
    function reload_page() {
        var section = document.getElementById('section_name').selectedIndex;
        var variable_S = '?section='.concat(section);
        var device = document.getElementById('device_name').selectedIndex;
        var variable_D = '&device='.concat(device);
        var url = "http://";
        var server = window.location.hostname;
        var path = window.location.pathname;
        var full_path = url.concat(server).concat(path).concat(variable_S).concat(variable_D);
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
<p>Device:
<select id = 'device_name' name = 'device_name' onchange="reload_page(this)">
        <?php
            $selected_device='';
            $i = 0;
            $max = count ($devices);
            while($i < $max ) {
                if ($i <> $_GET['device']) {
                    echo'<option value = "' . $devices[$i].'" name = "' . $devices[$i].'">';
                    echo($devices[$i]);
                    echo"</option>\n";
                }
                else {
                    echo'<option value = "' . $devices[$i] .'" selected name = "' . $devices[$i].'">';
                    echo($devices[$i]);
                    echo"</option>\n";
                    $selected_device=$devices[$i];

                }
            $i = ++$i;
            }
            if ($selected_device != "_DEFAULT" && $selected_device != "GLOBAL"){
                echo "<div id='delete' style='display: block;' >";
                echo "<input type = 'submit' name = 'Delete' value = '" . $delete_device . "' >";
                echo "</div>";
            }
        ?>
</select>

</p>


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
                    $selected_section=$sections[$i];
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
        if ($row['Section'] == $sections[$_GET['section']] and $row['DeviceName'] == $devices[$_GET['device']] ) {
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
<br />


<!--
    hidden fields to define parameters that are used when submit button is selected to write data to database
-->

<input type = "hidden" name="current_section" value="<?php echo $sections[$_GET['section']]; ?>">
<input type = "hidden" name="current_Sid" value="<?php echo $_GET['section']; ?>">
<input type = "hidden" name="current_device" value="<?php echo $devices[$_GET['device']]; ?>">
<input type = "hidden" name="current_Did" value="<?php echo $_GET['device']; ?>">

<input type = "submit" name = "Go" value = "<?php echo $send; ?>" onclick="target_popup(this)">
<input type = "submit" name = "Stop" value = "<?php echo $stop; ?>">


<?php
    if ($selected_device == "GLOBAL" && $selected_section == "EMAIL"){
        echo "</br></br>";
        echo "<div id='delete' style='display: block;' >";
        echo "<input type = 'submit' name = 'Testmail' value = '$testmail' >";
        echo "</div>";
        }
?>



<br />
<br />
<br />
<!-- show devices that have currently no individual settings. Individual settings can be added for selected device -->
<?php
    $max = count ($spindle_list);
    if ($max > 0){ 
    // <!-- select options for spindle names -->
    echo "<select id='add_device' name = 'add_device'>";
    $i = 0;
    while($i < $max ) {
        echo'<option value = "' . $spindle_list[$i].'" name = "' . $spindle_list[$i].'">';
        echo($spindle_list[$i]);
        echo"</option>";
        $i = ++$i;
    }
    echo "<div id='add' style='display: block;'>";
    echo "<input type = 'submit' name = 'Add' value = '" . $add_device . "' >";
    echo "</div>";
    }
    ?>
</select>

</br>
</br>
<input type = "submit" name = "Export_Data" value = "<?php echo $export_data; ?>">
        
</form>

