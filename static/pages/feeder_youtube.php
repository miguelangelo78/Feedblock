<?php
	$result=str_replace("\n", "",$result);
	$length; // 10/45 ONLY FOR YOUTUBE
	if($is_recent) $length=10;
	else $length=45;
	$from=0;
	if(preg_match_all("/<li class=\"channels.+?<\/ul>  <\/div>          <\/div>      <\/div>        <\/li>/",$result,$matches)){
		foreach($matches[0] as $youtube_entry){
			if($from>0){$from--;continue;}
			if($local_ctr-1==$length) break;
			//GENERAL REPLACES AND FIXES (ONLY FOR YOUTUBE):
			$youtube_entry=preg_replace("/href=\"/", "style='text-decoration:none;color:#303F9C' href=\"https://www.youtube.com",$youtube_entry);
			$youtube_entry=preg_replace("/src=\".+?\"/", "",$youtube_entry);
			$youtube_entry=preg_replace("/data-thumb/", "style='border-radius:3px;float:left;margin-top:50px;margin-left:-45px' src",$youtube_entry);
			$youtube_entry=preg_replace("/<button class=.+?<\/button>/","", $youtube_entry);
			$youtube_entry=preg_replace("/<li/","<li style='list-style-type: none;'", $youtube_entry);
			$youtube_entry=preg_replace("/video-time\">/","video-time\">Video time: ",$youtube_entry);
			$youtube_entry=preg_replace("/<div class=\"yt-lockup-meta/", "<span style='font-size:11px;'><div class=\"yt-lockup-meta", $youtube_entry);
			//TEMPLATE ENTRY:
			$template_begin="<span style='display:none' id='entry$db_ctr' sitename='youtube' url='www.youtube.com' class='entryclass'><div id='innerContent' dbindex='$db_ctr' style=\"border-radius:3px;box-shadow:0px 0px 10px #000000;
			background-color:white;margin-bottom:3px;width:750;font-family:arial;border-style:solid;border-color:#121630;border-width:1px\">
			<span style='float:left'>&nbspFrom: <img src='https://developers.google.com/youtube/images/YouTube-logo-full_color.png'
			 style='margin-top:10px' width=52 height=30></span>";
			$template_end="<br></span><div style='width:auto;height:27px;background-color:#DEE3E3;border-style:solid;border-width:1px;border-color:#DEE3E3;border-top-color:#CCCCCC;border-bottom-left-radius:5px;border-bottom-right-radius:5px'>
			<span style='float:right' id='controls'>
				<img class='hide_class' id='hide$db_ctr' style='cursor:pointer' onclick='hide_entry_click($(this))' src='../../img/eye.png'>
				<span class='likethis' onclick='makealike($(this))' style=''>I like this</span>
			</span>
			<span style='float:left' id='datereceived'>$date</span>
			<span class='enum' id='enum$db_ctr'># ".$ctr."&nbsp</span>
			</div></div></span>";

			$youtube_entry=$template_begin.$youtube_entry.$template_end;
			
			if(is_entry_new($youtube_entry)){$local_ctr++;$db_ctr++;$temp_output=$temp_output.$youtube_entry.";;;";}
		}
	}
?>