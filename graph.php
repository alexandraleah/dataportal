<?php
// error handler
function customError($errno, $errstr) {
  require_once('errorpage.php');
  die();
}
set_error_handler("customError");
	//  Get variables submitted in form
	 $parameter=$_POST["parameter-graph"];
	 $year=$_POST["date-graph"];
	 $site=$_POST["site-graph"];
	 //log into database
	 require_once 'login.php';
  	$conn = new mysqli($hn, $un, $pw, $db);
	if ($conn->connect_error) die($conn->connect_error);
	//Looking up site details
	$query = "SELECT Site_Name, Town, Latitude, Longitude
				FROM Monitoring_Sites WHERE Site_ID='$site'";
	$result = $conn->query($query);
	while ($row = mysqli_fetch_array($result)) {
   	$mysite = $row['Site_Name'];
    $town = $row['Town'];
    $lat = $row['Latitude'];
    $long = $row['Longitude'];
}
	$result ->close();

//query database for the given site, parameter, and year
	$contents = '';
	$query = "SELECT Reporting_Result, DATE_FORMAT(Date_Collected,'%m/%d/%Y') AS Date, Site_ID, Component_ID
				FROM Results WHERE Site_ID='$site' AND Component_ID='$parameter' AND EXTRACT(YEAR FROM Date_Collected)=$year";
	$result = $conn->query($query);
	while ($row = mysqli_fetch_array($result)) {
//Put reporting results into an array called $data
  	$data[]=$row['Reporting_Result'];
//create contents for data table
  	$contents = $contents . '<tr><td>' . $row['Date'] . '</td><td>' . $row['Reporting_Result']  . '</td></tr>' ;
}
//Convert the array containing the data into strings seperated with commas for use in highcharts script
	$series=join($data, ',');
	$result ->close();

//query databases for Months that samples were collected
	$query = "SELECT MONTHNAME(Date_Collected) AS Month
				FROM Results WHERE Site_ID='$site' AND Component_ID='$parameter' AND EXTRACT(YEAR FROM Date_Collected)=$year";
	$result = $conn->query($query);
	while ($row = mysqli_fetch_array($result)) {
  	$Month[]=$row['Month'];
}
$categories = "'" . join($Month, "', '") . "'";
//Set title, y-axis and standard values based on parameter
	if($parameter==26){
		$yAxis='Temperature (°C)';
		$title='Water Temperature';
		$standard1=28.3;
		$label1='Temperature Standard';
//Since temperature has only one standard set the second standard so that it will never display on the graph
		$standard2= 400;
		$label2='';
	}
	if ($parameter==12){
		$yAxis = 'E. Coli (cfu/100ml)';
		$title='E. Coli';
		$standard1=630;
		$label1='Boating Standard';
		$standard2=126;
		$label2='Swimming Standard';
	}
  $pic = 'images/Image_' . $site . '.jpg';
//output html page
echo <<<_END
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Your Graph | Charles River Water Quality Data</title>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
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
    <h2>Water quality data for $year at $mysite</h2>
		<main>
      <div id="content">
        <p>Below find a map and data table for samples collected at $mysite in $year. This project uses data from <a href="http://www.crwa.org/field-science/monthly-monitoring">Charles River Watershed Associaton's volunteer monthly monitoring program</a>.</p>
        <a href="index.php" class="mybutton">Start a new search</a>
        <h3>$title graph</h3>
        <div id="container">
        </div>
        &nbsp;
<script>
$(function () {
    Highcharts.setOptions({
    chart: {
        style: {
            fontFamily: '"Helvetica Neue",Helvetica,Arial,sans-serif',
                fontSize: '13px',
                fontColor: 'blue',
        }
    }
});
    var myChart = Highcharts.chart('container', {
        chart: {
            type: 'column'
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
            data: [$series],
        }, ]
    });
});

</script>
      <h3>$title data table</h3>
		  <table class="datatable">
			  <tr>
          <th>Date</th>
				  <th>$yAxis</th>
			  </tr>
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
$conn->close();
?>
