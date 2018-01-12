<?php

include('connection.php');

$linkID = $_GET['linkID'];
$queryDate = $_GET['queryDate'];
$stp = $_GET['stp'];

function multiexplode($delimiters,$string) {
    
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}

//$sql="SELECT DATE,TIME,TXTPS FROM iptps WHERE LSN = 'cdmasmscbb' AND DATE = '2017-11-28'";
$sql="SELECT DATE,TIME,TXTPS FROM iptps WHERE LSN = '" . $linkID . "' AND DATE = '" . $queryDate . "'"; //get current data from sql db
     
$result = $con->query($sql);

$list=Array();

while($row = $result->fetch_assoc()){ //fetch data for graph
			
	$data_date=$row['DATE'];	
	$data_time=$row['TIME'];	
	$y=$row['TXTPS'];


	list($year,$month,$date)=multiexplode(array(":","-"),$data_date);
	list($hour,$min,$sec)=multiexplode(array(":","."),$data_time);

	$month=$month-1;

	$x="Date.UTC( $year, $month, $date, $hour, $min, $sec)";

	$coordinate="[$x,$y]";

	$list[]=$coordinate;

	//echo $coordinate."<br>";
}

$sql1="SELECT DATE,TIME,TXTPS FROM iptps WHERE LSN = '" . $linkID . "' AND (DATE = DATE_SUB('2017-11-28', INTERVAL 7 DAY))"; //get previous week data
//$DATE=DATE-7;
$result1 = $con->query($sql1);

$list1=Array();

while($row = $result1->fetch_assoc()){
			
	$data_date=$row['DATE'];	
	$data_time=$row['TIME'];	
	$y=$row['TXTPS'];


	list($year,$month,$date)=multiexplode(array(":","-"),$data_date);
	list($hour,$min,$sec)=multiexplode(array(":","."),$data_time);
	//echo $hour."<br>";
	//echo $min."<br>";
	//echo $sec."<br>";

	$month=$month-1;

	$x="Date.UTC( 2017, 10, 28, $hour, $min, $sec)";

	$coordinate="[$x,$y]";

	$list1[]=$coordinate;

	//echo $coordinate."<br>";
}

if(count($list)>0){
	$coordinates=implode(",",$list);
	//echo $coordinates;
}

if(count($list1)>0){
	$coordinates1=implode(",",$list1);
	//echo $coordinates;
}


?>

 
<!DOCTYPE html>
<html>
<body bgcolor="#E6E6FA">

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>

<div id="container" style="height: 400px; min-width: 310px"></div>
<script>
    
$.getJSON('https://www.highcharts.com/samples/data/jsonp.php?filename=aapl-c.json&callback=?', function (data) {
    // Create the chart
    Highcharts.stockChart('container', {


        rangeSelector: {
            selected: 1
        },

        title: {
            text: '<?php echo $stp;?> TXTPS with TIME', //display titel
             style: {
               color: '#2f7ed8',
            fontWeight: 'bold',
            fontSize: '20px'
            }    
        },

        subtitle: {
        text: '<?php echo  $linkID;?>', //display subtitel
        style: {
            color: '#FF00FF',
            // fontWeight: 'bold',
            fontSize: '20px'
        }
    },

 
        xAxis: {                     //X axis
           type: 'datetime',
        dateTimeLabelFormats: {
         second: '%H:%M.%S'

        }
        },

        series: [{
            name: 'IPTPS',
            data: [<?php echo $coordinates; ?>],
            tooltip: {
                valueDecimals: 2
            }
        },{
            name: 'IPTPS',
            data: [<?php echo $coordinates1; ?>],
            tooltip: {
                valueDecimals: 2
            }
        }
		]
    });
});


</script>

</body>
</html>