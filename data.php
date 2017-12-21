<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Explore Data | Charles River Water Quality Data</title>
				<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="css/style2.css">		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script	src="js/script.js" type="text/javascript" ></script>
	</head>
	<body>			
		<div id="wrapper">
			<header>
				<a href="http://www.crwa.org"><img id="logo" src="images/logo.jpg" alt="Charles River Watershed Association"></a>
				<h1>CHARLES RIVER WATERSHED ASSOCIATION</h1>
				<h2>"Saving the Charles River Since 1965"</h2>
			</header>
			<main>
				<h2>Visualize Water Quality Data</h2>
				<p>Use these tool to create maps and graphs of water quality data. Choose
				one of the search types below and select the sites and parameters you are interested in. Let's get started!</p>			
<!--Map form begins-->				
				<form class="dataform" action="map.php" method="post">
					<h3>Make a Map</h3>
					<p>Create a map showing results from a single sampling day.</p>
					<label for="date-map">Choose a sampling date</label>
					<?php
		require_once 'login.php'; //require login file
		$conn = new mysqli($hn, $un, $pw, $db); // connect to database
		if ($conn->connect_error) die($conn->connect_error);
//Query database for available monthly monitoring sampling dates		
		$query = "SELECT DISTINCT Date_Collected, DATE_FORMAT(Date_Collected,'%m/%d/%Y') as Date FROM Results ORDER BY Date_Collected DESC"; //query database for sampling dates
		$result = $conn->query($query);
		if (!$result) die($conn->error);
//Create an option for each date with the date as the value and displaying the formatted date		
		$rows = $result->num_rows;
		echo "<select name='date-map' id='date-map'>";
		for ($j = 0; $j <$rows; ++$j)
			{
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_ASSOC);
			echo "<option value='" . $row['Date_Collected'] . "'>" . $row['Date'] . "</option>";
			}
	echo "</select>";
	$result ->close();
		?>
					<label for="parameter-map">Choose a parameter to map</label><br>
					<select name="parameter-map" id="parameter-map">
						<option value="26">Water Temperature</option>
						<option value="12">E Coli</option>
					</select><br>
					<br>
					<br>
					<br>
					<input type="submit" value="Map!">
				</form>				
<!--End map form-->
<!--Graph form begins-->				
				<form class="dataform" action="graph.php" method="post">
					<h3>Generate a Graph</h3>
					<p>Create a graph showing results from a specific site for the year.</p>
					<label for="year-graph">Choose a year</label>
		<?php
//query database for years containing monthly monitoring data		
		$query = "SELECT DISTINCT EXTRACT(YEAR FROM Date_Collected) AS Year FROM Results ORDER BY Year Desc"; //query database for available years
		$result = $conn->query($query); //query database
		if (!$result) die($conn->error);
		$rows = $result->num_rows;
		echo "<select name='date-graph' id='year-graph'>"; //create dropdown
		for ($j = 0; $j <$rows; ++$j) // display options
			{
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_ASSOC);
			echo "<option value='" . $row['Year'] . "'>" . $row['Year'] . "</option>";
			}
	echo "</select>";
	$result ->close();
		?>
					<label for="parameter-graph">Choose a parameter to graph</label><br>
					<select name="parameter-graph" id="parameter-graph">
						<option value="26">Water Temperature</option>
						<option value="12">E. Coli</option>
					</select><br>
					<label for="site-graph">Choose a site</label>
		<?php
//Query the database for the names of the sites and towns that contain monthly monitoring data and order alphabetically by town. Exclude roving sites. 		
			$query = "SELECT Site_ID, Site_Name, Town FROM Monitoring_Sites WHERE Site_ID IN(
			SELECT DISTINCT Site_ID FROM RESULTS) AND Site_ID NOT LIKE 'ROV%' ORDER BY Town ASC"; // query database for site names and ids. (If using complete results table will need to add a where clause limiting it to vmm project)
			$result = $conn->query($query); //query database
			if (!$result) die($conn->error);
			$rows = $result->num_rows;
			echo "<select name='site-graph' id='site-graph'>"; // create dropdown 
			for ($j = 0; $j <$rows; ++$j) // display options
			{
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_ASSOC);
			echo "<option value='" . $row['Site_ID'] . "'>" . $row['Town'] . ' - ' . $row['Site_Name'] . "</option>";
			}
	echo "</select>";
	$result ->close();
		?>
					<input type="submit" value="Graph!">
				</form>
<!--Graph form ends-->
<!--Comparison form begins-->
				<form class="dataform" action="compare.php" method="post">
					<h3>Create a Comparison</h3>
					<p>Compare one site for a specific month year over year.</p>
					<label for="month-compare">Choose a month</label>
					<select name="month-compare" id="month-compare">
						<option value="1">January</option>
						<option value="2">February</option>
						<option value="3">March</option>
						<option value="4">April</option>
						<option value="5">May</option>
						<option value="6">June</option>
						<option value="7">July</option>
						<option value="8">August</option>
						<option value="9">September</option>
						<option value="10">October</option>
						<option value="11">November</option>
						<option value="12">December</option>
					</select>
					<label for="parameter-compare">Choose a parameter to graph</label><br>
					<select name="parameter-compare" id="parameter-compare">
						<option value="26">Water Temperature</option>
						<option value="12">E. Coli</option>
					</select><br>
					<label for="site-compare">Choose a site</label>
						<?php
			$query = "SELECT Site_ID, Site_Name, Town FROM Monitoring_Sites WHERE Site_ID IN(
			SELECT DISTINCT Site_ID FROM RESULTS) AND Site_ID NOT LIKE 'ROV%' ORDER BY Town ASC"; // query database for site names and ids. (If using complete results table will need to add a where clause limiting it to vmm project)
			$result = $conn->query($query); //query database
			if (!$result) die($conn->error);
			$rows = $result->num_rows;
			echo "<select name='site-compare' id='site-compare'>"; // create dropdown 
			for ($j = 0; $j <$rows; ++$j) // display options
			{
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_ASSOC);
			echo "<option value='" . $row['Site_ID'] . "'>" . $row['Town'] . ' - ' . $row['Site_Name'] . "</option>";
			}
	echo "</select>";
	$result ->close();
		?>
					<input type="submit" value="Create!">
				</form>
<!--comparison form ends-->
<!--download data form begins-->
				<form target="_blank" class="dataform" action="download.php" method="post">
					<h3>Download data</h3>
					<label for="parameter_download">Choose a parameter to download</label><br>
					<select name="parameter_download" id="parameter_download">
						<option value="26">Water Temperature</option>
						<option value="12">E. Coli</option>
					</select><br>
					<input type="submit" value="Download!">
				</form>
<!--download data form ends-->
			</main>
<!--Require php file that contains footer-->	
				<?php
			require_once('footer.php');
			?>
		</div>
		</body>
<?php 
	$conn->close();
?>
</html>