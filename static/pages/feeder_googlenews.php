<?php
	$result=str_replace("\n", "",$result);
	$length;
	if($is_recent) $length=10;
	else $length=10;
	$from=0;
	
	$j=0;
	if(preg_match_all("/<div class=\"blended-wrapper.+?esc-sep/",$result,$matches)){
		foreach($matches[0] as $gnews_entry){
			if($from>0){$from--;continue;}
			if($local_ctr-1==$length) break;
			$j++; if($j==1) continue; //IGNORE THE FIRST ENTRY

			//GENERAL REPLACES AND FIXES (ONLY FOR GOOGLE NEWS):
			$gnews_entry=preg_replace("/<div class=\"esc-ex.+?<\/td>/","", $gnews_entry);
			$gnews_entry=preg_replace("/<div class=\'esc-sep/","",$gnews_entry);
			$gnews_entry=preg_replace("/<(?:span|div) class=\'al-attribution-timestamp\'>.+?<\/(?:span|div)>/","",$gnews_entry);
			$gnews_entry=preg_replace("/<span class=\'dash-separator\'>.+?<\/span>/", "",$gnews_entry);
			$gnews_entry=preg_replace("/<label class=\"esc-thumbnail-image-source\">.+?<\/label>/", "",$gnews_entry);
			$gnews_entry=preg_replace("/attribution-source\'>/", "attribution-source' style='display:block;margin-top:-15px;font-size:15px'><b>Source:</b> ", $gnews_entry);
			$gnews_entry=preg_replace("/>See realtime coverage</", "><span style='margin-bottom:3px;margin-top:40px;display:block;margin-right:40px;margin-left:-90px;border-style:solid;border-width:1px;border-color:#6073A8;border-radius:5px;width:100px;height:40px;padding:5px 5px 5px 5px;background-color:#7089cf;color:white'>See realtime coverage</span><",$gnews_entry); //REALTIME COVERAGE
			$gnews_entry=preg_replace("/\" style=\"2\"/", "\" style=\"2;text-decoration:none\"", $gnews_entry);
			$gnews_entry=preg_replace("/snippet-wrapper\">/", "snippet-wrapper\" style='font-size:15px;text-align:left;'><b>Description:</b> ", $gnews_entry);
			$gnews_entry=preg_replace("/><span class=\"titletext/","style='font-weight:normal;font-size:15px;color:#303F9C;text-decoration:none'><span class=\"titletext", $gnews_entry); //NEWS TITLE
			$gnews_entry=preg_replace("/href=\"\//", "href=\"https://news.google.com/", $gnews_entry);
			$gnews_entry=preg_replace("/<div class=\"esc-thumbnail\".+?<\/div><\/div><\/div>/", "", $gnews_entry);
			$gnews_entry=preg_replace("/>[0-9]+? .+?ago/", ">", $gnews_entry);
			$gnews_entry=preg_replace("/<div class=\"al-attribution-source\">.+?<\/div>/", "",$gnews_entry);
			$gnews_entry=preg_replace("/>Written by</", "style='display:inline'><b style='font-size:15px;font-family:arial'>Written by </b><",$gnews_entry);
			//TEMPLATE ENTRY:
			$template_begin="<span style='display:none' id='entry$db_ctr' sitename='news.google.com' url='news.google.com' class='entryclass'><div id='innerContent' dbindex='$db_ctr' style=\"border-radius:3px;box-shadow:0px 0px 10px #000000;
			background-color:white;margin-bottom:3px;width:750;font-family:arial;border-style:solid;border-color:#121630;border-width:1px\">
			<span style='float:left'>&nbspFrom: <img src='http://upload.wikimedia.org/wikipedia/commons/2/23/Google-News_logo.png'
			style='margin-top:10px' width=70 height=15></span>";
			$template_end="<div style='width:auto;height:27px;background-color:#DEE3E3;border-style:solid;border-width:1px;border-color:#DEE3E3;border-top-color:#CCCCCC;border-bottom-left-radius:5px;border-bottom-right-radius:5px'>
			<span style='float:right' id='controls'>
				<img class='hide_class' id='hide$db_ctr' style='cursor:pointer' onclick='hide_entry_click($(this))' src='../../img/eye.png'>
				<span class='likethis' onclick='makealike($(this))' style=''>I like this</span>
			</span>
			<span style='float:left' id='datereceived'>$date</span>
			<span class='enum' id='enum$db_ctr'># ".$ctr."&nbsp</span>
			</div></div></span>";

			$gnews_entry=$template_begin.$gnews_entry.$template_end;
			
			if(is_entry_new($gnews_entry)){$local_ctr++;$db_ctr++;$temp_output=$temp_output.$gnews_entry.";;;";}		
		}
	}
?>