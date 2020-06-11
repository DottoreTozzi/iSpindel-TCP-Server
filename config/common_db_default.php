<?php

    // configure your database connection here:
    define('DB_SERVER',"127.0.0.1");
    define('DB_NAME',"iSpindle");
    define('DB_USER',"iSpindle");
    define('DB_PASSWORD',"ohyeah");
    define('DB_PORT',"3306");

try {
    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
    }
catch (Exception $e) {
 $conn = false;
}
    define("defaultTimePeriod", 24);    // Timeframe for chart (backwards from now)
    define("defaultReset",  false);     // Flag for Timeframe Start (beginning of chart display)
    define("defaultDaysAgo", 7);        // Default number of days past to look for active iSpindels

    define("CONSOLE_LOG", 0);		// 0: disabled; 1 to allow some internal log to brwoser console
?>

