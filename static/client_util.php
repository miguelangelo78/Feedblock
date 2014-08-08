<?php
	session_start();
	//error_reporting(0);
	$output="";
	$db_name="scrapfeed_website";
	$tbl_name="members";
	$botuser="botadmin";
	$botpass="a2790121aecb468122a0e2e0f2240e68";
	if(isset($_GET["username"])) $username=$_GET["username"];
	if(isset($_GET["password"])) $password=$_GET["password"];
	if(isset($_GET["mode"])) $mode=$_GET["mode"];
	if(isset($_GET["sitename"])) $sitearray=explode(";",$_GET["sitename"]);
	if(isset($_GET["maxentries"])) $max_entries=intval($_GET["maxentries"]);
				
	mysql_connect("localhost","root","") or die("Couldn't connect to database");
	mysql_select_db($db_name);
	
	function filter_entries($html){
		global $tbl_name,$username,$password;
		//USER LIKES:
		$user_likes=explode(";",mysql_fetch_array(mysql_query("SELECT entriesliked FROM $tbl_name WHERE username='$username' AND password='$password'"))[0]);
		for($i=0;$i<count($user_likes)-1;$i++)
			if(strpos($html, $user_likes[$i])){
				$html=preg_replace("/>I like this</",">You liked this<", $html);
				$html=preg_replace("/class='likethis'/","class='likedthis'", $html);
				break;
			}
		//ENTRIES SEEN:
		$user_entriesseen=explode(";",mysql_fetch_array(mysql_query("SELECT entriesseen FROM $tbl_name WHERE username='$username' AND password='$password'"))[0]);
		for($i=0;$i<count($user_entriesseen)-1;$i++)		
			if(strpos($html, $user_entriesseen[$i]))//CHANGE OPACITY:
				$html=preg_replace("/style=\"border/","style=\"opacity:0.4;border", $html);
		return $html;
	}
	function get_db_entrycount(){
		global $tbl_name,$botuser,$botpass;
		return (int)(mysql_fetch_array(mysql_query("SELECT entrycount FROM $tbl_name WHERE username='$botuser' AND password='$botpass'"))[0]);	
	}
	function get_user_entrycount(){
		global $tbl_name,$username,$password;
		return (int)(mysql_fetch_array(mysql_query("SELECT entrycount FROM $tbl_name WHERE username='$username' AND password='$password'"))[0]);
	}
	function extract_number($str){
		preg_match_all('!\d+!', $str, $matches);
		return (int)$matches[0];
	}
	function sync_user_count($db_entrycount){
		global $tbl_name,$username,$password;
		mysql_query("UPDATE $tbl_name SET entrycount=$db_entrycount WHERE username='$username' AND password='$password'");			
	}
	function sync_user_seen(){
		global $tbl_name,$username,$password;
		if(isset($_GET["entries_seen"])){
			$entriesseen=$_GET["entries_seen"];
			$entriesseen_db=explode(";",mysql_fetch_array(mysql_query("SELECT entriesseen FROM $tbl_name WHERE username='$username' AND password='$password'"))[0]);
			
			$last_entry_seen=intval(mysql_fetch_array(mysql_query("SELECT entrylastseen FROM $tbl_name WHERE username='$username' AND password='$password'"))[0]);
			for($i=0;$i<count($entriesseen);$i++){
				//IF DATABASE DOESN'T CONTAIN $entriesseen[$i] THEN APPEND
				if(!in_array($entriesseen[$i],$entriesseen_db)){
					mysql_query("UPDATE $tbl_name SET entriesseen=CONCAT(entriesseen,'$entriesseen[$i];') WHERE username='$username' AND password='$password'");
					//IF $entriesseen[$i] IS SMALLER THAN $last_entry_seen_number THEN SET entrylastseen=$entriesseen[$i]
					preg_match("/[0-9]+/",$entriesseen[$i],$entriesseen_number);
					if($last_entry_seen<=0 || $entriesseen_number[0]<$last_entry_seen) 
						mysql_query("UPDATE $tbl_name SET entrylastseen=$entriesseen_number[0] WHERE username='$username' AND password='$password'");
				}
			}
		}
	}
	function get_sitearray_arranged(){
		global $sitearray;
		$sitearray_arranged="";;
		for($i=0;$i<count($sitearray);$i++){
			$sitearray_arranged.="entrytype=\"url='".$sitearray[$i]."'\"";
			if($i<count($sitearray)-1) $sitearray_arranged.=" OR ";
		}
		return $sitearray_arranged;
	}
	function fetch_db_blocks($from,$length,$mode){
		global $tbl_name,$botuser,$botpass,$sitearray;
		//FETCH ALL BLOCKS FROM DATABASE
		$output=""; $result;
		switch($mode){
			case "sync": 
				$result=mysql_query("SELECT html FROM block_html ORDER BY id DESC LIMIT $from,$length");
				break;
			case "first":
				$sitearray_arranged=get_sitearray_arranged();
				$result=mysql_query("SELECT html FROM block_html WHERE $sitearray_arranged ORDER BY id DESC LIMIT $from,$length");
				break;
			case "single":
				$result=mysql_query("SELECT html FROM block_html ORDER BY id LIMIT $from,1");
				break;
			case "interval":
				//TODO: USE 'websites' FIELD TO GET THE INTERVAL
				$sitearray_arranged=get_sitearray_arranged();
				if((int)$_GET["page"]>1)$from+=get_db_entrycount()-get_user_entrycount();
				$result=mysql_query("SELECT html FROM block_html WHERE $sitearray_arranged ORDER BY id DESC LIMIT $from,$length");
				break;
		}
		while($row=mysql_fetch_array($result)) $output=$output.$row[0];
		return $output;	
	}
	function is_user_using_website($entry_html){
		global $sitearray,$tbl_name,$username,$password;
		$itsthere=0;
		preg_match("/sitename='(.+?)'/",$entry_html,$matches);
		$entry_html_type="FLAG_EMPTY";
		if(isset($matches[1])) $entry_html_type=$matches[1];
		
		
		//FILTRO POR SITES ESCOLHIDOS
		for($i=0;$i<count($sitearray);$i++) if(strpos($sitearray[$i],$entry_html_type)!==false){$itsthere=1; break;}
		//FILTRO POR SITES ESCONDIDOS
		$user_hidden_entries=explode(";;;",mysql_fetch_array(mysql_query("SELECT entrieshidden FROM $tbl_name WHERE username='$username' AND password='$password'"))[0]);
		for($i=0;$i<count($user_hidden_entries)-1;$i++)
			if(strpos($entry_html, $user_hidden_entries[$i])!==false){$itsthere=2; break;}
		return $itsthere;	
	}

	switch($mode){
		case "sync": 
			//CHECK IF USER IS SYNCED WITH SERVER:
			$db_entrycount=get_db_entrycount();
			$user_entrycount=get_user_entrycount();
			//IF YES DON'T DO ANYTHING AND ADD FLAG TO OUTPUT
			if($user_entrycount==$db_entrycount) $output=$output."FLAG_IS_SYNCED;;;";
			else{
				//ELSE FETCH THE NEW BLOCKS
				$db_difference=$db_entrycount-$user_entrycount;
				$db_entries=explode(";;;",fetch_db_blocks(0,$db_difference,"sync"));
				for($i=0;$i<count($db_entries);$i++)$db_entries[$i]=preg_replace("/entry".($i+1)."/","entry_".($i+1),$db_entries[$i]);
				$entries_added=0;
				if($db_difference>=$max_entries) $db_difference=$max_entries; // CLIP THE DIFERENCE
				for($i=0;$i<$db_difference;$i++){
					if($i>count($db_entries)) break;
					if(isset($db_entries[$i]) && is_user_using_website($db_entries[$i])==1){
						$entries_added++;
						$output=$output.preg_replace("/entry_[0-9]+?/", "entry_".$entries_added, $db_entries[$i]).";;;";
					}
				}
				sync_user_count($db_entrycount);
			}
			sync_user_seen();
			break;
		case "first":
			$db_firstentries=explode(";;;",fetch_db_blocks(0,$max_entries,"first"));
			$entries_added=0;
			for($i=0;$entries_added<$max_entries;$i++){
				if($i>count($db_firstentries)) break;
				if(isset($db_firstentries[$i])){
					switch(is_user_using_website($db_firstentries[$i])){
						case 1:
							$output=$output.filter_entries(preg_replace("/entry".($i+1)."/","entry_".($i+1),$db_firstentries[$i])).";;;";
							$entries_added++;
							break;
						case 2:
							//TODO: HIDE ENTRY PARCIALLY INSTEAD OF NOT SHOWING IT
							//TODO: FETCH DB_CTR
							if(preg_match("/dbindex='([0-9]+)'/",$db_firstentries[$i],$match)){
								$hidden_block="<span style='display:none' id='entry$i' class='entryclass'>
								<div id='innerContent' dbindex='".$match[1]."' style=\"border-radius:3px;box-shadow:0px 0px 10px rgb(0, 0, 0);
								background-color: rgba(0, 0, 0, 0.5);margin-bottom:3px;width:750px;font-family:arial;border-width:1px\">
								<span class='showbutton'>Show</span>
								<script>
								$('#entry$i > #innerContent').css({'background-color':'rgba(0,0,0,0.5)'});
								$('#entry$i').css({'cursor':'pointer'});					
								$(entry$i).click(function(){
									restore_block($(this));
								});
								</script>
								</div>";
								$output=$output.filter_entries($hidden_block).";;;";
								$entries_added++;
							}
							break;
					}
				}
			}
			sync_user_count((int)(mysql_fetch_array(mysql_query("SELECT entrycount FROM $tbl_name WHERE username='$botuser' AND password='$botpass'"))[0]));
			break;
		case "interval_fetch":
			$page=(int)($_GET["page"]);
			$db_entries=explode(";;;",fetch_db_blocks($page*$max_entries-$max_entries,$max_entries,"interval"));
			for($i=0;$i<count($db_entries);$i++){
				switch(is_user_using_website($db_entries[$i])){
					case 1:
						$output=$output.filter_entries(preg_replace("/entry".($i+1)."/","entry_".($i+1),$db_entries[$i])).";;;";
						break;
					case 2:
						if(preg_match("/dbindex='([0-9]+)'/",$db_entries[$i],$match)){
							$hidden_block="<span style='display:none' id='entry$i' class='entryclass'>
								<div id='innerContent' dbindex='".$match[1]."' style=\"border-radius:3px;box-shadow:0px 0px 10px rgb(0, 0, 0);
								background-color: rgba(0, 0, 0, 0.5);margin-bottom:3px;width:750px;font-family:arial;border-width:1px\">
								<span class='showbutton'>Show</span>
								<script>
								$('#entry$i > #innerContent').css({'background-color':'rgba(0,0,0,0.5)'});
								$('#entry$i').css({'cursor':'pointer'});					
								$(entry$i).click(function(){
									restore_block($(this));
								});
								</script>
								</div>";
							$output=$output.filter_entries($hidden_block).";;;";
						}
						break;
				}
			}
			if($page==1)sync_user_count((int)(mysql_fetch_array(mysql_query("SELECT entrycount FROM $tbl_name WHERE username='$botuser' AND password='$botpass'"))[0]));
			sync_user_seen();
			break;
		case "single_fetch":
			$entrynum_tofetch=intval($_GET["entrytofetch"]);
			$output=$output.filter_entries(explode(";;;",fetch_db_blocks($entrynum_tofetch,0,"single"))[0]);
			//TODO: UPDATE DATABASE:
			$remove_entryhide="hide".$_GET["entrytofetch"].";;;";
			mysql_query("UPDATE members SET entrieshidden=REPLACE(entrieshidden,'$remove_entryhide','') WHERE username='$username' AND password='$password'");
			break;
		case "deletesite": 
			$sitetodelete=$_GET["sitetodelete"];
			$current_user_sites=mysql_fetch_array(mysql_query("SELECT websites FROM $tbl_name WHERE username='$username' AND password='$password'"))[0];
			$current_user_sites=str_replace("$sitetodelete","" ,$current_user_sites);
			mysql_query("UPDATE members SET websites='$current_user_sites' WHERE username='$username' AND password='$password'");
			$_SESSION["websites"]=$current_user_sites;
			break;
		case "addsite":
			$sitetoadd=$_GET["sitetoadd"];
			mysql_query("UPDATE members SET websites=CONCAT(websites,';$sitetoadd') WHERE username='$username' AND password='$password'");
			$_SESSION["websites"].=";".$sitetoadd;
			break;
		case "getsites":
			echo mysql_fetch_array(mysql_query("SELECT websites FROM $tbl_name WHERE username='$username' AND password='$password'"))[0];
			break;
		case "alterentriesperpage":
			$newentriesperpage=$_GET["newentriesperpage"];
			mysql_query("UPDATE members SET entriesperpage=$newentriesperpage WHERE username='$username' AND password='$password'");
		 	break;
		 case "getentriesperpage":
		 	echo mysql_fetch_array(mysql_query("SELECT entriesperpage FROM $tbl_name WHERE username='$username' AND password='$password'"))[0];
		 	break;
		case "hide":
			$entriestohide=addslashes($_GET["hideentries"]);
			mysql_query("UPDATE members SET entrieshidden=CONCAT(entrieshidden,'$entriestohide;;;') WHERE username='$username' AND password='$password'");
			break;
		case "like":
			$entryliked=$_GET["entryliked"];
			if($_GET["undo"]==0) mysql_query("UPDATE members SET entriesliked=CONCAT(entriesliked,'$entryliked') WHERE username='$username' AND password='$password'");
			else mysql_query("UPDATE members SET entriesliked=REPLACE(entriesliked,'$entryliked','') WHERE username='$username' AND password='$password'");
			break;
		case "lastentry":
			echo intval(get_db_entrycount())-intval(mysql_fetch_array(mysql_query("SELECT entrylastseen FROM $tbl_name WHERE username='$username' AND password='$password'"))[0]);
			break;
		case "getautomaticentry_enableflag":
			echo mysql_fetch_array(mysql_query("SELECT entryautomatic FROM $tbl_name WHERE username='$username' AND password='$password'"))[0];
			break;
		case "setautomaticentry_enableflag":
			$set_automatic_entry=$_GET["setautomaticentry"];
			if($set_automatic_entry=="0") mysql_query("UPDATE $tbl_name SET entryautomatic=0 WHERE username='$username' AND password='$password'");
			else mysql_query("UPDATE $tbl_name SET entryautomatic=1 WHERE username='$username' AND password='$password'");
			break;
		case "getentriesseen_enableflag":
			echo mysql_fetch_array(mysql_query("SELECT entriesseen_enable FROM $tbl_name WHERE username='$username' AND password='$password'"))[0];
			break;
		case "setentriesseen_enableflag":
			$setentryseen_enable=$_GET["setentryseenenable"];
			if($setentryseen_enable=="0") mysql_query("UPDATE $tbl_name SET entriesseen_enable=0 WHERE username='$username' AND password='$password'");
			else mysql_query("UPDATE $tbl_name SET entriesseen_enable=1 WHERE username='$username' AND password='$password'");
			break;
		case "submitregistration":
			$reg_name=$_GET["name"];
			$reg_lastname=$_GET["lastname"];
			$reg_username=$_GET["username"];
			$reg_password=$_GET["password"];
			$reg_entrylastseen=get_db_entrycount();
			mysql_query("INSERT $tbl_name (name,lastname,username,password,websites,entrycount,entriesperpage,entrylastseen,entryautomatic,entriesseen_enable) 
						 VALUES			  ('$reg_name','$reg_lastname','$reg_username',MD5('$reg_password'),'news.google.com;9gag.com/fresh;www.reddit.com;www.youtube.com',0,25,$reg_entrylastseen,1,0)");
			break;
		case "submitopinion":
			$memberopinion=$_GET["member_opinion"];
			mysql_query("INSERT member_changes (member_opinion,username) VALUES ('$memberopinion','$username')");
			break;
	}
	echo $output;
?>