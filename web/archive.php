<?php
// Archive.php script to show older fermentation data
// Version 1.0 May 20, 2020 (initial release)
//
// Archive can show different diagram styles of old datasets
// End of fermentation can be set to display only relevant data
// Comments can be added at any given time selected in the diagram
//
// _GET Parameters:
// recipe_ID : selected ID for recipe to be shown -> if not set, lowest ID from database will be used
// type      : Diagram type to be shown (0 - 4) -> if not set, 0 is default
// RID_END   : Flag (timestamp) to send end of fermentation flag to atabase for selected ID. Data will be shown only from reset to this flag
// comment   : Add comment to selected id at selected timestamp (RID_END is used for this purpose)
//


// error handling 
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

// DB config values will be pulled from differtent location and user can personalize this file: common_db_config.php
// If file does not exist, values will be pulled from default file
 
if ((include_once '../config/common_db_config.php') == FALSE){
       include_once("../config/common_db_default.php");
      }
     include_once("include/common_db_query.php");

// select the minimum ID for the recipe in the archive table and set it as selected recipe
$min_recipe_id = "SELECT min(Recipe_ID) FROM Archive";
$q_sql = mysqli_query($conn, $min_recipe_id) or die(mysqli_error($conn));
$result = mysqli_fetch_array($q_sql);
$selected_recipe = $result[0];

//write_log('Selected_recipe: '. $selected_recipe);

// if archive is empty, go back to index page
if (!$selected_recipe){
    $url="http://";
    $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
    $url .= "index.php";
    // open the page
    header("Location: ".$url);
    
}

// Check if recipe_id is set and use this id as selected recipe if set
if(isset($_GET['recipe_id'])){
    $selected_recipe = $_GET['recipe_id'];
}

//check if flag for end of fermenation is already existing
$rid_end_exists = 0;
$check_RID_END = "SELECT Timestamp FROM Data WHERE Recipe_ID = '$selected_recipe' AND Internal = 'RID_END'";
$q_sql = mysqli_query($conn, $check_RID_END) or die(mysqli_error($conn));
$rows = mysqli_num_rows($q_sql);
if ($rows <> 0){
    $rid_end_exists = 1;
    $result = mysqli_fetch_array($q_sql);
    $timestamp_rid = $result[0];
}

// Check if other _GET parameters are set
if(!isset($_GET['type']))
    {
    $diagram_type = '0';
    }
else {
    $diagram_type = $_GET['type'];
    }


if(!isset($_GET['comment']))
    {
    $comment = '';
    }
else {
    $comment = $_GET['comment'];
    }

// if RID_END parameter is set,flag has to be written to the data table at the given timestamp
if(!isset($_GET['RID_END']))
    { 
    $RID_END = ''; 
    }
else {
    $RID_END = ($_GET['RID_END']); 
// if comment is not set in addition to RID_END, write RID_END flag to data table for selected recipe at selected timestamp
    if($comment == ''){
        //if flag for end of fermenation is already existing: remove it
        if ($rid_end_exists == 1)
        {
            $remove_recipe_ID="UPDATE Data Set Internal = NULL WHERE Recipe_ID = $selected_recipe AND Timestamp = $timestamp_rid";
            $q_sql = mysqli_query($conn, $remove_recipe_ID) or die(mysqli_error($conn));
            write_log("SELECT to remove RID_END is existing: " . $remove_recipe_ID);

        }

        //add Flag for end of fermentation for archive to last datapoint of current spindle
        $add_recipe_ID="UPDATE Data Set Internal = 'RID_END' WHERE Recipe_ID = $selected_recipe AND UNIX_TIMESTAMP(Timestamp) = $RID_END";
        $q_sql = mysqli_query($conn, $add_recipe_ID) or die(mysqli_error($conn));
        write_log("SELECT to Add RID_END: " . $add_recipe_ID);


    }
// if comment parameter is set in addition to RID_END, comment is written to datatable for selected recipe at selected timestamp
    else {
        $add_recipe_ID="UPDATE Data Set Comment = '$comment' WHERE Recipe_ID = $selected_recipe AND UNIX_TIMESTAMP(Timestamp) = $RID_END";
        write_log("SELECT to add comment: " . $add_recipe_ID);
        mysqli_set_charset($conn, "utf8mb4");
        $q_sql = mysqli_query($conn, $add_recipe_ID) or die(mysqli_error($conn));
    
    }

}

// self called: if back button is selected, landing page is loaded
if (isset($_POST['Stop']))
    {
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "index.php";
        // open the page
        header("Location: ".$url);
    }

// self called function: if go button is selected, archive page is reloaded with selected recipe_id and diagram type
if (isset($_POST['Go']))
    {
        $recipe_id = $_POST['archive_name'];
        $dia_type = $_POST['diagram_type'];
        // reload page for selected archive
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "archive.php?recipe_id=".$recipe_id."&type=".$dia_type;
        // open the page
        header("Location: ".$url);
    }

// self called function: if Del button is selected, data for currenlty selected fermentation will be deleted from data and archive table (this cannot be undone)
if (isset($_POST['Del']))
    {
        $recipe_id = $_POST['archive_name'];        
        //delete active recipe
        delete_recipe_from_archive($conn,$recipe_id);
        // reload page for selected archive
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "archive.php";
        // open the page
        header("Location: ".$url);
    }

// self called function: if Remove button is selected, RIE_END Flag is removed from selected dataset. New flag can be added at different position.
if (isset($_POST['Remove']))
    {
        $recipe_id = $_POST['archive_name'];
        $dia_type = $_POST['diagram_type'];
        //delete active recipe
        delete_rid_flag_from_archive($conn,$recipe_id);
        // reload page for selected archive
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "archive.php?recipe_id=".$recipe_id."&type=".$dia_type;
        // open the page
        header("Location: ".$url);
    }

// self called function: if Export button is selected, data of selected fermentation is exported to a CSV file including a summary at the top. Data can be used in Excel
if (isset($_POST['Export']))
    {
        $txt_recipe_name =  $_POST['txt_recipe_name'];
        $txt_end =  $_POST['txt_end'];
        $txt_initial_gravity =  $_POST['txt_initial_gravity'];
        $initial_gravity =  $_POST['initial_gravity'];
        $txt_final_gravity =  $_POST['txt_final_gravity'];
        $final_gravity =  $_POST['final_gravity'];
        $txt_attenuation =  $_POST['txt_attenuation'];
        $attenuation =  $_POST['attenuation'];
        $txt_alcohol =  $_POST['txt_alcohol'];
        $txt_calibration =  $_POST['txt_calibration'];
        $alcohol =  $_POST['alcohol'];
        $recipe_id = $_POST['archive_name'];
        $dia_type = $_POST['diagram_type'];
        $csv_type = $_POST['radio_csv'];

        //export active recipe to csv file
        ExportArchiveValues($conn,$recipe_id, $txt_recipe_name, $txt_end, $txt_initial_gravity, $initial_gravity, $txt_final_gravity, $final_gravity, $txt_attenuation, $attenuation, $txt_alcohol, $alcohol, $txt_calibration, $csv_type);
        // reload page for selected archive
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "archive.php?recipe_id=".$recipe_id."&type=".$dia_type;
        // open the page
        header("Location: ".$url);
    }

// get initial gravity and constants for gravity calulaction to be shown in the header table
list($isCalib,$initial_gravity, $const0, $const1, $const2, $const3) = getArchiveInitialGravity($conn, $selected_recipe);
// get all data for the different diagram tyes
list($SpindleName, $RecipeName, $start_date, $end_date, $dens, $temperature, $angle, $gravity, $battery, $rssi, $SVG, $ABV) = getArchiveValues($conn, $selected_recipe, $initial_gravity);
// get the final gracvitry for this ID
list($isCalib,$final_gravity) = getArchiveFinalGravity($conn, $selected_recipe, $end_date);
// get selected colo scheme for the layout
$document_class = get_color_scheme($conn);

// calculate various parameters to be displayed in the header table
$attenuation = ($initial_gravity - $final_gravity)*100 / $initial_gravity;
$real_dens = 0.1808 * $initial_gravity + 0.8192 * $final_gravity;
$alcohol = ((100* ($real_dens - $initial_gravity) / (1.0665 * $initial_gravity -206.65)) / 0.795);

// define date and number formats for parameters in the header table
$start_date = date("Y-m-d", strtotime($start_date));
$end_date = date("Y-m-d", strtotime($end_date));
$const0 = number_format($const0,4);
$const1 = number_format($const1,4);
$const2 = number_format($const2,4);
$const3 = number_format($const3,4);
$cal = 0;

// define diagram parameters such as units and max/min values and data to be shown depenting on the selection of diagram_type

if($diagram_type == '0')
{
    $file = "plato4";
    $PARA_FIRST_Y_MIN = "PLATO_Y_AXIS_MIN";
    $PARA_FIRST_Y_MAX = "PLATO_Y_AXIS_MAX";
    $PARA_SECOND_Y_MIN = "TEMPERATURE_Y_AXIS_MIN";
    $PARA_SECOND_Y_MAX = "TEMPERATURE_Y_AXIS_MAX";
    $first_y_unit = " °P";
    $second_y_unit = " °C";
    $ChartFirst = $dens;
    $ChartSecond = $temperature;
    $cal = 1;
}

if($diagram_type == '2')
{
    $file = "plato4";
    $PARA_FIRST_Y_MIN = "PLATO_Y_AXIS_MIN";
    $PARA_FIRST_Y_MAX = "PLATO_Y_AXIS_MAX";
    $PARA_SECOND_Y_MIN = "TEMPERATURE_Y_AXIS_MIN";
    $PARA_SECOND_Y_MAX = "TEMPERATURE_Y_AXIS_MAX";
    $first_y_unit = " °P";
    $second_y_unit = " °C";
    $ChartFirst = $gravity;
    $ChartSecond = $temperature;
}

if($diagram_type == '1')
{
    $file = "angle";
    $PARA_FIRST_Y_MIN = "ANGLE_Y_AXIS_MIN";
    $PARA_FIRST_Y_MAX = "ANGLE_Y_AXIS_MAX";
    $PARA_SECOND_Y_MIN = "TEMPERATURE_Y_AXIS_MIN";
    $PARA_SECOND_Y_MAX = "TEMPERATURE_Y_AXIS_MAX";
    $first_y_unit = " °";
    $second_y_unit = " °C";
    $ChartFirst = $angle;
    $ChartSecond = $temperature;
}
if($diagram_type == '3')
{
    $file = "batterytrend";
    $PARA_FIRST_Y_MIN = "BATTERY_Y_AXIS_MIN";
    $PARA_FIRST_Y_MAX = "BATTERY_Y_AXIS_MAX";
    $PARA_SECOND_Y_MIN = "RSSI_Y_AXIS_MIN";
    $PARA_SECOND_Y_MAX = "RSSI_Y_AXIS_MAX";
    $first_y_unit = " V";
    $second_y_unit = " dB";
    $ChartFirst = $battery;
    $ChartSecond = $rssi;
}

if($diagram_type == '4')
{
    $file = "svg_ma";
    $PARA_FIRST_Y_MIN = "SVG_Y_AXIS_MIN";
    $PARA_FIRST_Y_MAX = "SVG_Y_AXIS_MAX";
    $PARA_SECOND_Y_MIN = "ALCOHOL_Y_AXIS_MIN";
    $PARA_SECOND_Y_MAX = "ALCOHOL_Y_AXIS_MAX";
    $first_y_unit = " %";
    $second_y_unit = " %";
    $ChartFirst = $SVG;
    $ChartSecond = $ABV;
}

// pull axis lable for first y depending on selected diagram type
$first_y = get_field_from_sql($conn,$file,"first_y");

// do the same for the second y axis. For the SVG diagram we need to pull the lable of the third y axis and assign it here to the second
if ($diagram_type != '4'){
$second_y = get_field_from_sql($conn,$file,"second_y");
}
else {
$second_y = get_field_from_sql($conn,$file,"third_y");
}

// pull the min/max values for the selected diagram type from the settings table
$first_y_min = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_FIRST_Y_MIN));
$first_y_max = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_FIRST_Y_MAX));
$second_y_min = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_SECOND_Y_MIN));
$second_y_max = intval(get_settings_from_sql($conn,"DIAGRAM","GLOBAL",$PARA_SECOND_Y_MAX));

// Get fields from database in language selected in settings
$file = "plato4";
$recipe_name = get_field_from_sql($conn,'diagram',"recipe_name");
$x_axis = get_field_from_sql($conn,$file,"x_axis");
$tooltip_at = get_field_from_sql($conn,'diagram',"tooltip_at");
$tooltip_time = get_field_from_sql($conn,'diagram',"tooltip_time");

$file = "settings";
$stop = get_field_from_sql($conn,$file,"stop");

$file="archive";
$delete_archive = get_field_from_sql($conn,$file,"delete_archive");
$archive_end = get_field_from_sql($conn,$file,"archive_end");
$time_selected = get_field_from_sql($conn,$file,"time_selected");
$archive_end_removal = get_field_from_sql($conn,$file,"archive_end_removal");
$txt_attenuation = get_field_from_sql($conn,$file,"attenuation");
$txt_final_gravity = get_field_from_sql($conn,$file,"final_gravity");
$txt_calibration = get_field_from_sql($conn,$file,"calibration_archive");
$txt_end = get_field_from_sql($conn,$file,"end");
$txt_archive = get_field_from_sql($conn,$file,"archive");
$comment_text = get_field_from_sql($conn,$file,"comment_text");
$txt_initial_gravity = get_field_from_sql($conn,$file,"header_initialgravity");
$txt_alcohol = get_field_from_sql($conn,$file,"alcohol");

$file = "index";
$show_diagram = get_field_from_sql($conn,$file,"show_diagram");
$send_comment = get_field_from_sql($conn,$file,"send_comment");
$chart_filename_04 = get_field_from_sql($conn,$file,"chart_filename_04");
$chart_filename_06 = get_field_from_sql($conn,$file,"chart_filename_06");
$chart_filename_08 = get_field_from_sql($conn,$file,"chart_filename_08");
$chart_filename_12 = get_field_from_sql($conn,$file,"chart_filename_12");
$chart_filename_10 = get_field_from_sql($conn,$file,"chart_filename_10");

// get all fermentations from the archive table
$archive_sql = "SELECT * FROM Archive ORDER BY Recipe_ID";
$archive_result=mysqli_query($conn, $archive_sql) or die(mysqli_error($conn));
// parameter is used to check if archive table is filled with content
$len = mysqli_num_rows($archive_result);

?>

<!DOCTYPE html>
<html>
<head>
  <form name="main" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
  <meta charset="utf-8">
  <title>iSpindle Data</title>
<!--  <meta http-equiv="refresh" content="120"> -->
  <meta name="Keywords" content="iSpindle, iSpindel, Chart, genericTCP">
  <meta name="Description" content="iSpindle Fermentation Chart">
  <script src="include/jquery-3.1.1.min.js"></script>
  <script src="include/moment.min.js"></script>
  <script src="include/moment-timezone-with-data.js"></script>
  <link rel="stylesheet" type="text/css" href="./include/iSpindle.css">
<script type="text/javascript">


// define constants for data in chart. Allows for more than two variables. Recipe information is included here and can be displayed in tooltip
const chartDens=[<?php echo $ChartFirst;?>]
const chartTemp=[<?php echo $ChartSecond;?>]
// define constants to be displayed in diagram -> no php code needed in chart
const recipe_name=[<?php echo "'".$recipe_name."'";?>]
const first_y=[<?php echo "'".$first_y."'";?>]
const second_y=[<?php echo "'".$second_y."'";?>]
const first_y_min = <?php echo $first_y_min;?>;
const second_y_min = <?php echo $second_y_min;?>;
const first_y_max = <?php echo $first_y_max;?>;
const second_y_max = <?php echo $second_y_max;?>;
const first_y_unit = [<?php echo "'".$first_y_unit."'";?>]
const second_y_unit = [<?php echo "'".$second_y_unit."'";?>]
const x_axis=[<?php echo "'".$x_axis."'";?>]
const tooltip_at=[<?php echo "'".$tooltip_at."'";?>]
const tooltip_time=[<?php echo "'".$tooltip_time."'";?>]
const archive_end=[<?php echo "'".$archive_end."'";?>]
const time_selected=[<?php echo "'".$time_selected."'";?>]
const RecipeName=[<?php echo "'".$RecipeName."'";?>]

// function to add comment to archive for selected recipe and reload page
function reload_page() {
    var comment_text = document.getElementById('comment').value;
    var dia_type = document.getElementById('diagram_type').value;
    var recipe_id = '<?php echo $selected_recipe ?>';
    var variable_r = '?recipe_id='.concat(recipe_id);
    var variable_t = '&type='.concat(dia_type);
    var variable_end = '&RID_END='.concat(Math.round(end_date/1000));
    var variable_c = '&comment='.concat(comment_text);
    var url = "http://";
    var server = window.location.hostname;
    var path = window.location.pathname;
    var full_path = url.concat(server).concat(path).concat(variable_r).concat(variable_t).concat(variable_end).concat(variable_c);
    window.open(full_path,"_self");
    }

// diagram preparation
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
        var labelText = RecipeName;
        chart = new Highcharts.Chart(
        {    
            chart:
            {   height: (7 / 16 * 100) + '%', // 16:9 ratio
                backgroundColor: 'rgba(0,0,0,0)',
                renderTo: 'container'
            },
            title:
            {
                text: null //chart_header
            },
            subtitle:
            { 
                      text: null //chart_subheader                 
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
            plotOptions: {
                series: {
                    allowPointSelect: true,
            point: {
                events: {
// function if point in chart is selected
// comment field will be shown timestamp of selected point will be written to end_date variable
                    select: function () {
                        var text = Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', new Date(this.x)) + time_selected;
                            end_date = this.x;
                            chart = this.series.chart;
                            const timestamp = end_date;
                        if (!chart.lbl) {
                            chart.lbl = chart.renderer.label(text, 100, 10)
                                .attr({
                                    padding: 10,
                                    r: 5,
                                    fill: Highcharts.getOptions().colors[1],
                                    zIndex: 5
                                })
                                .css({
                                    color: '#FFFFFF'
                                })
                                .add();
                                document.getElementById('Commentfield').style.display = "block";

                        } else {
                            chart.lbl.attr({
                                text: text
                            });
                        }
                    }
                }
            }
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
                    startOnTick: true,
                    endOnTick: true,
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
// Tooltip will show data at cursor position for datetime / and value
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
// labels will be shown if comment is included in database: these labels are above the dataline
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
// labels will be shown if comment is included in database: these labels are below the dataline (if two labels are close together, both (up and down) will be shown)
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

                }
            ] //series      

    });
    });
  });

</script>
</head>

<body class='<?php echo $document_class ?>'>
<div id="wrapper">
<div id="summary_table">
<!-- select options for  archives-->
<?php
// Table that shows the select boxes and the data summary
echo "<table border='1'>";
echo "<tbody>";
echo "<tr>";
echo "<td><b>$txt_archive :</b></td><td>";

// show results from the archive select if the table has content
if ($len != 0)
{
    echo "<select id='archive_name' name = 'archive_name'>";
    while($row = mysqli_fetch_assoc($archive_result) )
    {
        $start = $row['Start_date'];
        $newDate = date("Y-m-d", strtotime($start));
// $ID is used for parameter archive_name that is used as _POST value in selects
        $ID = $row['Recipe_ID'];
        if ($selected_recipe==$ID) 
        {
            echo "<option value = '$ID' selected>";
        }
        else
        {
            echo "<option value = '$ID'>";
        }
        echo($row['Recipe_ID']." | ".$row['Name']." | ".$newDate." | ".$row['Recipe']);
    }

    echo "</option>";
    echo "</select>";

}
echo "</td><td align='center'>";
echo "<span title='$stop'><input type = 'submit' id='Stop' name = 'Stop' value = '$stop'></span>";

echo "</td><td></td>";
echo "<td><b>Device:</b></td>";
echo "<td align='center'>$SpindleName</td>";
echo "<td><b>$recipe_name</b></td>"; 
echo "<td align='center'>$RecipeName</td>";
echo "<td><b>Start:</b></td>";
echo "<td align='center'>$start_date</td>";
echo "<td><b>$txt_end :</b></td>";
echo "<td align='center'>$end_date</td>";
echo "</tr>";

echo "<tr><td><b>Diagram :</b></td>";
echo "<td align='center'>";
echo "<select id='diagram_type' name='diagram_type'>";
echo "<option value='0'";
if ($diagram_type == 0) echo " selected";
echo ">$chart_filename_04</option>";
echo "<option value='1'";
if ($diagram_type == 1) echo " selected";
echo ">$chart_filename_06</option>";
echo "<option value='2'";
if ($diagram_type == 2) echo " selected";
echo ">$chart_filename_08</option>";
echo "<option value='3'";
if ($diagram_type == 3) echo " selected";
echo ">$chart_filename_12</option>";
echo "<option value='4'";
if ($diagram_type == 4) echo " selected";
echo ">$chart_filename_10</option>";
echo "</select>";


echo "</td><td align='center'>"; // Diagram type selection
echo "<span title='$show_diagram'><input type = 'submit' id='Go' name = 'Go' value = '$show_diagram'></span>";

echo "</td><td></td>";
echo "<td><b>$txt_initial_gravity :</b></td>";
echo "<td align='center'>" . number_format($initial_gravity,1) . " °P</td>";
echo "<td><b>$txt_final_gravity :</b></td>";
echo "<td align='center'>" . number_format($final_gravity,1) . " °P</td>";
echo "<td><b>$txt_attenuation :</b></td>";
echo "<td align='center'>" . number_format($attenuation,1) ." %</td>";
echo "<td><b>$txt_alcohol :</b></td>";
echo "<td align='center'>". number_format($alcohol,1) ." Vol%</td>";


echo "</tr>";
echo "<tr><td rowspan='2'><b>$comment_text</b></td>";
echo "<td rowspan='2' align='center'>";
echo "<div id='Commentfield' style='display: none;'>";
echo "<input type = 'input' id='comment' name = 'comment'>";
echo "<button type='button' id='send_comment'>$archive_end</button>";
echo "</div>";
echo "</td><td rowspan='2' align='center'>";
if($rid_end_exists == 1)
{
    echo "<span title='" . "$archive_end_removal" . "'><input type = 'submit' id='Remove' name = 'Remove' value = '" . "$archive_end_removal" . "'></span>";
    echo "<br/>";
}
else 
{
    echo "<br/>";
}

echo "<span title='$delete_archive'><input type = 'submit' id='delete' name = 'Del' value = '$delete_archive'></span>";

echo "</td><td rowspan='2'></td>";
echo "<td><b>$txt_calibration :</b></td>";
echo "<td align='center' colspan='7'>";
if ($cal == 1){
    if ($const0 == 0){
        printf("%01.5f * tilt^2 %+01.5f * tilt %+01.5f",$const1,$const2,$const3);
        }
    else {
        printf("%01.5F * tilt^3 %+01.5f * tilt^2 %+01.5f * tilt %+01.5f",$const0,$const1,$const2,$const3);
        }
}
else {
    echo "N/A";
}
echo "</td></tr>";
echo "<tr><td align='center' colspan='8'>"; 
echo "<span title='Export'><input type = 'submit' id='Export' name = 'Export' value = 'Export'></span>";
echo "<input type='radio' name='radio_csv' value='csv1' checked='checked' /> CSV";
echo "<input type='radio' name='radio_csv' value='csv2' /> Beersmith";
echo "<input type='radio' name='radio_csv' value='csv3' /> KBH2 CSV";
echo "</td></tr>";
echo "</tbody>";
echo "</table>";
?>
<input type = "hidden" name="txt_recipe_name" value="<?php echo $recipe_name; ?>">
<input type = "hidden" name="txt_end" value="<?php echo $txt_end; ?>">
<input type = "hidden" name="txt_initial_gravity" value="<?php echo $txt_initial_gravity; ?>">
<input type = "hidden" name="initial_gravity" value="<?php echo  number_format($initial_gravity,1); ?>">
<input type = "hidden" name="txt_final_gravity" value="<?php echo $txt_final_gravity; ?>">
<input type = "hidden" name="final_gravity" value="<?php echo  number_format($final_gravity,1); ?>">
<input type = "hidden" name="txt_attenuation" value="<?php echo $txt_attenuation; ?>">
<input type = "hidden" name="attenuation" value="<?php echo number_format($attenuation,1); ?>">
<input type = "hidden" name="txt_alcohol" value="<?php echo $txt_alcohol; ?>">
<input type = "hidden" name="alcohol" value="<?php echo  number_format($alcohol,1); ?>">
<input type = "hidden" name="txt_calibration" value="<?php echo $txt_calibration; ?>">




</div>

  <script src="include/highcharts.js"></script>
  <script src="include/modules/exporting.js"></script>
  <script src="include/modules/offline-exporting.js"></script>
  <div id="container" style="width: 95%;position: relative;"></div>
</div>
<script> document.querySelector('#send_comment').addEventListener('click', reload_page); </script>
</body>
</form>
</html>
