<?php
	$mode=$_POST["mode"];
	$new_html=addslashes($_POST["html"]);
	$botuser="botadmin";
	$botpass="a2790121aecb468122a0e2e0f2240e68";
	$db_name="scrapfeed_website";
	if(isset($_POST["entrycount"])) $block_count=(int)$_POST["entrycount"];
	
	$tbl_name="members";

	mysql_connect("localhost","root","") or die("Couldn't connect to database");
	mysql_select_db($db_name);
	
	switch($mode){
		case "add":
			//INSERT BLOCK '$new_html' INTO DATABASE AND INSERT ENTRYTYPE INTO DATABASE
			$entrytypename=addslashes($_POST["entrytype"]);
			echo $entrytypename;
			mysql_query("INSERT INTO block_html (html,entrytype) VALUES ('$new_html','$entrytypename')");
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
			mysql_query("UPDATE $tbl_name SET entrycount=0,entrytitles='' WHERE username='$botuser' AND password='$botpass'");
			mysql_query("TRUNCATE block_html");
			break;
		
	}
?>