<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

// Show the Angle/Temperature chart
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

// Array angle and temperature contain now also recipe name for each data point which will be displeayed in diagram tooltip
// Variable RecipeName will be displayed in header depending on selected timeframe
list($angle, $temperature) = getChartValues($conn, $_GET['name'], $timeFrame, $_GET['reset']);
list($RecipeName, $show) = getCurrentRecipeName($conn, $_GET['name'], $timeFrame, $_GET['reset']);

$Header=$_GET['name'].' '.$RecipeName;

if (!$_GET['reset'])
{
 $DataAvailable=isDataAvailable($conn, $_GET['name'], $timeFrame);
  if($DataAvailable[0]=='0')
  {
   $Header='Keine Daten von '.$_GET['name'].' in diesem Zeitraum. Bitte noch weitere '.$DataAvailable[1].' Tage zurückgehen';
  }  
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>iSpindle Data</title>
  <meta http-equiv="refresh" content="120">
  <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP">
  <meta name="Description" content="iSpindle Fermentation Chart">
  <script src="include/jquery-3.1.1.min.js"></script>
  <script src="include/moment.min.js"></script>
  <script src="include/moment-timezone-with-data.js"></script>
  <script src="include/highcharts.js"></script>

<script type="text/javascript">

const chartAngle = [<?php echo $angle;?>]

const chartTemp = [<?php echo $temperature;?>]



//console.log(chartAngle)
//console.log(chartTemp)

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
      {
        renderTo: 'container'
      },
      title: 
      {
         text: 'iSpindel: <?php echo $Header;?>'
      },
      subtitle: 
      { text: ' <?php
                  $timetext = 'Temperatur und Winkel ';               
                  if($_GET['reset']) 
                  {     
                    $timetext .= 'seit dem letzten Reset: ';
                  }
                  else
	                {
                    $timetext .= 'der letzten ';
                  }
                  if($tfweeks != 0)
                  {
                    $timetext .= $tfweeks . ' Woche(n), ';
                  }
                  if($tfdays != 0)
                  {
                    $timetext .= $tfdays . ' Tag(e), ';
                  }
                  $timetext .= $tfhours . ' Stunde(n).';
                  echo $timetext;
                ?>'
      },


      xAxis: 
      {
	type: 'datetime',
	gridLineWidth: 1,
	title:
        {
          text: 'Uhrzeit'
        }
      },      
      yAxis: [
      {  
	startOnTick: false,
	endOnTick: false, 
        min: 0,
	max: 90,
	title: 
        {
          text: 'Winkel'         
        },      
	labels: 
        {
          align: 'left',
          x: 3,
          y: 16,
          formatter: function() 
          {
            return this.value +'°'
          }
        },
	showFirstLabel: false
      },{
         // linkedTo: 0,
	 startOnTick: false,
	 endOnTick: false,
	 min: -5,
	 max: 35,
	 gridLineWidth: 0,
         opposite: true,
         title: {
            text: 'Temperatur'
         },
         labels: {
            align: 'right',
            x: -3,
            y: 16,
          formatter: function() 
          {
            return this.value +'°C'
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
	   if(this.series.name == 'Temperatur') {
           	const pointData = chartTemp.find(row => row.timestamp === this.point.x)
		return '<b>Sudname: </b>'+ pointData.recipe +'<br>'+'<b>'+ this.series.name +' </b>um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y.toFixed(2) +'°C';
	   } else {
		const pointData = chartAngle.find(row => row.timestamp === this.point.x)
	   	return '<b>Sudname: </b>'+ pointData.recipe +'<br>'+'<b>'+ this.series.name +' </b>um '+ Highcharts.dateFormat('%H:%M', new Date(this.x)) +' Uhr:  '+ this.y.toFixed(2) +'°';
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
          name: 'Winkel', 
	  color: '#FF0000',
	  data: chartAngle.map(row => [row.timestamp, row.value]),
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
          name: 'Temperatur', 
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
        
        }

      ] //series      
    });
  });  
});
</script>
</head>
<body>
 
<div id="wrapper">
  <script src="include/highcharts.js"></script>
  <div id="container" style="width:98%; height:98%; position:absolute"></div>
</div>
 
</body>
</html>

