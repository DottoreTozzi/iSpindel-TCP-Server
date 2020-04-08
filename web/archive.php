<?php

// Show the Density/Temperature chart
// GET Parameters:
// hours = number of hours before now() to be displayed
// days = hours x 24   
// weeks = days x 7    
// name = iSpindle name
 
// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file
 
if ((include_once './config/common_db_config.php') == FALSE){
       include_once("./config/common_db_default.php");
      }
     include_once("include/common_db_query.php");

// Check GET parameters (for now: Spindle name and Timeframe to display) 
if(!isset($_GET['recipe_id'])) $_GET['recipe_id'] = 1; else $_GET['recipe_id'] = $_GET['recipe_id'];
$selected_recipe=$_GET['recipe_id'];

// self called: if back button is selected, landing page is loaded
if (isset($_POST['Stop']))
    {
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "index.php";
        // open the page
        header("Location: ".$url);

    }

// self caled function: if add button is selected, default settings for selected device will be copied and can be modified later individually
if (isset($_POST['Go']))
    {
        $recipe_id = $_POST['archive_name'];
        // reload page for selected archive
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "archive.php?recipe_id=".$recipe_id;
        // open the page
        header("Location: ".$url);
    }



                                                                            
$timeFrame = $_GET['hours'] + ($_GET['days'] * 24) + ($_GET['weeks'] * 168);
if($timeFrame == 0) $timeFrame = defaultTimePeriod;
$tftemp = $timeFrame;           
$tfweeks = floor($tftemp / 168);
$tftemp -= $tfweeks * 168;    
$tfdays = floor($tftemp / 24);
$tftemp -= $tfdays * 24;
$tfhours = $tftemp;                                
                                                   
list($SpindleName, $RecipeName, $start_date, $end_date, $dens, $temperature, $angle) = getArchiveValuesPlato4($conn, $_GET['recipe_id']);

$start_date = date("Y-m-d", strtotime($start_date));


// Get fields from database in language selected in settings
$file = "plato4";
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
$not_calibrated = get_field_from_sql($conn,'diagram',"not_calibrated"); 
$tooltip_at = get_field_from_sql($conn,'diagram',"tooltip_at");
$tooltip_time = get_field_from_sql($conn,'diagram',"tooltip_time");
$file = "settings";
$stop = get_field_from_sql($conn,$file,"stop");

$file = "index";
$show_diagram = get_field_from_sql($conn,$file,"show_diagram");



// define header displayed in diagram depending on value for recipe
if ($RecipeName <> '') {
    $Header=$SpindleName.' | ' . $recipe_name .' ' . $RecipeName . ' | Start: ' . $start_date;
    }
else {
    $Header='iSpindel: ' . $SpindleName . ' | Start: ' . $start_date;
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

// get all spindle names to be displayed in form that have submitted data within the timeframe of $daysago
    $archive_sql = "SELECT * FROM Archive ORDER BY Recipe_ID";
    $archive_result=mysqli_query($conn, $archive_sql) or die(mysqli_error($conn));
    $len = mysqli_num_rows($archive_result);


?>

<!DOCTYPE html>
<html>
<head>
  <form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
  <meta charset="utf-8">
  <title>iSpindle Data</title>
  <meta http-equiv="refresh" content="120">
  <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP">
  <meta name="Description" content="iSpindle Fermentation Chart">
  <script src="include/jquery-3.1.1.min.js"></script>
  <script src="include/moment.min.js"></script>
  <script src="include/moment-timezone-with-data.js"></script>
  <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">

<script type="text/javascript">

// define constants for data in chart. Allows for mor than two variables. Recipe information is included here and can be displayed in tooltip
const chartDens=[<?php echo $dens;?>]
const chartTemp=[<?php echo $temperature;?>]
// define constants to be displayed in diagram -> no php code needed in chart
const recipe_name=[<?php echo "'".$recipe_name."'";?>]
const first_y=[<?php echo "'".$first_y."'";?>]
const second_y=[<?php echo "'".$second_y."'";?>]
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
                    
    if ('<?php echo $isCalib;?>' == '0')
    {
        document.write('<h2>iSpindel \'<?php echo $_GET['name'] . ' ' . $not_calibrated;?>\'</h2>');
    }
    else
    {
        Highcharts.setOptions({
              global: {
                  timezone: 'Europe/Berlin'
              }
          });
                
        chart = new Highcharts.Chart(
        {
            chart:
            {   backgroundColor: 'rgba(0,0,0,0)',
                renderTo: 'container'
            },
            title:
            {
                text: chart_header
            },
            subtitle:
            { 
                      text: chart_subheader                 
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
                    min: 0,
                    max: 25,
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
                            return this.value + '°P'
                        }
                    },
                    showFirstLabel: false
                    },{
                    // linkedTo: 0,
                    startOnTick: true,
                    endOnTick: true,
                    min: -5,
                    max: 30,
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
                    if(this.series.name == second_y) {
			const pointData = chartTemp.find(row => row.timestamp === this.point.x)
                        return '<b>' + recipe_name + ' </b>'+pointData.recipe+'<br>'+'<b>'+ this.series.name + ' </b>' + tooltip_at + ' ' + Highcharts.dateFormat('%H:%M', new Date(this.x)) + ' ' + tooltip_time + ' ' + this.y.toFixed(2) +'°C';
                    } else {
			const pointData = chartDens.find(row => row.timestamp === this.point.x)
                        return '<b>' + recipe_name + ' </b>'+pointData.recipe+'<br>'+'<b>'+ this.series.name + ' </b>' + tooltip_at + ' ' + Highcharts.dateFormat('%H:%M', new Date(this.x))  + ' ' + tooltip_time + ' ' + this.y.toFixed(2) +'%';
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

                }
            ] //series      
            });
    }
  });
});
</script>
</head>
<body>

<!-- select options for spindle names -->
<?php
    if ($len != 0){

echo "<select id='archive_name' name = 'archive_name'>";
while($row = mysqli_fetch_assoc($archive_result) )
{
    $start_date = $row['Start_date'];
    $newDate = date("Y-m-d", strtotime($start_date));
    $ID = $row['Recipe_ID'];
if ($selected_recipe==$ID) 
{
    echo "<option value = '$ID' selected>";
}
else
{
    echo "<option value = '$ID'>";
}
?>
                <?php echo($row['Recipe_ID']." | ".$row['Name']." | ".$newDate." | ".$row['Recipe']) ?>
        <?php
            }
        ?>
        </option>
</select>
<?php
    }
?>

<span title="<?php echo($show_diagram)?>"><input type = "submit" id='diagram' name = "Go" value = "<?php echo($show_diagram)?>"></span>
</br>
<span title="<?php echo($stop)?>"><input type = "submit" id='Stop' name = "Stop" value = "<?php echo($stop)?>"></span>

<div id="wrapper">
  <script src="include/highcharts.js"></script>
  <script src="include/modules/exporting.js"></script>
  <script src="include/modules/offline-exporting.js"></script>
  <div id="container" style="width:90%; height:90%; position:absolute"></div>
</div>
</form> 
</body>
</html>