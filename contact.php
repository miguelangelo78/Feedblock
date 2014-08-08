<?php session_start(); if(!isset($_SESSION['myusername'])) header("location:static/login.php"); ?>
<html>
	<head><title>Contact | Feedblock</title></head>
	<script type="text/javascript" src="scripts/jquery-1.11.1.min.js"></script>
	<script>
		var username="<?php echo $_SESSION['myusername']; ?>";
		var password="<?php echo $_SESSION['mypassword']; ?>";
	</script>
	<link rel="stylesheet" type="text/css" href="styles/styles_footer.css">
	<link rel="shortcut icon" href="img/favicon.png">
	<body background="img/background.png">
		<script>
			function submit_opinion(){
				if($("#opinion_text").val()!=""){
					$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
							data:{username:username,password:password,mode:"submitopinion",member_opinion:$("#opinion_text").val()}
					});
					$("#thanks_you").text(" Thanks for submitting your opinion");
					setTimeout(function(){$("#thanks_you").text("");$("#opinion_text").val("");},3000);
				}else{
					$("#thanks_you").text(" Your submission is empty");
					setTimeout(function(){$("#thanks_you").text("");},3000);
				}
			}
		</script>
		<div id='contact_return' onclick='window.location.href="index.php"'>Return</div>
			<span id='welcome_contact'>Contact</span>
			<div id='contact_info'>
				<center>This website was created by Miguel Santos</center><br>
				If there is any change you'd like be made, please write it below:
				<textarea id='opinion_text' type='text' rows="5" cols="50"></textarea>
				<br><div id='contact_submit' onclick="submit_opinion()">Submit</div><span id='thanks_you'></span><br>
				Email: <b>santosmiguel25@gmail.com</b>
			</div>
	</body>
</html>