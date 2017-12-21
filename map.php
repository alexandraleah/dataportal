<?php
//error handler
	function customError($errno, $errstr) {
	require_once('errorpage.php');
  die();
	}
	set_error_handler("customError");
//Get variables submitted in form
	$parameter=$_POST["parameter-map"];
	$date=$_POST["date-map"];
	$friendlydate = date('F j, Y', strtotime($date));
//log into database
	require_once 'login.php';
  $conn = new mysqli($hn, $un, $pw, $db);
	if ($conn->connect_error) die($conn->connect_error);

//Set title, units and standard values based on parameter
	if($parameter==26){
		$heading='Temperature (&deg;C)';
		$units ='&deg;C';
		$title='Water Temperature';
		$standard1=28.3;
		$standard2=100;
		$label1='Temperature Standard';
		$caption = "The red markers represent samples that exceeded the water temperature standard of 28.3 &deg;C.";
		$legend = "<div><img src='images/red.png'>> $standard1 $units</div>
		<div><img src='images/blue.png'>< $standard1 $units</div>";
	}
	if ($parameter==12){
		$heading = 'E. Coli (cfu/100ml)';
		$units = 'cfu/100ml';
		$title='E. Coli';
		$standard1=630;
		$label1='Boating Standard';
		$standard2=126;
		$label2='Swimming Standard';
		$legend= "<div><img src='images/red.png'>> $standard1 $units</div>
		<div><img src='images/yellow.png'>< $standard1 $units</div>
		<div><img src='images/blue.png'>< $standard2 $units</div>";
		$caption = "<p>The blue markers represents samples that met the E. coli swimming standard of 126 colony forming units per 100 milliliters (about a teacup of water) (#/100ml). Yellow represents the samples that exceeded the swimming standard yet met the boating standard of 630/100ml. A violation of the boating standard is represented by the red color.
Standards are set by the Massachusetts Department of Environmental Protection (MassDEP) to indicate level of health risk in freshwaters.  MassDEP recommends not swimming or fishing when the geometric mean of bacteria counts exceed 126/100ml, and not boating on days when the geometric mean of levels rise above 630/100ml.</p>";
	}
//query database for results, monitoring sites and lat and long data
	$contents = '';
	$query ="SELECT Reporting_Result, Site_ID,  Latitude, Longitude, Town
			FROM Results NATURAL JOIN Monitoring_Sites
			WHERE Component_ID='$parameter' AND Date_Collected='$date' AND Site_ID NOT LIKE 'ROV%'
			ORDER BY River_mile_Headwaters";
	$result = $conn->query($query);
	$sitesarray = array();
    while($row =mysqli_fetch_assoc($result)){
//Output content for table
			$contents = $contents . '<tr><td>' . $row['Site_ID'] . '</td><td>' . $row['Town'] . '</td><td>' . $row['Reporting_Result']  . '</td></tr>' ;
//Output as 2d array
			$sitesarray[] = array_values($row);
    }
//Format as json to send to javascript
		$mysites = json_encode($sitesarray);
// Begin html page
echo <<<_END
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Your Map | Charles River Water Quality Data</title>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script	src="js/script.js" type="text/javascript" ></script>
	</head>
	<body>
		<div id="wrapper">
			<header>
				<a href="http://www.crwa.org"><img id="logo" src="images/logo.jpg" alt="Charles River Watershed Association"></a>
				<h1>CHARLES RIVER WATERSHED ASSOCIATION</h1>
				<img id="banner" src="images/VMM_main_banner.jpg" alt="banner">
			</header>
			<h2>Water Quality for $friendlydate</h2>
			<main>
				<div id="content">
					<p>Below find a map and data table for the samples collected on $friendlydate. Additional information about the
					sampling day can go here or more information about the data or map.</p>
					<a href="index.php" class="mybutton">Start a new search</a>
					<h3>$title Map</h3>
					<div id="mapcontainer">
						<div id="map"></div>
						<div id ="legend">
							<h4>Legend</h4>
							$legend
						</div>
					</div>&nbsp;
<!--Begin javascript function for google map-->
<script>
	var sites = JSON.parse('$mysites');
	var infowindow = null;
  $(document).ready(function () { initialize();  });
    function initialize() {
      var centerMap = new google.maps.LatLng(42.226499, -71.354711);
      var myOptions = {
          zoom: 10,
          center: centerMap,
          mapTypeId: google.maps.MapTypeId.ROADMAP
      }
      var map = new google.maps.Map(document.getElementById("map"), myOptions);
      setMarkers(map, sites);
	    infowindow = new google.maps.InfoWindow({
          content: "loading..."
      });
      var watershed = new google.maps.KmlLayer({
        url: 'https://dl.dropboxusercontent.com/s/cyjcmt10z0r2sz7/charles.kml',
        map: map,
				preserveViewport:true,
      });
		}
    function setMarkers(map, markers) {
    	for (var i = 0; i < markers.length; i++) {
				var sites = markers[i];
				var icon ='images/red.png';
				if (sites[0]>$standard1){
					icon = 'images/red.png';
				}
				else if ($parameter==12 && sites[0]>$standard2) {
					 icon = 'images/yellow.png';
				}
				else {
					  icon = 'images/blue.png';
				}
        var siteLatLng = new google.maps.LatLng(sites[2], sites[3]);
        var marker = new google.maps.Marker({
        	position: siteLatLng,
					icon: icon,
          map: map,
          html: '<h4> Site ' + sites[1] + ', ' + sites[4] + '</h4><img src = "images/Image_' + sites[1] + '.jpg" alt="site picture" style="width: 100%; max-width:200px;"><p> $title: ' + sites[0] + '$units</p>'
        });
        var contentString = "Some content";
        google.maps.event.addListener(marker, "click", function () {
          infowindow.setContent(this.html);
          infowindow.open(map, this);
        });
      }
			map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);
    }
</script>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDQ1-u0UjE-_uAbeH6HF8KhS8uDJQaWj-8&callback=initialize"></script>
<!--Create the datatable-->
					<h3>$title Data Table</h3>
					<table class="datatable">
						<tr>
							<th>Site</th>
							<th>Town</th>
							<th>$heading</th>
						</tr>
							$contents
						</table>
				</div>
				<div id="sidebar">
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
				</div>
			</main>
_END;
//include the footer
			require_once('footer.php');
echo <<<_END
		</div>
	</body>
</html>
_END;
//close the database connection
$conn->close();
?>
