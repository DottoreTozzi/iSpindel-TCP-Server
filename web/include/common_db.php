<?php

    // configure your database connection here:
    define('DB_SERVER',"localhost");
    define('DB_NAME',"iSpindel");
    define('DB_USER',"");
    define('DB_PASSWORD',"");

    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
    }

    define("defaultTimePeriod", 24);    // Timeframe for chart (backwards from now)
    define("defaultReset",  false);     // Flag for Timeframe Start (beginning of chart display)
    define("defaultDaysAgo", 365);        // Default number of days past to look for active iSpindels
?>

