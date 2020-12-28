<?php
// show errors in case of issues with php
// ini_set('display_errors', 'On');
// error_reporting(E_ALL | E_STRICT);

// Loads personal config file for db connection details. If not found, default file will be used
if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
    }

// "Days Ago parameter set?
    if(!isset($_GET['LANGUAGE'])) $_GET['LANGUAGE'] = "DE" ; else $_GET['LANGUAGE'] = $_GET['LANGUAGE'];
    $LANGUAGE = $_GET['LANGUAGE'];
    $help_file= "help_$LANGUAGE.php";

    if (!file_exists($help_file)){
        $help_file = "help_DE.php";
    }

    $url="http://";
    $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
    $url .= $help_file;
    // open the page
    header("Location: ".$url);
    exit;
?>
