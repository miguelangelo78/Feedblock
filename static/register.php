<html>
	<head>
		<title>Register | Feedblock</title>
	</head>
	<script type="text/javascript" src="../scripts/jquery-1.11.1.min.js"></script>
	<script>
		function submit_registration(){
			var name=$("#name").val();;
			var lastname=$("#lastname").val();
			var username=$("#username").val();
			var password=$("#password").val();
			if(name.length>0 && lastname.length>0 && username.length>0 && password.length>0)
				$.ajax({
					type:'GET',async:true,timeout:30000,url:'client_util.php',
					data:{mode:"submitregistration",name:name,lastname:lastname,username:username,password:password},
					success:function(data){
						$("#success_label").text("Thank you for registering on our website. You're going be redirected to the login page in a few seconds ...");
						setTimeout(function(){
							window.location="login.php";
						},3000);
					}
				});
			else{
				$("#success_label").text("You must fill every field correctly");
				setTimeout(function(){$("#success_label").text("");},2000);
			}		
		}
	</script>
	<body>
		<link rel="stylesheet" type="text/css" href="../styles/styles_loginregister.css">
		<center>
			<span id='welcome_form'>
				<span id='welcometitle'>Feedblock</span><br>
				<span id='sloganwelcometitle'>Your favourite website live</span>
			</span>
		</center>

		<center>
			<div id='registration_form'>
				
				<b id='title_regpage'>Registration page</b><br>
				<br>Enter your details:<br>
				<table id='registration_table'>
					<tr>
						<td>Name:</td>
						<td><input type='textbox' id='name'></td>
					</tr>
					<tr>
						<td>Lastname:</td> 
						<td><input type='textbox' id='lastname'></td>
					</tr>
					<tr>
						<td>Username:</td>
						<td><input type='textbox' id='username'></td>
					</tr>
					<tr>
					<tr>
						<td>Password:</td><td><input type='password' id='password'></td>
					</tr>
				</table>
				<span style='margin-top:8px;display:block;width:60px;font-size:15px' id='registerbtn' onclick='submit_registration()'>Register</span>
				<span id='goback_btn' onclick="location.href='login.php'">Go back</span>
	
				<div id='success_label'></div>
			</div>
		</center>
	</body>
</html>