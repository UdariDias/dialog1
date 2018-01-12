<?php require_once('connection.php');
$page = $_SERVER['PHP_SELF'];
$sec = "300";


$con->query('DROP TABLE IF EXISTS query_table');
$create="CREATE  TABLE query_table AS
									SELECT LSN,
									STP_LINK,
									CONCAT(STP_LINK,'_',LSN) AS STP_LSN,
									TIMESTAMP(DATE,SEC_TO_TIME((TIME_TO_SEC(TIME) DIV 300) * 300)) AS timestamp,
									CONCAT(LSN,'_',STP_LINK,'_',TIMESTAMP(DATE,SEC_TO_TIME((TIME_TO_SEC(TIME) DIV 300) * 300))) AS data_1,
									TXTPS AS data_2,
									CONCAT(LSN,'_',STP_LINK,'_',DATE_SUB(TIMESTAMP(DATE,SEC_TO_TIME((TIME_TO_SEC(TIME) DIV 300) * 300)), INTERVAL 7 DAY)) AS data_3
									FROM iptps
									";
$con->query($create);

$con->query('DROP TABLE IF EXISTS query_table_2');
$query = "CREATE TEMPORARY TABLE query_table_2 AS
									SELECT 	currdata.data_1,
									 currdata.data_2,
        	 				 currdata.LSN,
									 currdata.STP_LSN,
									 currdata.STP_LINK,
									 currdata.timestamp,
        	 				 prevdata.data_2 AS prev_tx,
        	 				 (currdata.data_2 - prevdata.data_2) as diff
        	 				 FROM query_table AS currdata
        	 				 LEFT JOIN query_table AS prevdata ON currdata.data_3 = prevdata.data_1
									 WHERE  (prevdata.data_1 IS NOT NULL) AND currdata.timestamp = ( SELECT MAX( timestamp) FROM query_table ) 
 									 ORDER BY currdata.LSN, currdata.STP_LINK ASC";
$con->query($query);

$con->query('DROP TABLE IF EXISTS query_table_3');
$transpose ="CREATE Temporary TABLE query_table_3 AS
									SELECT  @row_num := IF(@prev_value=currdata.LSN,@row_num+1,1) AS RowNumber,
									currdata.STP_LINK,
        					currdata.LSN,
									currdata.data_2,
									currdata.prev_tx,
									currdata.diff,
       						@prev_value := currdata.LSN
  								FROM query_table_2 currdata,
     							(SELECT @row_num := 1) x,
     							(SELECT @prev_value := '') y
  								ORDER BY currdata.LSN, currdata.STP_LINK ASC";
$con->query($transpose);

$final =" 	SELECT LSN,
sum(prev_tx*(1-abs(sign(RowNumber-1)))) AS STPC1,
sum(data_2*(1-abs(sign(RowNumber-1)))) as STPC2,
sum(diff*(1-abs(sign(RowNumber-1)))) AS STPC3,

sum(prev_tx*(1-abs(sign(RowNumber-2)))) as STPD1,
sum(data_2*(1-abs(sign(RowNumber-2)))) as STPD2,
sum(diff*(1-abs(sign(RowNumber-2)))) AS STPD3,

sum(prev_tx*(1-abs(sign(RowNumber-3)))) as STPE1,
sum(data_2*(1-abs(sign(RowNumber-3)))) as STPE2,
sum(diff*(1-abs(sign(RowNumber-3)))) AS STPE3,

sum(prev_tx*(1-abs(sign(RowNumber-4)))) as STPF1,
sum(data_2*(1-abs(sign(RowNumber-4)))) as STPF2,
sum(diff*(1-abs(sign(RowNumber-4)))) AS STPF3
from query_table_3 group by LSN";
$result_set =  $con->query($final);

 ?>
 <!DOCTYPE html>
 <html>
      <head>


           <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
           <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />



           <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
           <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
           <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
					 <meta name="viewport" content="width=device-width, initial-scale=1.0">
					 <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
      </head>
      <body>



           <br /><br />
           <div class="container" style="height:100%; width: 100%">

                <br />
                <div class="table-responsive">
                     <table id="test" class="table table-striped table-bordered">
                          <thead>
                               <tr>
                               <th  rowspan ="2"> LSN </th>
                               <th  colspan="3">STP C</th>
                               <th  colspan="3">STP D</th>
                               <th  colspan="3">STP E</th>
                               <th  colspan="3">STP F</th>
                               </tr>
                               <tr>
                               <th> prev_tx</th>
                               <th> curr_tx</th>
                               <th> difference</th>
                               <th> prev_tx</th>
                               <th> curr_tx</th>
                               <th> difference</th>
                               <th> prev_tx</th>
                               <th> curr_tx</th>
                               <th> difference</th>
                               <th> prev_tx</th>
                               <th> curr_tx</th>
                               <th> difference</th>
                               </tr>
                          </thead>
                          <?php
                          while($row = mysqli_fetch_array($result_set))
                          {
                               echo '
                               <tr>
                                    <td>'.$row["LSN"].'</td>
                                    <td bgcolor="grey">'.$row["STPC1"].'</td>
																		<td bgcolor="grey">'.$row["STPC2"].'</td>
																		<td bgcolor="grey">'.$row["STPC3"].'</td>
                                    <td>'.$row["STPD1"].'</td>
																		<td	>'.$row["STPD2"].'</td>
																		<td>'.$row["STPD3"].'</td>
																		<td bgcolor="grey">'.$row["STPE1"].'</td>
																		<td bgcolor="grey">'.$row["STPE2"].'</td>
																		<td bgcolor="grey">'.$row["STPE3"].'</td>
																		<td>'.$row["STPF1"].'</td>
																		<td>'.$row["STPF2"].'</td>
																		<td>'.$row["STPF3"].'</td>

                               </tr>
                               ';
                          }
                          ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <title>Select Query</title>
  <style>
    table {border-collapse: collapse;}
    td,th {border: 1px solid black; padding: 8px;}
  </style>
      <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
</head>
<body>
  <nav class="navbar navbar-inverse">
  <div class="container-fluid">

    <ul class="nav navbar-nav">
      <li class="active"><a href="navigate.php">Home</a></li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="localhost/STP/navigate.php">Graph <span class="caret"></span></a>
        <ul class="dropdown-menu" style="overflow-y: auto;height: 500px;">
          <input class="form-control" id="myInput" type="text" placeholder="Search..">
          <?php

   $sql3= "SELECT DISTINCT LSN AS distinctLSN FROM iptps"; 
   $result3 = $con->query($sql3);

// echo $sql3;

while($row = $result3->fetch_assoc()){

  $lsn=$row['distinctLSN'];  
  // echo $lsn . "</br>";
  echo '<li><a href="http://localhost/STP/navigate.php?lsn=' . $lsn . '">' . $lsn . '</a></li>';

}
  ?>
        
        </ul>
      </li>
      <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="localhost/STP/navigate.php">Table <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <input class="form-control" id="myInput" type="text" placeholder="Search..">
          <li><a href="http://localhost/STP/table.php">Selected ones</a></li>
          <li><a href="http://localhost/STP/table.php">All</a></li>
    </ul>
  </div>
</nav>
                     </table>
                </div>
           </div>
      </body>
 </html>
 <script>
 $(document).ready(function(){
      $('#test').DataTable({
				"scrollY": false
				// "lengthMenu": [ 15, 25, 50, 75, 100 ]
			});
 });
 </script>
