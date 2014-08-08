<html>
	<head>
		<title>Feedblock | Login</title>
		<script type="text/javascript" src="../scripts/jquery-1.11.1.min.js"></script>
	</head>
	<body>
		<link rel="stylesheet" type="text/css" href="../styles/styles_loginregister.css">
		<center>
			<span id='welcome_form'>
				<span id='welcometitle'>Welcome to Feedblock</span><br>
				<span id='sloganwelcometitle'>Your favourite website live</span>
			</span>
		</center>

		<table id='login_tbl' width="300" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
			<tr>
				<form name="form1" method="post" action="check_login.php">
					<td>
						<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
							<tr>
								<td colspan="3"><strong>Login page</strong></td>
							</tr>
							<tr>
								<td width="78">Username:</td>
								<td width="294"><input name="username" type="text" id="username"></td>
							</tr>
							<tr>
								<td>Password:</td>
								<td><input name="password" type="password" id="password"></td>
							</tr>
							<tr>
								<td>
									<input id='loginbtn' type="submit" name="Submit" value="Login">
									<span id='successmsg'>
										<?php 
										if(isset($_GET["success"]))
											echo "Wrong username or password.
											<script>
											setTimeout(function(){
												//$(\"#successmsg\").text('');
											},3000);
											</script>";
										?>
									</span>
								</td>
							</tr>
						</table>
					</td>
				</form>
			</tr>
		</table>
		<center>
		<span id='notregistered'>
			Not registered yet?
			<span id="registerbtn" onclick="location.href='register.php'">Register now</span>
		</span>
		</center>
	</body>
</html>