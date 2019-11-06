<?php

// Show battery status as a chart
// GET Parameters:
// name = iSpindle name
 
// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file
 
if ((include_once './config/common_db_config.php') == FALSE){
       include_once("./config/common_db_default.php");
      }
     include_once("include/common_db_query.php");

// Check GET parameters (for now: Spindle name and Timeframe to display) 
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];

list($time, $temperature, $angle, $battery) = getCurrentValues($conn, $_GET['name']);

// Get fields from database in language selected in settings
$file = "battery";
$header_battery = get_field_from_sql($conn,$file,"header_battery");
$diagram_battery = get_field_from_sql($conn,$file,"diagram_battery");


?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>iSpindle Current Data</title>
  <meta http-equiv="refresh" content="120">
  <meta name="Keywords" content="iSpindle, iSpindel, status, current, genericTCP">
  <meta name="Description" content="iSpindle Current Status">
  <script src="include/jquery-3.1.1.min.js"></script>

<script type="text/javascript">
$(function () 
{
  var chart_battery;
 
  $(document).ready(function() 
  { 
    chart_battery = new Highcharts.Chart(
    {
      chart: 
      {
        type: 'gauge',
        plotBackgroundColor: null,
        plotBackgroundImage: null,
        plotBorderWidth: 0,
        plotShadow: false,
        renderTo: 'battery'
      },
      title: 
      {
        text: '<?php echo $header_battery;?> <?php echo $_GET['name'];?>'
      },

      pane: {
        startAngle: -150,
        endAngle: 150,
        background: [{
            backgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
                    [0, '#FFF'],
                    [1, '#333']
            ]
            },
            borderWidth: 0,
            outerRadius: '109%'
        }, {
            backgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
                    [0, '#333'],
                    [1, '#FFF']
            ]
            },
            borderWidth: 1,
            outerRadius: '107%'
        }, {
            // default background
        }, {
            backgroundColor: '#DDD',
            borderWidth: 0,
            outerRadius: '105%',
            innerRadius: '103%'
        }]
    },

    // the value axis
    yAxis: {
        min: 2.7,
        max: 4.5,

        minorTickInterval: 'auto',
        minorTickWidth: 1,
        minorTickLength: 10,
        minorTickPosition: 'inside',
        minorTickColor: '#666',

        tickPixelInterval: 30,
        tickWidth: 2,
        tickPosition: 'inside',
        tickLength: 10,
        tickColor: '#666',
        labels: {
            step: 2,
            rotation: 'auto'
        },
        title: {
            text: '<?php echo $diagram_battery;?>'
        },
        plotBands: [{
            from: 3.5,
            to: 4.5,
            color: '#55BF3B' // green
        }, {
            from: 3.1,
            to: 3.6,
            color: '#DDDF0D' // yellow
        }, {
            from: 2.7,
            to: 3.1,
            color: '#DF5353' // red
        }]
    },

    series: [{
        name: 'battery',
        data: [<?php echo $battery;?>],
        tooltip: {
            valueSuffix: ' Volt'
        }
      }]
    }); // chart   
  });  
});
</script>
</head>
<body>
<a href=/iSpindle/index.php><img src=include/icons8-home-26.png></a> 
<div id="wrapper">
<script src="include/highcharts.js"></script>
<script src="include/highcharts-more.js"></script>
<div id="battery" style="width: 98%; height: 98%; position: absolute"></div>
</div>
 
</body>
</html>
