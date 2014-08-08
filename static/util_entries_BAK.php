<?php
	$mode=$_POST["mode"];
	$new_html=addslashes($_POST["html"]);
	$botuser="botadmin";
	$botpass="a2790121aecb468122a0e2e0f2240e68";
	if(isset($_POST["entrycount"])) $block_count=(int)$_POST["entrycount"];
	
	$tbl_name="members";

	mysql_connect("localhost","","") or die("Couldn't connect to database");
	mysql_select_db("test") or die("Couldn't select database");

	switch($mode){
		case "get":
			//FETCH ALL BLOCKS FROM DATABASE
			echo mysql_fetch_array(mysql_query("SELECT entryblocks FROM $tbl_name WHERE username='$botuser' AND password='$botpass'"))[0];
			break;
		case "add":
			//INSERT BLOCK '$new_html' INTO DATABASE
			mysql_query("UPDATE $tbl_name SET entryblocks=CONCAT('$new_html',entryblocks) WHERE username='$botuser' AND password='$botpass'");
			break;
		case "set":
			mysql_query("UPDATE $tbl_name SET entryblocks='$new_html' WHERE username='$botuser' AND password='$botpass'");
			break;
		case "addcount":
			mysql_query("UPDATE $tbl_name SET entrycount=entrycount+$block_count WHERE username='$botuser' AND password='$botpass'");
			break;
		case "getcount":
			echo mysql_fetch_array(mysql_query("SELECT entrycount FROM $tbl_name WHERE username='$botuser' AND password='$botpass'"))[0];
			break;
		case "cleardb":
			mysql_query("UPDATE $tbl_name SET entrycount=0,entryblocks='',entrytitles='' WHERE username='$botuser' AND password='$botpass'");
			break;
		
	}
?>