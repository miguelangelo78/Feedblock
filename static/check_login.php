<?php
	$db_name="scrapfeed_website";
	$tbl_name="members";

	mysql_connect("localhost","root","") or die("Couldn't connect to database");
	mysql_select_db($db_name) or die("Couldn't select database");

	$myusername=mysql_real_escape_string(stripslashes($_POST['username']));
	$mypassword=mysql_real_escape_string(stripslashes(md5($_POST['password'])));
	$result=mysql_query("SELECT * FROM $tbl_name WHERE username='$myusername' and password='$mypassword'");
	
	if(mysql_num_rows($result)==1){
		session_start();
		$_SESSION['myusername']=$myusername;
		$_SESSION['mypassword']=$mypassword;
		
		$results_array=array();
		while($row=mysql_fetch_array($result)){
			$results_array[]=$row['websites'];
			$results_array[]=$row['entryblocks'];
			$results_array[]=$row['entriesperpage'];
		}
		$_SESSION['websites']=$results_array[0];
		$_SESSION['entryblocks']=$results_array[1];
		$_SESSION['entriesperpage']=$results_array[2];
		$_SESSION["page"]=1;
		$_SESSION["botadmin_user"]=mysql_fetch_array(mysql_query("SELECT username FROM $tbl_name WHERE id=1"))[0];
		$_SESSION["botadmin_pass"]=mysql_fetch_array(mysql_query("SELECT password FROM $tbl_name WHERE id=1"))[0];
		$_SESSION["botadmin_websites"]=mysql_fetch_array(mysql_query("SELECT websites FROM $tbl_name WHERE id=1"))[0];
		header("location:../index.php");
	}else header("location:login.php?success=no");
	ob_end_flush();
?>