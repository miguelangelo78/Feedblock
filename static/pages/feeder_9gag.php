<?php
	$result=str_replace("\n", "",$result);
	$length; // 10/10 ONLY FOR REDDIT
	if($is_recent) $length=10;
	else $length=10;
	$from=0;

	if(preg_match_all("/<article.+?<\/article>/",$result,$matches)){
		foreach($matches[0] as $ngag_entry){
			if($from>0){$from--;continue;}
			if($local_ctr-1==$length) break;
			
			//GENERAL REPLACES AND FIXES (ONLY FOR 9GAG):
			$ngag_entry=preg_replace("/<div class=\"badge-entry-sticky\">.+?<\/div>/", "", $ngag_entry);
			$ngag_entry=preg_replace("/<div class=\"post-afterbar.+?<\/article>/", "<br><br></article>", $ngag_entry);
			$ngag_entry=preg_replace("/href=\"\/gag\//", "/style='font-weight:normal;text-decoration:none;color:#303F9C' href=\"http://9gag.com/gag/", $ngag_entry);
			$ngag_entry=str_replace("img class=\"badge-item-img","img style='border-radius:3px;width:auto;height:300px' class=\"badge-item-img",$ngag_entry); // IMAGE SIZE
			$ngag_entry=str_replace("View Full Post","<br>View Full Post",$ngag_entry);
			$ngag_entry=str_replace("<span class=\"play","<br><span class=\"play",$ngag_entry);
			//TEMPLATE ENTRY:
			$template_begin="<span style='display:none' id='entry$db_ctr' sitename='9gag' url='9gag.com/fresh' class='entryclass'><div id='innerContent' dbindex='$db_ctr' style=\"border-radius:3px;box-shadow:0px 0px 10px #000000;
			background-color:white;margin-bottom:3px;width:750;font-family:arial;border-style:solid;border-color:#121630;border-width:1px\">
			<span style='float:left'>&nbspFrom: <img src='http://upload.wikimedia.org/wikipedia/commons/e/e9/9gag_logo.png'
			style='margin-top:10px' width=35 height=30></span>";
			$template_end="<div style='width:auto;height:27px;background-color:#DEE3E3;border-style:solid;border-width:1px;border-color:#DEE3E3;border-top-color:#CCCCCC;border-bottom-left-radius:5px;border-bottom-right-radius:5px'>
			<span style='float:right' id='controls'>
				<img class='hide_class' id='hide$db_ctr' style='cursor:pointer' onclick='hide_entry_click($(this))' src='../../img/eye.png'>
				<span class='likethis' onclick='makealike($(this))' style=''>I like this</span>
			</span>
			<span style='float:left' id='datereceived'>$date</span>
			<span class='enum' id='enum$db_ctr'># ".$ctr."&nbsp</span>
			</div></div></span>";

			$ngag_entry=$template_begin.$ngag_entry.$template_end;
			
			if(is_entry_new($ngag_entry)){$local_ctr++;$db_ctr++;$temp_output=$temp_output.$ngag_entry.";;;";}
		}
	}
?>