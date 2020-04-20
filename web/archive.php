<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

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

$min_recipe_id = "SELECT min(Recipe_ID) FROM Archive";
$q_sql = mysqli_query($conn, $min_recipe_id) or die(mysqli_error($conn));
$result = mysqli_fetch_array($q_sql);
$selected_recipe = $result[0];

// Check GET parameters if set
if(isset($_GET['recipe_id'])){
    $selected_recipe = $_GET['recipe_id'];
}

$rid_end_exists = 0;

//check if flag for end of fermenation is already existing
$check_RID_END = "SELECT Timestamp FROM Data WHERE Recipe_ID = '$selected_recipe' AND Internal = 'RID_END'";
$q_sql = mysqli_query($conn, $check_RID_END) or die(mysqli_error($conn));
$rows = mysqli_num_rows($q_sql);
if ($rows <> 0){
    $rid_end_exists = 1;
    $result = mysqli_fetch_array($q_sql);
    $timestamp_rid = $result[0];
}

if(!isset($_GET['comment']))
    {
    $comment = '';
    }
else {
    $comment = $_GET['comment'];
    }

if(!isset($_GET['RID_END']))
    { 
    $RID_END = ''; 
    }
else {
    $RID_END = intval($_GET['RID_END']);

    if($comment == ''){
        //if flag for end of fermenation is already existing: remove it
        if ($rid_end_exists == 1)
        {
            $remove_recipe_ID="UPDATE Data Set Internal = NULL WHERE Recipe_ID = '$selected_recipe' AND Timestamp = '$timestamp_rid'";
            $q_sql = mysqli_query($conn, $remove_recipe_ID) or die(mysqli_error($conn));
        }

        //add Flag for end of fermentation for archive to last datapoint of current spindle
        $timestamp_add= intval($RID_END/1000);
        $add_recipe_ID="UPDATE Data Set Internal = 'RID_END' WHERE Recipe_ID = '$selected_recipe' AND UNIX_TIMESTAMP(Timestamp) = '$timestamp_add'";
        $q_sql = mysqli_query($conn, $add_recipe_ID) or die(mysqli_error($conn));

    }
    else {
        $timestamp_add= intval($RID_END/1000);
        $add_recipe_ID="UPDATE Data Set Comment = '$comment' WHERE Recipe_ID = '$selected_recipe' AND UNIX_TIMESTAMP(Timestamp) = '$timestamp_add'";
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

if (isset($_POST['Remove']))
    {
        $recipe_id = $_POST['archive_name'];
        //delete active recipe
        delete_rid_flag_from_archive($conn,$recipe_id);
        // reload page for selected archive
        $url="http://";
        $url .= $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";
        $url .= "archive.php?recipe_id=".$recipe_id;
        // open the page
        header("Location: ".$url);
    }



$timeFrame = defaultTimePeriod;
$tftemp = $timeFrame;           
$tfweeks = floor($tftemp / 168);
$tftemp -= $tfweeks * 168;    
$tfdays = floor($tftemp / 24);
$tftemp -= $tfdays * 24;
$tfhours = $tftemp;                                
                                                   
list($SpindleName, $RecipeName, $start_date, $end_date, $dens, $temperature, $angle) = getArchiveValuesPlato4($conn, $selected_recipe);
list($isCalib,$initial_gravity, $const1, $const2, $const3) = getArchiveInitialGravity($conn, $selected_recipe);
list($isCalib,$final_gravity) = getArchiveFinalGravity($conn, $selected_recipe, $end_date);

$attenuation = ($initial_gravity - $final_gravity)*100 / $initial_gravity;
$real_dens = 0.1808 * $initial_gravity + 0.8192 * $final_gravity;
$alcohol = ((100* ($real_dens - $initial_gravity) / (1.0665 * $initial_gravity -206.65)) / 0.795);

$start_date = date("Y-m-d", strtotime($start_date));
$end_date = date("Y-m-d", strtotime($end_date));
$const1 = number_format($const1,4);
$const2 = number_format($const2,4);
if ($const2 < 0){
    $separator = "";
}
else {
    $separator = "+";
}
$const3 = number_format($const3,4);


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


// define header displayed in diagram depending on value for recipe
if ($RecipeName <> '') {
    $Header=$SpindleName.' | ' . $recipe_name .' ' . $RecipeName . ' | Start: ' . $start_date;
    }
else {
    $Header='iSpindel: ' . $SpindleName . ' | Start: ' . $start_date;
    }


// define subheader to be displayed in diagram
$timetext = $subheader . ' ';
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
<!--  <meta http-equiv="refresh" content="120"> -->
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
const archive_end=[<?php echo "'".$archive_end."'";?>]
const time_selected=[<?php echo "'".$time_selected."'";?>]
const RecipeName=[<?php echo "'".$RecipeName."'";?>]

function reload_page(end_date,comment_text) {
    var recipe_id = '<?php echo $selected_recipe ?>';
    var variable_r = '?recipe_id='.concat(recipe_id);
    var variable_end = '&RID_END='.concat(end_date);
    var variable_c = '&comment='.concat(comment_text);
    var url = "http://";
    var server = window.location.hostname;
    var path = window.location.pathname;
    var full_path = url.concat(server).concat(path).concat(variable_r).concat(variable_end).concat(variable_c);
    window.open(full_path,"_self");
    }

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
                    select: function () {
                        var text = Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', new Date(this.x)) + time_selected,
                            end_date = this.x,
                            chart = this.series.chart;
                            const timestamp = end_date;
                        if (!chart.lbl) {
                            chart.lbl = chart.renderer.label(text, 100, 70)
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
        chart.myButton = chart.renderer.button(archive_end,400,70)
            .attr({
                zIndex: 3
            })
            .on('click', function () {
            comment_text = document.getElementById('comment').value;
            reload_page(end_date,comment_text)
            })
            .add();

                    }
                }
            }
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
                            return Comment.text_up;
                        }
                    },
                   {
                        enabled: true,
                        shape: 'callout',
                        y: 35,
                        borderRadius: 5,
                        backgroundColor: 'rgba(252, 255, 255, 0.7)',
                        borderWidth: 1,
                        borderColor: '#000',
                        formatter: function() {
                            const Comment = chartDens.find(row => row.timestamp === this.point.x)
                            return Comment.text_down;
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
<body>
<div id="wrapper">
<div id="summary_table">
<!-- select options for  archives-->
<?php
echo "<table border='1'>";
echo "<tbody>";
echo "<tr>";
echo "<td><b>$txt_archive :</b></td><td>";

if ($len != 0)
{
    echo "<select id='archive_name' name = 'archive_name'>";
    while($row = mysqli_fetch_assoc($archive_result) )
    {
        $start = $row['Start_date'];
        $newDate = date("Y-m-d", strtotime($start));
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
echo "<td align='center'> Different Diagram styles to be added</td><td align='center'>"; // Diagram type selection
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
echo "</div>";
echo "</td><td rowspan='2' align='center'>";
if($rid_end_exists == 1)
{
    echo "<span title='" . "$archive_end_removal" . "'><input type = 'submit' id='Remove' name = 'Remove' value = '" . "$archive_end_removal" . "'></span>";
    echo "</br>";
}
else 
{
    echo "</br>";
}

echo "<span title='$delete_archive'><input type = 'submit' id='delete' name = 'Del' value = '$delete_archive'></span>";

echo "</td><td rowspan='2'></td>";
echo "<td><b>$txt_calibration :</b></td>";
echo "<td align='center' colspan='7'> $const1 * tilt $separator $const2 * tilt^2 + $const3";
echo "</td></tr>";
echo "<tr><td align='center' colspan='8'>"; 
echo "Export function to be added";
echo "</td></tr>";
echo "</tbody>";
echo "</table>";
?>
</div>

  <script src="include/highcharts.js"></script>
  <script src="include/modules/exporting.js"></script>
  <script src="include/modules/offline-exporting.js"></script>
  <div id="container" style="width: 95%;position: relative;"></div>
</div>
</body>
</form>
</html>
