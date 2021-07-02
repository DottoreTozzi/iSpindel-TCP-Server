<?php

// Show the Density/Temperature chart
// GET Parameters:
// hours = number of hours before now() to be displayed
// days = hours x 24
// weeks = days x 7
// name = iSpindle name

// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file
 
if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
      }
     include_once("include/common_db_query.php");

// Check GET parameters (for now: Spindle name and Timeframe to display)
if(!isset($_GET['hours'])) $_GET['hours'] = 0; else $_GET['hours'] = $_GET['hours'];
if(!isset($_GET['name'])) $_GET['name'] = 'iSpindel001'; else $_GET['name'] = $_GET['name'];
if(!isset($_GET['reset'])) $_GET['reset'] = defaultReset; else $_GET['reset'] = $_GET['reset'];
if(!isset($_GET['days'])) $_GET['days'] = 0; else $_GET['days'] = $_GET['days'];
if(!isset($_GET['weeks'])) $_GET['weeks'] = 0; else $_GET['weeks'] = $_GET['weeks'];

// Calculate Timeframe in Hours
$timeFrame = $_GET['hours'] + ($_GET['days'] * 24) + ($_GET['weeks'] * 168);
if($timeFrame == 0) $timeFrame = defaultTimePeriod;
$tftemp = $timeFrame;
$tfweeks = floor($tftemp / 168);
$tftemp -= $tfweeks * 168;
$tfdays = floor($tftemp / 24);
$tftemp -= $tfdays * 24;
$tfhours = $tftemp;

list($isCalib, $dens, $temperature, $angle, $gravity, $battery, $RSSI, $temperature2) = getChartValues($conn, $_GET['name'], $timeFrame, $_GET['reset']);
list($RecipeName, $show) = getCurrentRecipeName($conn, $_GET['name'], $timeFrame, $_GET['reset']);
$document_class = get_color_scheme($conn);
// Get fields from database in language selected in settings
$file = "plato";
$recipe_name = get_field_from_sql($conn,'diagram',"recipe_name");
$first_y = get_field_from_sql($conn,$file,"first_y");
$second_y = get_field_from_sql($conn,$file,"second_y");
$x_axis = get_field_from_sql($conn,$file,"x_axis");
$subheader = get_field_from_sql($conn,$file,"timetext");
$subheader_reset = get_field_from_sql($conn,$file,"timetext_reset");
$subheader_weeks = get_field_from_sql($conn,'diagram',"timetext_weeks");
$subheader_days = get_field_from_sql($conn,'diagram',"timetext_days");
$subheader_hours = get_field_from_sql($conn,'diagram',"timetext_hours");
$header_no_data_1 = get_field_from_sql($conn,'diagram',"header_no_data_1");
$header_no_data_2 = get_field_from_sql($conn,'diagram',"header_no_data_2");
$header_no_data_3 = get_field_from_sql($conn,'diagram',"header_no_data_3");
$tooltip_at = get_field_from_sql($conn,'diagram',"tooltip_at");
$tooltip_time = get_field_from_sql($conn,'diagram',"tooltip_time");
$PARA_FIRST_Y_MIN = "PLATO_Y_AXIS_MIN";
$PARA_FIRST_Y_MAX = "PLATO_Y_AXIS_MAX";
$PARA_SECOND_Y_MIN = "TEMPERATURE_Y_AXIS_MIN";
$PARA_SECOND_Y_MAX = "TEMPERATURE_Y_AXIS_MAX";
$first_y_unit = " °P";
$second_y_unit = " °C";
$ChartFirst = $gravity;
$ChartSecond = $temperature;
$ChartThird = $temperature2;

$first_y_min = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_FIRST_Y_MIN));
$first_y_max = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_FIRST_Y_MAX));
$second_y_min = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_SECOND_Y_MIN));
$second_y_max = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_SECOND_Y_MAX));



$file = "settings";
$stop = get_field_from_sql($conn,$file,"stop");

// define header displayed in diagram depending on value for recipe
if ($RecipeName <> '') {
    $Header=$_GET['name'].' | ' . $recipe_name .' ' . $RecipeName;
    }
else {
    $Header='iSpindel: ' . $_GET['name'];
    }


// Header will show, that there is no data available, and displays timeframe user needs to go back to see data in diagram
if (!$_GET['reset'])
{
 $DataAvailable=isDataAvailable($conn, $_GET['name'], $timeFrame);
  if($DataAvailable[0]=='0')
  {
   $Header='iSpindel: ' . $header_no_data_1 . ' ' . $_GET['name']. ' ' . $header_no_data_2 . ' ' .$DataAvailable[1]. ' ' . $header_no_data_3;
  }
}

// define subheader to be displayed in diagram
$timetext = $subheader . ' ';
if($_GET['reset']) {
    $timetext = $subheader_reset . ' ';
    }
if($tfweeks != 0) {
    $timetext .= $tfweeks . ' ' . $subheader_weeks;
    }
if($tfdays != 0) {
    $timetext .= $tfdays . ' ' . $subheader_days;
    }
$timetext .= $tfhours . ' ' . $subheader_hours;

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>iSpindle Data</title>
  <meta http-equiv="refresh" content="120">
  <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP">
  <meta name="Description" content="iSpindle Fermentation Chart">
  <script src="include/jquery-3.1.1.min.js"></script>
  <script src="include/moment.min.js"></script>
  <script src="include/moment-timezone-with-data.js"></script>
  <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">
  <link rel="shortcut icon" href="./include/favicon.ico" type="image/x-icon">
  <link rel="icon" href="./include/favicon.ico" type="image/x-icon">

<script type="text/javascript">

// define constants for data in chart. Allows for mor than two variables. Recipe information is included here and can be displayed in tooltip
const chartDens=[<?php echo $ChartFirst;?>]
const chartTemp=[<?php echo $ChartSecond;?>]
const chartTemp2=[<?php echo $ChartThird;?>]

// define constants to be displayed in diagram -> no php code needed in chart
const recipe_name=[<?php echo "'".$recipe_name."'";?>]
const first_y=[<?php echo "'".$first_y."'";?>]
const second_y=[<?php echo "'".$second_y."'";?>]
const third_y=[<?php echo "'".$second_y." 2'";?>]
const ThirdChartAvailable=chartTemp2.length
var third_legend = false
if (ThirdChartAvailable != 0){
        var third_legend = true
        }


const first_y_min = <?php echo $first_y_min;?>;
const second_y_min = <?php echo $second_y_min;?>;
const first_y_max = <?php echo $first_y_max;?>;
const second_y_max = <?php echo $second_y_max;?>;
const first_y_unit = [<?php echo "'".$first_y_unit."'";?>]
const second_y_unit = [<?php echo "'".$second_y_unit."'";?>]


const x_axis=[<?php echo "'".$x_axis."'";?>]
const chart_header=[<?php echo "'" . $Header . "'";?>]
const chart_subheader=[<?php echo "'" . $timetext . "'";?>]
const tooltip_at=[<?php echo "'".$tooltip_at."'";?>]
const tooltip_time=[<?php echo "'".$tooltip_time."'";?>]

$(function () 
{
  var chart;
 
  $(document).ready(function() 
  {
        Highcharts.setOptions({
              global: {
                  timezone: 'Europe/Berlin'
              }
          });
                
        chart = new Highcharts.Chart(
        {
            chart:
            {   backgroundColor: "rgba(0,0,0,0)",
                renderTo: 'container'
            },
            title:
            {
                text: chart_header
            },
            subtitle:
                { text: chart_subheader
            },
            xAxis:
            {
                type: 'datetime',
                gridLineWidth: 1,
                title:
            {
                text: x_axis
            }
            },
            yAxis: [
                {
                    startOnTick: false,
                    endOnTick: false,
                    min: first_y_min,
                    max: first_y_max,
                    title:
                    {
                        text: first_y
                    },
                    labels:
                    {
                        align: 'left',
                        x: 3,
                        y: 16,
                        formatter: function()
                        {
                            return this.value + first_y_unit
                        }
                    },
                    showFirstLabel: false
                    },{
                    // linkedTo: 0,
                    startOnTick: false,
                    endOnTick: false,
                    min: second_y_min,
                    max: second_y_max,
                    gridLineWidth: 0,
                    opposite: true,
                    title: {
                        text: second_y
                    },
                    labels: {
                        align: 'right',
                        x: -3,
                        y: 16,
                        formatter: function() 
                        {
                            return this.value + second_y_unit
                        }
                    },
                    showFirstLabel: false
                }
            ],
            tooltip:
            {
                crosshairs: [true, true],
                formatter: function() 
                {
                    if(this.series.name == second_y) {
                        const pointData = chartTemp.find(row => row.timestamp === this.point.x)
                        return '<b>' + recipe_name + ' </b>'+pointData.recipe+'<br>'+'<b>'+ this.series.name + ' </b>' + tooltip_at + ' ' + Highcharts.dateFormat('%d.%m %H:%M', new Date(this.x)) + ' ' + tooltip_time + ' ' + this.y.toFixed(2) + second_y_unit;
                    } else {
                        const pointData = chartDens.find(row => row.timestamp === this.point.x)
                        return '<b>' + recipe_name + ' </b>'+pointData.recipe+'<br>'+'<b>'+ this.series.name + ' </b>' + tooltip_at + ' ' + Highcharts.dateFormat('%d.%m %H:%M', new Date(this.x))  + ' ' + tooltip_time + ' ' + this.y.toFixed(2) + first_y_unit;
                    }
                }
            },  
            legend: 
            {
                enabled: true
            },
            credits:
            {
                enabled: false
            },
            series:
            [
                {
                    dataLabels: [{
                        enabled: true,
                        shape: 'callout',
                        y: -15,
                        borderRadius: 5,
                        backgroundColor: 'rgba(252, 255, 255, 0.7)',
                        borderWidth: 1,
                        borderColor: '#000',
                        formatter: function() {
                            const Comment = chartDens.find(row => row.timestamp === this.point.x)
                            var label_up = Comment.text_up
                            if (Comment.text_up){
                            label_up = Highcharts.dateFormat('%d.%m - %H:%M', new Date(this.x))  + '<br/> ' + Comment.text_up
                            }
                            return label_up;
                        }
                    },
                   {
                        enabled: true,
                        shape: 'callout',
                        y: 50,
                        borderRadius: 5,
                        backgroundColor: 'rgba(252, 255, 255, 0.7)',
                        borderWidth: 1,
                        borderColor: '#000',
                        formatter: function() {
                            const Comment = chartDens.find(row => row.timestamp === this.point.x)
                            var label_down = Comment.text_down
                            if (Comment.text_down){
                            label_down = Highcharts.dateFormat('%d.%m - %H:%M', new Date(this.x))  + '<br/> ' + Comment.text_down
                            }
                            return label_down;
                        }
                    }],

                    name: first_y,
                    color: '#FF0000',
                    data: chartDens.map(row => [row.timestamp, row.value]),
                    marker: 
                    {
                        symbol: 'square',
                        enabled: false,
                        states: 
                        {
                            hover:
                            {
                                symbol: 'square',
                                enabled: true,
                                radius: 8
                            }
                        }
                    }
                },
                {
                    name: second_y,
                    yAxis: 1,
                    color: '#0000FF',
                    data: chartTemp.map(row => [row.timestamp, row.value]),
                    marker: 
                        {
                            symbol: 'square',
                            enabled: false,
                            states: 
                            {
                                hover:
                                {
                                symbol: 'square',
                                enabled: true,
                                radius: 8
                                }
                            }
                        }

                },
                {
                    name: third_y,
                    showInLegend: third_legend,
                    yAxis: 1,
                    color: '#2e7d32',
                    data: chartTemp2.map(row => [row.timestamp, row.value]),
                    marker:
                        {
                            symbol: 'square',
                            enabled: false,
                            states:
                            {
                                hover:
                                {
                                symbol: 'square',
                                enabled: true,
                                radius: 8
                                }
                            }
                        }

                }

            ] //series      
            });
  });
});
</script>
</head>
<body class='<?php echo $document_class ?>'>

<a href=/iSpindle/index.php><img src=include/icons8-home-26.png alt="<?php echo $stop; ?>" width="50" height="50"></a>
 
<div id="wrapper">
  <script src="include/highcharts.js"></script>
  <script src="include/modules/exporting.js"></script>
  <script src="include/modules/offline-exporting.js"></script>
  <div id="container" style="width:90%; height:90%; position:absolute"></div>
</div>
 
</body>
</html>
