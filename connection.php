<?php

$conerror='connection failed';

$host='localhost';
$username='root';
$pass='1993';
$mydb='dialog';


$con=mysqli_connect($host,$username,$pass);
//mysql_select_db($mydb);
if (!mysqli_connect($host,$username,$pass,$mydb) || !mysqli_select_db($con,$mydb)) {
die($conerror);
}

else{

}
?>