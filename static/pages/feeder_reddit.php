<?php 
	$result=str_replace("\n","", $result);

	$length; // 9/34 ONLY FOR REDDIT
	if($is_recent) $length=9;
	else $length=34;
	$from=0; //OPTIONAL FOR REDDIT

	if(preg_match_all("/<div class=\" thing id.+?\" onclick.+?clearleft\"><\\/div>/",$result,$matches)){
		foreach($matches[0] as $reddit_entry){
			if($from>0){$from--;continue;}
			if($local_ctr-1==$length) break;

			//if(strpos($reddit_entry,"rounded nsfw-stamp")) continue;
			//if(strpos($reddit_entry,"display:none")) continue; //<- OPTIONAL (ENABLE OR DISABLE NEW REDDIT ENTRIES)

			//GENERAL REPLACES AND FIXES (ONLY FOR REDDIT):

			//LIKES AND DISLIKES:
			preg_match("/data-ups=\"[0-9]+\"/", $reddit_entry,$likes);
			preg_match("/[0-9]+/",$likes[0],$likes);
			preg_match("/data-downs=\"[0-9]+\"/", $reddit_entry,$dislikes);
			preg_match("/[0-9]+/",$dislikes[0],$dislikes);

			$reddit_entry=preg_replace("/<div class=\"score d.+?div>/", "<span style='font-size:11px'>Dislikes: $dislikes[0]</span> ",$reddit_entry);
			$reddit_entry=preg_replace("/<div class=\"score u.+?div>/", "",$reddit_entry);
			$reddit_entry=preg_replace("/<div class=\"score l.+?div>/", "<span style='font-size:11px'>Likes: <b><span style='color:green'>$likes[0]</span></b></span> ",$reddit_entry);
			$reddit_entry=preg_replace("/rank\">(?:[0-9]+)?</", "rank><", $reddit_entry);
			$reddit_entry=preg_replace("/<li class=\"share.+span>/","",$reddit_entry);
			$reddit_entry=str_replace("<li class=\"first\">", "<li style=\"list-style: none;\" class=\"first\">", $reddit_entry);
			$reddit_entry=str_replace("to&#32;<a href","to&#32;<b><a href",$reddit_entry);
			$reddit_entry=str_replace("<ul class=\"flat-list buttons\">", "</b><ul class=\"flat-list buttons\">", $reddit_entry);
			$reddit_entry=str_replace("/domain","http://www.reddit.com/domain",$reddit_entry);
			$reddit_entry=str_replace("<a class=\"title may", "<a style='text-decoration:none;color:#303F9C' class=\"title may", $reddit_entry);
			$reddit_entry=str_replace("<a href=\"", "<a style='text-decoration:none;color:#303F9C' href=\"", $reddit_entry);
			$reddit_entry=preg_replace("/class=\"linkflairlabel\" title=\".+?\">.+?<\/span>/", "class=\"linkflairlabel\" title=\"\"></span>", $reddit_entry);
			$reddit_entry=preg_replace("/height='[0-9]+' /", "height='80' ", $reddit_entry); //IMAGE HEIGHT
			$reddit_entry=preg_replace("/width='[0-9]+' /", "style='float:left;margin-left:50px;border-radius:3px;' width='100' ", $reddit_entry); //IMAGE WIDTH
			if(strpos($reddit_entry,"var cache =")) $reddit_entry=preg_replace("/var cache(?:.|\n)+? \";/", "",$reddit_entry);
			if(strpos($reddit_entry,"href=\"/r/")) $reddit_entry=str_replace("href=\"/r/","href=\"http://www.reddit.com/r/", $reddit_entry);
			$reddit_entry=str_replace("style='display:none'","",$reddit_entry);
			$reddit_entry=str_replace("<p class=\"tagline\">", "<span style='font-size:11px;'><p class=\"tagline\">", $reddit_entry); //FONT SIZE

			$reddit_entry=preg_replace("/<\/li><\/div>/","</span></li></div><div style='width:auto;height:27px;background-color:#DEE3E3;border-style:solid;border-width:1px;border-color:#DEE3E3;border-top-color:#CCCCCC;border-bottom-left-radius:5px;border-bottom-right-radius:5px'>
			<span style='float:right' id='controls'>
				<img class='hide_class' id='hide$db_ctr' style='cursor:pointer' onclick='hide_entry_click($(this))' src='../../img/eye.png'>
				<span class='likethis' onclick='makealike($(this))' style=''>I like this</span>
			</span>
			<span style='float:left' id='datereceived'>$date</span>
			<span class='enum' id='enum$db_ctr'># ".$ctr."&nbsp</span>
			</div>", $reddit_entry); // ENTRY CONTROLS

			//TEMPLATE ENTRY:
			$template_begin="<span style='display:none' id='entry$db_ctr' class='entryclass' url='www.reddit.com' sitename='reddit'><div id='innerContent' dbindex='$db_ctr' style=\"border-radius:3px;box-shadow:0px 0px 10px #000000;
			background-color:white;margin-bottom:3px;width:750;font-family:arial;border-style:solid;border-color:#121630;border-width:1px\">
			<span style='float:left'>&nbspFrom: <img src='http://blog.pixsy.net/wp-content/uploads/2012/11/
			reddit_logo_banner.jpg' style='margin-top:10px' width=52 height=20></span>";
			$template_end="</div></span>";

			$reddit_entry=$template_begin.$reddit_entry.$template_end;
			
			if(is_entry_new($reddit_entry)){$local_ctr++;$db_ctr++;$temp_output=$temp_output.$reddit_entry.";;;";}
		}
	}
?>