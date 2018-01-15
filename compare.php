<?php
//error handler
function customError($errno, $errstr) {
	require_once('errorpage.php');
  	die();
}
set_error_handler("customError");
//Get variables submitted in form
$parameter=$_POST["parameter-compare"];
$month=$_POST["month-compare"];
$dateObj   = DateTime::createFromFormat('!m', $month);
$monthName = $dateObj->format('F');
$site=$_POST["site-compare"];
//log into database
require_once 'login.php';
  $conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);
//Look up site details
$query = "SELECT Site_Name, Town, Latitude, Longitude
					FROM Monitoring_Sites WHERE Site_ID='$site'";
$result = $conn->query($query);
while ($row = mysqli_fetch_array($result)) {
	$mysite = $row['Site_Name'];
	$town = $row['Town'];
	$lat = $row['Latitude'];
	$long = $row['Longitude'];
}
//Close result
$result ->close();
$contents = '';//Create an empty string to fill with contents of table.
//query database for the given site, parameter, and month
$query = "SELECT Reporting_Result, DATE_FORMAT(Date_Collected,'%m/%d/%Y') AS Date, Site_ID, Component_ID
					FROM Results WHERE Site_ID='$site' AND Component_ID='$parameter' AND EXTRACT(MONTH FROM Date_Collected)=$month";
$result = $conn->query($query);
while ($row = mysqli_fetch_array($result)) {
 //create contents for data table
	$contents = $contents . '<tr><td>' . $row['Date'] . '</td><td>' . $row['Reporting_Result']  . '</td></tr>' ;
//Create an array containing each reporting result.
	$data[]=$row['Reporting_Result'];
}
//Prepare the data for highcharts by creating a string containing each datapoint separated with commas
	$series = join($data, ',');
//query databases for years that samples were collected
	$query = "SELECT EXTRACT(YEAR FROM Date_Collected) AS Year
				FROM Results WHERE Site_ID='$site' AND Component_ID='$parameter' AND EXTRACT(MONTH FROM Date_Collected)=$month";
	$result = $conn->query($query);
//Create an array containing each reporting result.
	while ($row = mysqli_fetch_array($result)) {
  	$year[]=$row['Year'];
}
//Prepare the data for highcharts by creating a string containing each year separated with commas
	$categories = join($year, ',');
	$result ->close();
//Create title, y-axis, standards and labels for the standards based on parameter chosen. (If I decide to add more parameters, I should set this up as a case statement, instead of an if statement)
	//If the user chose temperature (ID 26)
	if($parameter==26){
		$yAxis='Temperature (°C)';
		$title='Water Temperature';
		$standard1=28.3;
		$label1='Temperature Standard';
//Since temperature has only one standard set the second standard so that it will never display on the graph
		$standard2= 400;
		$label2='';
	}
	//If the user chose E. Coli (ID 12)
	if ($parameter==12){
		$yAxis = 'E. Coli (cfu/100ml)';
		$title='E. Coli';
		$standard1=630;
		$label1='Boating Standard';
		$standard2=126;
		$label2='Swimming Standard';
	}
	$pic = 'images/Image_' . $site . '.jpg';
//html of page begins
echo <<<_END
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Your Graph | Charles River Water Quality Data</title>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<!--Highcharts javascript hosted on highcharts website-->
		<script src="http://code.highcharts.com/highcharts.js"></script>
		<script src="http://code.highcharts.com/modules/exporting.js"></script>
		<script	src="js/script.js" type="text/javascript" ></script>
	</head>
	<body>
		<div id="wrapper">
			<header>
				<a href="http://www.crwa.org"><img id="logo" src="images/logo.jpg" alt="Charles River Watershed Association"></a>
				<h1>CHARLES RIVER WATERSHED ASSOCIATION</h1>
				<img id="banner" src="images/VMM_main_banner.jpg" alt="banner">
			</header>
			<h2>Water quality data for $mysite </h2>
			<main>
				<div id="content">
				<p>Below find a map and data table for samples collected at $mysite in $monthName. This project uses data from <a href="http://www.crwa.org/field-science/monthly-monitoring">Charles River Watershed Associaton's volunteer monthly monitoring program</a>.</p>
					<a href="index.php" class="mybutton">Start a new search</a>
					<h3>Graph for $monthName sampling</h3>
<!--Create container to display chart-->
					<div id="container">
					</div>
					&nbsp;
<!--Javascript function to create chart-->
<script>
	$(function () {
//Set highchart options
		Highcharts.setOptions({
		chart: {
			style: {
				fontFamily: '"Helvetica Neue",Helvetica,Arial,sans-serif',
				fontSize: '13px',
				fontColor: 'blue',
			}
		}
	});
//Create chart. Use php variables defined above to display data specific to the user's search.
	var myChart = Highcharts.chart('container', {
		chart: {
			type: 'spline'
		},
		title: {
			text: '$title'
		},
		xAxis: {
			categories: [$categories]
		},
		yAxis: {
			title: {
				text: '$yAxis'
				},
//The plotlines display the standard for the parameter
			plotLines: [{
				color: 'red',
				dashStyle: 'solid',
				value: $standard1,
				width: 2,
				label: {
					text: '$label1'
				}
			},
				{
				color: 'yellow',
				dashStyle: 'solid',
				value: $standard2,
				width: 2,
				label: {
					text: '$label2'
					}
				}]
			},
			series: [{
				name: '$mysite',
				data: [$series]
			}]
		});
	});
	</script>
			<h3>Data table for $monthName smapling</h3>
			<table class="datatable">
				<tr>
					<th>Date</th>
					<th>$yAxis</th>
				</tr>
<!--Displays the contents created above in php-->
				$contents
			</table>
		</div>
		<div id="sidebar">
			<h3>About $mysite</h3>
			<img src="$pic">
			<p>Site ID: $site<br>
				Town: $town<br>
				Coordinates: $lat, $long<br>
			</p>
			<h3>About Volunteer Monthly Monitoring</h3>
			<a href="http://www.crwa.org/field-science/monthly-monitoring" target="_blank">Volunteer Monthly Monitoring Overview</a><br>
			<a href="http://www.crwa.org/field-science/monthly-monitoring/sampling-sites" target="_blank">Sampling Sites</a><br>
			<a href="http://www.crwa.org/citizen-scientist-application" target="_blank">Become a Volunteer</a><br>
			<a href="http://www.crwa.org/field-science/monthly-monitoring/volunteer-resources" target="_blank">Volunteer Resources</a><br>
			<h3>About Field Science</h3>
			<a href="http://www.crwa.org/field-science" target="_blank">Field Science Overview</a><br>
			<a href="http://www.crwa.org/american-shad-restoration" target="_blank">American Shad Restoration Program</a><br>
			<a href="http://www.crwa.org/field-science/water-quality-notification" target="_blank">Water Quality Notification Program</a><br>
			<a href="http://www.crwa.org/canoeing-for-clean-water" target="_blank">Canoeing for Clean Water</a><br>
			<a href="http://www.crwa.org/education/watershed-scientist-app" target="_blank">Watershed Scientist App</a><br>
			<h3>Learn More About Our Work</h3>
			<a href="http://www.crwa.org/projects" target="_blank">Project Overview</a><br>
			<a href="http://www.crwa.org/blue-cities" target="_blank">Blue Cities Inititative<a><br>
			<a href="http://www.crwa.org/blue-cities-exchange" target="_blank">Blue Cities Exchange</a><br>
			<a href="http://www.crwa.org/climate-change-adaptation" target="_blank">Climate Change Adaptation</a><br>
			<a href="http://www.crwa.org/law-advocacy-and-policy" target="_blank">Law, Advocacy &#038; Policy</a><br>
			<a href="http://www.crwa.org/smart-sewering" target="_blank">Smart Sewering</a><br>
			<a href="http://www.crwa.org/twinning" target="_blank">Twinning</a><br>
			<br>
		</div>
	</main>
_END;
require_once('footer.php');
echo <<<_END
</div>
</body>
</html>
_END;
//Close the connection
$conn->close();
?>
