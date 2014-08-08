<?php session_start(); if(!isset($_SESSION['myusername'])) header("location:static/login.php"); ?>
<html>
	<head><title>About | Scrapfeed</title></head>
	<script type="text/javascript" src="scripts/jquery-1.11.1.min.js"></script>
	<link rel="stylesheet" type="text/css" href="styles/styles_footer.css">
	<link rel="shortcut icon" href="img/favicon.png">
	<body background="img/background.png">
		<div id='about_return' onclick='window.location.href="index.php"'>Return</div>
			<span id='welcome_about'>About</span>
			<div id='about_info'>
				<b>The main goal</b> of this place is to provide you a live feed of your favourite websites without opening multiple tabs and refreshing the page for new updates. For example, you receive news and fun jokes LIVE without going to multiple places.<br><br>
				It is dedicated to <b>web scraping</b> which presents information live to you of any highly active website you like.<br><br>
				<b>What is web scraping?</b><br>Web scraping is the act of colecting selective information from other websites for any number of purposes. You may think this is plagiarism. In fact, it is not, because all that's done is the collection of information and then redirect the user to the ORIGINAL content. This is positive for the websites that are being collected. It increases user activity and every new post gets a chance of being seen and upvoted, which will increase overall activity on every website.
			</div>
	</body>
</html>