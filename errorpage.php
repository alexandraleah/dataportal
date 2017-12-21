<!DOCTYPE html>
<!--This page is displayed when there is not enough data to create the graph-->
<html lang="en">
	<head>
		<title>Your Graph | Charles River Water Quality Data</title>
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
			<h2>Data not available for these search parameters</h2>
			<main>
				<p>An error occured or there is not enough data available for your search. Please try another search. If errors persist please
				<a href="http://www.crwa.org/contact" target="_blank">contact us.</a></p>
				<p><a href="index.php" class="mybutton">Start a new search</a></p>
			</main>
			<?php require_once('footer.php');?>
		</div>
	</body>
</html>
