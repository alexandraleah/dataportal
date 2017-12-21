<?php
//Save the parameter the user requests in a variable
  $parameter=$_POST["parameter_download"];
//connect to database
  require_once 'login.php'; //require login file
  $conn = new mysqli($hn, $un, $pw, $db); 
	if ($conn->connect_error) die($conn->connect_error);
//If user chose temperature
if($parameter==26) {
//name of file
  $filename = "vmm-temperature-data" . ".xls";
  header("Content-Disposition: attachment; filename=\"$filename\"");
//Query database
	$query = "SELECT Site_ID as 'ID', Site_Name as 'Name', Town, River_mile_Headwaters AS 'River Mile', 
					Date_Collected AS 'Date', Reporting_Result
					FROM Results NATURAL JOIN Monitoring_Sites 
					WHERE Component_ID = '26'";
	$result = $conn->query($query); 
//start file	
	echo "Charles River Watershed Association			
Monthly Water Quality Data\r\n";
//Title for each column	
	echo "Site ID \t Site Name \t Town \t River Mile \t Date \t Temperature deg. C\r\n";
//Populating the spreadsheet with data 
	while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)){
echo $row['ID'] . "\t" . $row['Name'] . "\t" . $row['Town'] . "\t" . $row['River Mile'] . "\t" . $row['Date']
. "\t" . $row['Reporting_Result'] . "\r\n";
  }
  $result ->close();
  }
//Simliarly if user picked the e-coli parameter
if($parameter==12) {
  	  $filename = "vmm-ecoli-data" . ".xls";
  header("Content-Disposition: attachment; filename=\"$filename\"");

	$query = "SELECT Site_ID as 'ID', Site_Name as 'Name', Town, River_mile_Headwaters AS 'River Mile', 
					Date_Collected AS 'Date', Reporting_Result, Unit_ID 
					FROM Results NATURAL JOIN Monitoring_Sites 
					WHERE Component_ID = '12'";
	$result = $conn->query($query); 
	echo "Charles River Watershed Association			
Monthly Water Quality Data
E. Coli reported as MPN/100ml or cfu/100ml\r\n";
	echo "Site ID \t Site Name \t Town \t River Mile \t Date \t E. coli \r\n";
  
	while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)){
echo $row['ID'] . "\t" . $row['Name'] . "\t" . $row['Town'] . "\t" . $row['River Mile'] . "\t" . $row['Date']
. "\t" . $row['Reporting_Result'] . "\r\n";
  }
  $result ->close();
  }
//close connection to database
	$conn->close();	
?>