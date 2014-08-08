<?php
	error_reporting(0);
	@ini_set('display_errors', 0);
	date_default_timezone_set("Europe/Lisbon");
	$url="";
	$output="";
	$sitearray=explode(";",$_GET["sitename"]);
	$botuser="botadmin";
	$botpass="a2790121aecb468122a0e2e0f2240e68";
	$db_name="scrapfeed_website";
	$tbl_name="members";
	$is_recent=(int)$_GET["recent"];
	
	mysql_connect("localhost","root","") or die("Couldn't connect to database");
	mysql_select_db($db_name) or die("Couldn't select database");

	//DATE:
	$date=date("d/m/Y")." ".date("h:i:sa"); 
	if($is_recent) $date="<span style='color:red'>NEW</span> ".$date; 
	$date="<span style='margin-top:13px;margin-left:5px;float:left;color:#A3A3A3;font-size:11px;'>".$date.", entry</span>";

	function get_title_from_fetched_entry($temp_output){
		//TODO LATER: PUT THIS CODE IN A NEW PHP FILE
		preg_match("/sitename='.+?'/",$temp_output,$sitename);
		switch($sitename[0]){
			case "sitename='reddit'": preg_match("/tabindex=\"1\".+?<\/a>/", $temp_output,$title_to_return); return addslashes($title_to_return[0]);
			case "sitename='9gag'": preg_match("/alt=\".+?>/",$temp_output,$title_to_return); return addslashes($title_to_return[0]);	
			case "sitename='youtube'": preg_match("/dir=\"ltr\" title=\".+?\"/", $temp_output,$title_to_return); return addslashes($title_to_return[0]);
			case "sitename='news.google.com'": preg_match("/titletext\">.+?<\/span>/", $temp_output,$title_to_return); return addslashes($title_to_return[0]);
		}
	}
	function get_title_array_from_db(){
		global $botuser,$botpass,$tbl_name;
		return explode("|||",mysql_fetch_array(mysql_query("SELECT entrytitles FROM $tbl_name WHERE username='$botuser' AND password='$botpass'"))[0]);
	}
	function add_title_entry($temp_output_title){
		global $tbl_name,$botuser,$botpass;	
		mysql_query("UPDATE $tbl_name SET entrytitles=CONCAT(entrytitles,'$temp_output_title|||') WHERE username='$botuser' AND password='$botpass'");
	}
	function is_entry_new($temp_output){
		$temp_output_title=get_title_from_fetched_entry($temp_output);
		if(in_array($temp_output_title, get_title_array_from_db())) return false;
		else{add_title_entry(addslashes($temp_output_title));return true;}
	}
	
	//SYNCRONIZE COUNTER WITH DATABASE
	$local_ctr=1;
	$db_ctr=intval(mysql_fetch_array(mysql_query("SELECT entrycount FROM $tbl_name WHERE username='$botuser' AND password='$botpass'"))[0]);
	if($db_ctr==0) $db_ctr=1;
	
	for($i=0;$i<count($sitearray);$i++){	
		$url=$sitearray[$i];
		$temp_output="";
		
		//MAKE THE ACTUAL REQUEST FOR $URL
		$ch=curl_init();curl_setopt($ch,CURLOPT_URL,$url);curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);$result=curl_exec($ch);curl_close($ch);
		switch($url){
			case "www.reddit.com": 	 include("pages/feeder_reddit.php");  break;
			case "9gag.com/fresh":   include("pages/feeder_9gag.php");    break;
			case "www.youtube.com":  include("pages/feeder_youtube.php"); break;
			case "news.google.com": include("pages/feeder_googlenews.php"); break;
		}$output=$output.$temp_output;
	}
	echo $output;
?>