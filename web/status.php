<?php

// Show battery status as a chart
// GET Parameters:
// name = iSpindle name
//
// January 2019
// Added support for differnet languages that are pulled from strings table in database


// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file
 
if ((include_once './config/common_db_config.php') == FALSE){
       include_once("./config/common_db_default.php");
      }
     include_once("include/common_db_query.php");

// Check GET parameters (for now: Spindle name and Timeframe to display) 
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel000'; else $_GET['name'] = $_GET['name'];

list($time, $temperature, $angle, $battery) = getCurrentValues($conn, $_GET['name']);


// get description for fields in corresponding language
$file = "status";
$header_battery = get_field_from_sql($conn,$file,"header_battery");
$header_temperature = get_field_from_sql($conn,$file,"header_temperature");
$header_angle = get_field_from_sql($conn,$file,"header_angle");
$diagram_battery = get_field_from_sql($conn,$file,"diagram_battery");
$diagram_temperature = get_field_from_sql($conn,$file,"diagram_temperature");
$diagram_angle = get_field_from_sql($conn,$file,"diagram_angle");


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
const dia_battery=[<?php echo "'".$diagram_battery."'";?>]
const dia_temperature=[<?php echo "'".$diagram_temperature."'";?>]
const dia_angle=[<?php echo "'".$diagram_angle."'";?>]

$(function () 
{
  var chart_battery;
  var chart_angle;
  var chart_temperature;
 
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
        text: '<?php echo $_GET['name'].': '.$header_battery;?>'
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
            text: dia_battery
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
        name: dia_battery,
        data: [<?php echo $battery;?>],
        tooltip: {
            valueSuffix: dia_battery
        }
      }]
    }); // chart   
 
    chart_angle = new Highcharts.Chart(
    {
      chart: 
      {
        type: 'gauge',
        plotBackgroundColor: null,
        plotBackgroundImage: null,
        plotBorderWidth: 0,
        plotShadow: false,
        renderTo: 'angle'
      },

      title: 
      {
        text: '<?php echo $header_angle;?>'
 
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
        min: 0,
        max: 90,

        minorTickInterval: 'auto',
        minorTickWidth: 1,
        minorTickLength: 10,
        minorTickPosition: 'inside',
        minorTickColor: '#666',

        tickPixelInterval: 30,
        tickWidth: 2,
        tickPosition: 'inside',
        tickLength: 15,
        tickColor: '#666',
        labels: {
            step: 2,
            rotation: 'auto'
        },
        title: {
            text: dia_angle
        },
        plotBands: [{
            from: 0,
            to: 15,
            color: '#DF5353' // red
        }, {
            from: 15,
            to: 25,
            color: '#DDDF0D' // yellow
        }, {
            from: 25,
            to: 70,
            color: '#55BF3B' // green
        }, {
            from: 70,
            to: 80,
            color: '#DDDF0D' // yellow
        }, {
            from: 80,
            to: 90,
            color: '#DF5353' // red
        }]
    },

    series: [{
        name: dia_angle,
        data: [<?php echo $angle;?>],
        tooltip: {
            valueSuffix: '°'
        }
      }]
    }); // chart_angle   
    
    chart_temperature = new Highcharts.Chart(
    {
      chart: 
      {
        type: 'gauge',
        plotBackgroundColor: null,
        plotBackgroundImage: null,
        plotBorderWidth: 0,
        plotShadow: false,
        renderTo: 'temperature'
      },

      title: 
      {
        text: '<?php echo $header_temperature;?>'

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
        min: -2.5,
        max: 35.5,

        minorTickInterval: 0.5,
        minorTickWidth: 1,
        minorTickLength: 10,
        minorTickPosition: 'inside',
        minorTickColor: '#666',

        tickPixelInterval: 20,
        tickWidth: 2,
        tickPosition: 'inside',
        tickLength: 15,
        tickColor: '#666',
        labels: {
            step: 2,
            rotation: 'auto'
        },
        title: {
            text: '°C'
        },
        plotBands: [{
            from: -2.5,
            to: 8,
            color: '#DF5353' // red
        }, {
            from: 8,
            to: 14,
            color: '#DDDF0D' // yellow
        }, {
            from: 14,
            to: 22,
            color: '#55BF3B' // green
        }, {
            from: 22,
            to: 26,
            color: '#DDDF0D' // yellow
        }, {
            from: 26,
            to: 35.5,
            color: '#DF5353' // red
        }]
    },

    series: [{
        name: 'Temperatur',
        data: [<?php echo $temperature;?>],
        tooltip: {
            valueSuffix: '°C'
        }
      }]
    }); // chart_temp   
  });  
});
</script>
</head>
<body>
 
<a href=/iSpindle/index.php><img src=include/icons8-home-26.png></a>
<div id="wrapper" style="width:97%; height:96%; position:absolute">
<script src="include/highcharts.js"></script>
<script src="include/highcharts-more.js"></script>
<div id="battery" style="width:32%; height:96%; float:left"></div>
<div id="angle" style="width:32%; height:96%; float:right"></div>
<div id="temperature" style="width:32%; height:96%; margin-left: 32%; margin-right: 32%"></div>
</div>
</body>
</html>
