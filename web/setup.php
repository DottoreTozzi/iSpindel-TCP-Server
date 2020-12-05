<?php
// error reporting
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

// December 2020
// Added verification that checks write access to the config path
// Otherwise config files cannot be written
//
// May 2020 
// Initial script
// Script for initial setup of RaspySpindle Database

// relative path to stroe config files
$config_path = "../config/";
if (is_writable($config_path)==FALSE) 
    {
        echo $config_path.' is not writable. Permissions have to be adjusted for proper setup.<br>';
        $userInfo = posix_getpwuid(posix_getuid());
        $httpd_user = $userInfo['name'];
        $groupInfo = posix_getgrgid(posix_getgid());
        $httpd_group = $groupInfo = $groupInfo['name'];
//        $httpd_user = shell_exec( 'whoami' );
        $full_config_path = substr(getcwd(),0,-3)."config";
        echo "Please allow access for the http user group: $httpd_group to the config path: $full_config_path. <br><br>";
        echo "<b>Use the following commands:</b><br>";
        echo "sudo chown root:$httpd_group $full_config_path<br>";
        echo "sudo chmod 775 $full_config_path";
        exit;
    } 

// Loads personal config file for db connection details. If not found, default file will be used
if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
    }

$ServerAdmin   = "root";
$ServerPWD     = "admin";
$ServerAddress = DB_SERVER;
$ServerPort    = DB_PORT;
$RPiDBName     = DB_NAME;
$RPiDBUser     = DB_USER;
$RPiDBPWD      = DB_PASSWORD;

if ($conn){

    // establish path by the current URL used to invoke this page
    $url="http://";
    $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
    $url .= 'index.php';
    // open the page
    header("Location: ".$url);
}


// include common functions
include_once("./include/common_db_query.php");

// retrieve information about latest settings and strings tables
include_once("../config/tables.php");


// if save button is selected database is created
if (isset($_POST['Go']))
    {
    $DB_SERVER = $_POST['ServerAddress'];
    $DB_PORT = $_POST['ServerPort'];
    $DB_ADMIN = $_POST['ServerAdmin'];
    $DB_ADMIN_PWD = $_POST['ServerPWD'];
    $DB_NAME = $_POST['RPiDBName'];
    $DB_USER = $_POST['RPiDBUser'];
    $DB_USER_PWD = $_POST['RPiDBPWD'];

// check if connection to database via root user is possible
    $conn = mysqli_connect($DB_SERVER, $DB_ADMIN, $DB_ADMIN_PWD, '', $DB_PORT);
    if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
    }

// create database
    $create_db = "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

    if (mysqli_query($conn, $create_db)) {
        echo "Database $DB_NAME created successfully<br/>";
        } 
    else {
        echo "Error creating database " . mysqli_error($conn);
        exit;
        }

// create user    
    $create_user       = "CREATE USER '$DB_USER' IDENTIFIED BY '$DB_USER_PWD'";
    $grant_user        = "GRANT ALL PRIVILEGES ON $DB_NAME . * TO '$DB_USER'";
    $flush_privileges  = "FLUSH PRIVILEGES";

    if (mysqli_query($conn, $create_user)) {
        $popup_string = "User $DB_USER created successfully<br/>";
        }
    else {
        echo "Error creating user $DB_USER" . mysqli_error($conn);
        exit;
        }

    if (mysqli_query($conn, $grant_user)) {
        $popup_string .= "User $DB_USER granted rights to $DB_NAME<br/>";
        }
    else {
        echo "Error granting $DB_USER @ $DB_ACCESS rights to $DB_NAME:" . mysqli_error($conn);
        exit;
        }

    if (mysqli_query($conn, $flush_privileges)) {
        $popup_string .= "Flushed Privileges<br/>";
        }
    else {
        echo "Error flushing privileges:" . mysqli_error($conn);
        exit;
        }



// Connect now to the created database
    $conn = mysqli_connect($DB_SERVER, $DB_ADMIN, $DB_ADMIN_PWD, $DB_NAME, $DB_PORT);
    if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
    }

// create the settings table
// latest table version is maintained in tables.php
    $file_version               = LATEST_SETTINGS_TABLE;
// filename is provided in the iSpindle-Srv directory (e.g. Strings_008 for version 008 of table)
    $file_name                  = "../Settings_".$file_version.".sql";
    import_table($conn,"Settings",$file_name);
    echo ": Settings<br/>";
// create the strings table
// latest table version is maintained in tables.php
    $file_version               = LATEST_STRINGS_TABLE;
// filename is provided in the iSpindle-Srv directory (e.g. Strings_008 for version 008 of table)
    $file_name                  = "../Strings_".$file_version.".sql";
    import_table($conn,"Strings",$file_name);
    echo ": Strings<br/>";
// create Data Table
    $create_data = "CREATE TABLE `Data` (
                   `Timestamp` datetime NOT NULL,
                   `Name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                   `ID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                   `Angle` double NOT NULL,
                   `Temperature` double NOT NULL,
                   `Battery` double NOT NULL,
                   `ResetFlag` tinyint(1) DEFAULT NULL,
                   `Gravity` double NOT NULL DEFAULT 0,
                   `UserToken` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                   `Interval` int(11) DEFAULT NULL,
                   `RSSI` int(11) DEFAULT NULL,
                   `Recipe` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                   `Recipe_ID` int(11) NOT NULL,
                   `Internal` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                   `Comment` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    PRIMARY KEY (`Timestamp`,`Name`,`ID`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='iSpindle Data';";

    if (mysqli_query($conn, $create_data)) {
        $popup_string .= "Data Table created successfully<br/>";
        }
    else {
        echo "Error creating Data Table " . mysqli_error($conn);
        exit;
        }

// create archive table
    $create_archive = "CREATE TABLE `Archive` (
                      `Recipe_ID` int(11) NOT NULL AUTO_INCREMENT,
                      `Name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `ID` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `Recipe` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `Start_date` datetime NOT NULL,
                      `End_date` datetime DEFAULT NULL,
                      `const0` double DEFAULT NULL,
                      `const1` double DEFAULT NULL,
                      `const2` double DEFAULT NULL,
                      `const3` double DEFAULT NULL,
                      PRIMARY KEY (`Recipe_ID`)
                      ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    if (mysqli_query($conn, $create_archive)) {
        $popup_string .= "Archive Table created successfully<br/>";
        }
    else {
        echo "Error creating Archive Table " . mysqli_error($conn);
        exit;
        }

// create calibration table
    $create_calibration = "CREATE TABLE `Calibration` (
                          `ID` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
                          `const0` double NOT NULL,
                          `const1` double NOT NULL,
                          `const2` double NOT NULL,
                          `const3` double NOT NULL,
                          PRIMARY KEY (`ID`)
                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='iSpindle Calibration Data' ROW_FORMAT=COMPACT;";
    if (mysqli_query($conn, $create_calibration)) {
        $popup_string .= "Calibration Table created successfully<br/>";
        }
    else {
        echo "Error creating Calibration Table " . mysqli_error($conn);
        exit;
        }

// create config table
    $create_config = "CREATE TABLE `Config` (
                     `ID` int(11) NOT NULL,
                     `Interval` int(11) NOT NULL,
                     `Token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                     `Polynomial` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                     `Sent` tinyint(1) NOT NULL
                     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='iSpindle Config Data' ROW_FORMAT=COMPACT;";

    if (mysqli_query($conn, $create_config)) {
        $popup_string .= "Config Table created successfully<br/>";
        }
    else {
        echo "Error creating Config Table " . mysqli_error($conn);
        exit;
        }

// create php config file
    $file = $config_path."common_db_config.php";
    $config_string = "<?php
    // configure your database connection here:
    define('DB_SERVER','$DB_SERVER');
    define('DB_NAME','$DB_NAME');
    define('DB_USER','$DB_USER');
    define('DB_PASSWORD','$DB_USER_PWD');
    define('DB_PORT','$DB_PORT');
    
    try {
    \$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
    }
    catch (Exception \$e) {
    \$conn = false;
    }
    define('defaultTimePeriod', 24);    // Timeframe for chart (backwards from now)
    define('defaultReset',  false);     // Flag for Timeframe Start (beginning of chart display)
    define('defaultDaysAgo', 7);        // Default number of days past to look for active iSpindels
    define('CONSOLE_LOG', 0);           // 0: disabled; 1 to allow some internal log to brwoser console
?>";
write_log($config_string);

// write string to config file
file_put_contents($file, $config_string);

// create python ini file
$file = $config_path."iSpindle_config.ini";
$python_ini = "[GENERAL]
DEBUG = 0

[MYSQL]
SQL = 1
SQL_HOST = $DB_SERVER
SQL_DB = $DB_NAME
SQL_TABLE = Data
SQL_USER = $DB_USER
SQL_PASSWORD = $DB_USER_PWD
SQL_PORT = $DB_PORT";

write_log($python_ini);

// write string to config file
file_put_contents($file, $python_ini);

// reload setup page once all things are done
$url="http://";
$url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
$url .= 'setup.php?created=1';
// open the page
header("Location: ".$url);


}


// CSS layout for page 
$document_class = 'blue';
?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <title>RasPySpindel Database Setup</title>
    <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP, Select">
    <meta name="Description" content="iSpindle Fermentation Chart Selection Screen">
    <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">

<script type="text/javascript">

</script>

</head>

<body class='<?php echo $document_class ?>'>
<form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
<h1><?php echo "RaspySpindle Database Setup" ?></h1>
<br/>
<?php
$InputWidth = 15;
echo "<table border='0'>";
echo "<tr>";
echo "<th><b>Parameter</b></th>";
echo "<th><b>Value</b></th>";
echo "<th><b>Description</b></th>";
echo "</tr>";
echo "<tr><th> </th></tr>";
echo "<tr><th> </th></tr>";
echo "<tr>";
echo "<td><b>SQL Server Address</b></td>";
echo "<td><input type='text' name = 'ServerAddress' size='" . $InputWidth . "' required='required' value='$ServerAddress'></td>";
echo "</tr>";
echo "<tr>";
echo "<td><b>SQL Server Port</b></td>";
echo "<td><input type='text' name = 'ServerPort' size='" . $InputWidth . "' required='required' value='$ServerPort'></td>";
echo "</tr>";
echo "<tr>";
echo "<td><b>SQL Server Admin</b></td>";
echo "<td><input type='text' name = 'ServerAdmin' size='" . $InputWidth . "' required='required' value='$ServerAdmin'></td>";
echo "</tr>";
echo "<tr>";
echo "<td><b>SQL Server Admin Password</b></td>";
echo "<td><input type='text' name = 'ServerPWD' size='" . $InputWidth . "' required='required' value='$ServerPWD'></td>";
echo "</tr>";
echo "<tr><th> </th></tr>";
echo "<tr><th> </th></tr>";
echo "<tr>";
echo "<td><b>RaspySpindle Database Name</b></td>";
echo "<td><input type='text' name = 'RPiDBName' size='" . $InputWidth . "' required='required' value='$RPiDBName'></td>";
echo "</tr>";
echo "<tr>";
echo "<td><b>RaspySpindle Database User</b></td>";
echo "<td><input type='text' name = 'RPiDBUser' size='" . $InputWidth . "' required='required' value='$RPiDBUser'></td>";
echo "</tr>";
echo "<tr>";
echo "<td><b>RaspySpindle Database User Password</b></td>";
echo "<td><input type='text' name = 'RPiDBPWD' size='" . $InputWidth . "' required='required' value='$RPiDBPWD'></td>";
echo "</tr>";
echo "</table>";
?>
<br/>

<input type = "submit" name = "Go" value = <?php echo "Save" ?> >
<br />


