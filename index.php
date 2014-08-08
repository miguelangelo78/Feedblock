<?php session_start(); if(!isset($_SESSION['myusername'])) header("location:static/login.php"); ?>
<html>
	<header>
		<title>Home | Feedblock</title>
		<script type="text/javascript" src="scripts/jquery-1.11.1.min.js"></script>
		<link rel="stylesheet" type="text/css" href="styles/styles.css">
		<link rel="shortcut icon" href="img/favicon.png">
		<script>
			var username="<?php echo $_SESSION['myusername']; ?>";
			var password="<?php echo $_SESSION['mypassword']; ?>";
			var original_title="Feedblock";
			var first_entry_to_show=0;
			var page1=true;
			var is_loading=false;
			var is_syncing=true;
			var nextpage_appended=false;
			var entriesseen=new Array();
			var entries_to_insert_buffer=new Array();
			var entries_buffered=new Array();

			var enable_entriesseen=false;
			var automatic_insertion=false;

			var max_entries_per_page=25;
			$.ajax({type:'GET',async:false,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"getentriesperpage"},
					success:function(data){max_entries_per_page=parseInt(data);}
			});
			var interval=max_entries_per_page;
			
			var user_websites="";
			$.ajax({type:'GET',async:false,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"getsites"},
					success:function(data){user_websites=data;}
			});
		
			$.ajax({type:'GET',async:false,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"getentriesseen_enableflag"},
					success:function(data){if(data==1) enable_entriesseen=true; else enable_entriesseen=false;}
			});
			$.ajax({type:'GET',async:false,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"getautomaticentry_enableflag"},
					success:function(data){if(data==1) automatic_insertion=true; else automatic_insertion=false;}
			});

			</script>
	</header>
	<body background="img/background.png">
		<div id='header'>
			<span id='hometitle' onclick='goto_home()'>Feedblock</span>
			<a href='javascript:history.go(0)'><img id='logoimg' src="img/logo_final.png"></a>
			<span id='slogan'>Your favourite website live</span>
			<span id='header_controls'>
				<div title='Logout' id='logout_btn' onclick='window.location.href="static/logout.php"'></div>
				<div title="Configuration" id='config_btn' class='config_btn_header'>
					<!-- DROPDOWN CONFIGURATION MENU -->
					<ul id='config_submenu'>
						<li class="config_submenu_choice">
							<div id='website_config_txt'><b><center>Configuration</center></b></div>
							<b id='websites_chosen_txt'><center>Websites:</center></b>
							<script>
								$("<ul>").insertAfter("#websites_chosen_txt");
								var sitesarray=user_websites.split(";");
								for(var i=sitesarray.length-1;i>=0;i--)
									if(sitesarray[i]!="") $("<li class='config_submenu_choice1'><div onclick='remove_chosenwebsites($(this))' id='removechosenwebsite_"+sitesarray[i]+"' class='remove_chosenwebsite'></div> <div onclick=\"window.open('http://"+sitesarray[i]+"','_blank')\" style='display:inline-block;margin-top:-21px'>"+sitesarray[i]+"</div></li>").insertAfter("#websites_chosen_txt");
								$("</ul>").insertAfter("#websites_chosen_txt");
							</script>
							<?php
								echo "<center><select id='select_add_chosenwebsite'>";
								$bot_websites=explode(";",$_SESSION["botadmin_websites"]);
								for($i=0;$i<count($bot_websites);$i++)
									if($bot_websites[$i]!="") echo "<option value='$bot_websites[$i]'>$bot_websites[$i]</option>";
								echo "</select><div onclick='add_chosenwebsites()' id='add_chosenwebsite'></div></center>";
							?>
						</li>
						<li class="config_submenu_choice">
							<center><b id='entries_pp_txt'>Entries per page: </b>
							<script>$("<span>"+max_entries_per_page+"</span>").insertAfter("#entries_pp_txt");</script>
							</center><input onfocus="if (this.value == 'Enter value (Max: 200)') {this.style='color:black';this.value = '';}"
									   onblur="if (this.value == '') {this.style='color:#C4C4C4';this.value = 'Enter value (Max: 200)';}"
							 		   id='newentriesperpage' type='text' value='Enter value (Max: 200)'>
							<div id='alterentriespp_btn' onclick='alterEntriesPerPage()'>Ok</div>
						</li>
						<li class="config_submenu_choice">
							<center><b>Automatic insertion of entries</b><br>
							<div onclick='toggle_automatic_insertion()' id='on_automaticentries'></div>
							<div id='automatic_entry_enabled_txt'>
								<script>
								if(automatic_insertion) $("#automatic_entry_enabled_txt").text("On");
								else{
									$("#automatic_entry_enabled_txt").text("Off");
									$("#on_automaticentries").attr("id","off_automaticentries")
								}
								</script>
							</div>
							</center>
						</li>
						<li class="config_submenu_choice">
							<center><b>Entries transparency</b><br>
							<div onclick='toggle_entriesseen()' id='on_entriesseen'></div>
							<div id='entries_seen_enabled_txt'>
								<script>
								if(enable_entriesseen) $("#entries_seen_enabled_txt").text("On");
								else{
									$("#entries_seen_enabled_txt").text("Off");
									$("#on_entriesseen").attr("id","off_entriesseen");
								}
								</script>
							</div>
							</center></li>
						<li id='alertChanges'></li>
					</ul>
				</div>
				<div title='Profile' id='profile_btn' onclick='window.location.href="profile.php"'></div>
				<br><span id='welcome_back'>Welcome back <b><?php echo $_SESSION['myusername'] ?></b></span>
			</span>
			<span id='lastentryviewed' onclick='resume_lastseen()'><!--Resume last post--></span>
			<span id='gohome' onclick='goto_home()'>Go home</span>
			<div id='live_alert_container'>
				<?php
				//LIVE UPDATE ALERT
				$websites_exploded=explode(";",$_SESSION["websites"]);
				for($i=0;$i<count($websites_exploded);$i++){
					$websites_exploded[$i]=preg_replace("/www\./", "",$websites_exploded[$i]);
					if(preg_match("/.+?\./",$websites_exploded[$i],$website_name)){
						$website_name[0]=ucwords(preg_replace("/\./", "",$website_name[0]));
						echo "<span onclick='show_updates($(this))' style='display:block;position:fixed;' class='live_alert_class' id='live_alert_$website_name[0]'>$website_name[0]: <span class='live_update_number'>0 updates</span></span><br><br>";
					}
				}
			?>
			</div>
		</div>
		<div id='header_fixed'>
			<a href='javascript:history.go(0)'><img id='logoimg_fixed' src="img/logo_fixed_final.png"></a>
			<span id='hometitle_fixed' onclick='goto_home()'>Feedblock</span>
			<span id='paging_fixed'>Home</span>
			<span id='header_controls_fixed'>
				<div title='Logout' id='logout_btn' onclick='window.location.href="static/logout.php"'></div>
				<div title="Configuration" id='config_btn' class='config_btn_fixed'>
					
				</div>
				<div title='Profile' id='profile_btn' onclick='window.location.href="profile.php"'></div>
			</span>
		</div>
		<div title="Pause automatic synchronization" id='togglesync' onclick='toggle_synchronization()'></div>
		<center><br><span id='entrycontainer'></span></center>
		<span id='footer'>
			<a href="about.php" id='about_url'>About</a>
			<a href="contact.php" id='contact_url'>Contact</a>
		</span>


		<script>
		/*var title_script=document.createElement("script");
		title_script.type="text/javascript";
		title_script.src="scripts/get_titles.js";
		document.body.appendChild(title_script);*/

		function IsNumeric(input){
		    return (input - 0) == input && (''+input).replace(/^\s+|\s+$/g, "").length > 0;
		}
		function capitals(str){
			return str.charAt(0).toUpperCase() + str.slice(1);
		}
		function extract_numbers(str){
			return (str.replace( /^\D+/g, '')).replace("\"","");
		}
		function add_chosenwebsites(){
			var websiteToAdd=$("#select_add_chosenwebsite").val();
			$("#alertChanges").show();
			$("#alertChanges").html("Adding <br>"+websiteToAdd+" ...");
			if(user_websites.indexOf(websiteToAdd)==-1){
				$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
						data:{username:username,password:password,sitename:user_websites,mode:"addsite",sitetoadd:websiteToAdd},
						success:function(){location.reload();}
				});
			}else{
				$("#alertChanges").html("Already using this website");
				setTimeout(function(){$("#alertChanges").html(""); $("#alertChanges").hide();},2000);
			}
		}
		function remove_chosenwebsites(elem){
			$("#alertChanges").show();
			$("#alertChanges").html("Removing <br>"+elem.attr('id').match("_(.+)")[1]+" ...");
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"deletesite",sitetodelete:elem.attr('id').match("_(.+)")[1]},
					success:function(){location.reload();}
			});
		}
		function alterEntriesPerPage(){
			var user_entered_entriespp=$("#newentriesperpage").val();
			$("#alertChanges").show();
			if(!IsNumeric(user_entered_entriespp) || user_entered_entriespp=="Enter value (Max: 200)"){
				$("#alertChanges").html("Invalid input");
				setTimeout(function(){$("#alertChanges").html(""); $("#alertChanges").hide(); $("#newentriesperpage").css({"color":"#C4C4C4"}); $("#newentriesperpage").val("Enter value (Max: 200)");return;},2000);
			}else{
				if(!user_entered_entriespp.length) return; 
				if(parseInt(user_entered_entriespp)>200){
					$("#alertChanges").html("That number is too high");
					setTimeout(function(){$("#alertChanges").html("");$("#alertChanges").hide();},2000);
				}else{
					$("#alertChanges").html("Changing interval ...");
					$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
							data:{username:username,password:password,sitename:user_websites,mode:"alterentriesperpage",newentriesperpage:user_entered_entriespp},
							success:function(){location.reload();}
					});
				}
			}
		}
		function toggle_automatic_insertion(){
			var bool_to_send="0";
			if(automatic_insertion) bool_to_send="0"; else bool_to_send="1";
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"setautomaticentry_enableflag",setautomaticentry:bool_to_send},
					success:function(){location.reload();}
			});
		}
		function toggle_entriesseen(){
			var bool_to_send="0";
			if(enable_entriesseen) bool_to_send="0"; else bool_to_send="1";
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"setentriesseen_enableflag",setentryseenenable:bool_to_send},
					success:function(){location.reload();}
			});
		}
		function makealike(elem){
			//TODO: CHANGE LIKEBUTTON LOCALLY
			var undolike=0;
			if($(elem).html()!="You liked this"){
				$(elem).html("You liked this");
				$(elem).attr("class","likedthis");
			}
			else{$(elem).html("I like this");undolike=1;$(elem).attr("class","likethis");}

			var elemid=/id=\"hide([0-9]+?)\"/g.exec(elem.parent().html())[1];
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"like",page:(interval/max_entries_per_page),maxentries:max_entries_per_page,entryliked:"entry"+elemid+";",undo:undolike}
			});		
		}
		function toggle_synchronization(){
			var toggle_sync_btn_val=$("#togglesync").val();
			if(is_syncing){
				$("#togglesync").css({"background-image":"url('../img/resume_sync.ico')"});
				$("#togglesync").attr("title","Resume automatic synchronization");
				is_syncing=false;
			}else{
				$("#togglesync").css({"background-image":"url('../img/pause_sync.ico')"});
				$("#togglesync").attr("title","Pause automatic synchronization");
				is_syncing=true;
				synchronize_clientserver(false);
			}
		}
		function getEntryCount(){
			for(var i=0;true;i++) if($("#entry"+(i+1)).attr("id")==null) return i;
		}
		function update_title(mode,addamount,page){
			switch(mode){
				case "add": 
					if(/[0-9]/.test(document.title))
					{
						var quantity_to_add=parseInt(document.title.match(/[0-9]+/)[0])+addamount;
						if(quantity_to_add==0) document.title=document.title.replace(/\([0-9]+?\)/g,"");
						else document.title=document.title.replace(/[0-9]+/g,quantity_to_add);
					}
					else document.title+=" ("+(addamount)+")";
					break;
				case "clear": document.title=original_title; break;
				case "setpagination":
					document.title=document.title.replace(/| Page [0-9]+/,"");
					if(page==1)	document.title="Home | "+document.title; else document.title+=" | Page "+page;
					break;
			}
			//if(document.title.match(/[0-9]+/)[0]=="0") document.title=document.title.replace(/\([0-9]+?\)/g,"");
		}
		function hide_entries(entryid,speed,mode,remove){
			if(mode=="slide") $("#entry"+entryid).slideUp(speed,function(){$("#entry"+entryid).hide(); if(remove) $("#entry"+entryid).remove();});
			else if(mode=="clear"){ $("#entry"+entryid).remove(); }
			else if(mode=='slidenshow'){
				$("#entry"+entryid).animate({height:40},500,function(){
					$("#entry"+entryid+" > #innerContent").html("<span class='showbutton'>Show</span>");
					$("#entry"+entryid+" > #innerContent").css({"background-color":"rgba(0,0,0,0.5)"});
					$("#entry"+entryid).css({"cursor":"pointer"});
					$("#entry"+entryid).click(function(){
						restore_block($(this));
					});
					$("#entry"+entryid).css({"display":"inline"});
				});
			}
			else{ $("#entry"+entryid).hide(); if(remove) $("#entry"+entryid).remove();}	
		}
		function remove_new_label(html,mode){
			if(mode=="single") html=html.replace(/<span style='color:red'>NEW<\/span>/g,"");
			else for(var i=0;i<html.length;i++) html[i]=html[i].replace(/<span style='color:red'>NEW<\/span>/g,"");return html;
		}
		function show_updates(website_toupdate){
			var entrytype_tofade=website_toupdate.attr("id").match(/alert_(.+)/)[1];
			var entries_buffered_inserted=0;
			for(var i=entries_buffered.length-1;i>=0;i--){
				if(entries_buffered[i]=="") continue;
				var entry_buffered_type=capitals(entries_buffered[i].match(/sitename=\'(.+?)\'/)[1]);
				if(entry_buffered_type.indexOf(".")>-1) entry_buffered_type=entry_buffered_type.substring(0,entry_buffered_type.indexOf(".")); //NEWS FIX
				if(entry_buffered_type==entrytype_tofade){
					$(entries_buffered[i]).insertBefore("#entry1");
					entries_buffered[i]="";
					entries_buffered_inserted++;
					update_title("add",-1,0);
				}
			}
			if(entries_buffered_inserted>0){
				$('html, body').animate({ scrollTop: 0 }, 'fast');
				$("#live_alert_"+entrytype_tofade+" > .live_update_number").html("0 updates");
				update_ids(0,false);
			}
		}
		function update_live_alerts(){
			for(var i=0;i<entries_to_insert_buffer.length;i++){
				var entrytype=capitals(entries_to_insert_buffer[i].match(/sitename=\'(.+?)\'/)[1]);
				if(entrytype.indexOf(".")>-1) entrytype=entrytype.substring(0,entrytype.indexOf(".")); //NEWS FIX
				var current_number=parseInt($("#live_alert_"+entrytype+" > .live_update_number").html().match(/[0-9]+/))+1;
				$("#live_alert_"+entrytype+" > .live_update_number").html(current_number+" updates");
				entries_buffered.push(entries_to_insert_buffer[i]);	
			}
			entries_to_insert_buffer=[];
		}
		function update_ids(from,syncmode){
			for(var i=0;true;i++){
				var entryblock=$(".entryclass").get(i);
				if(entryblock==null) break;
				entryblock.id="entry"+(i+1+from);
				var enumeration=$("#"+entryblock.id+" .enum");
				enumeration.html("#"+(i+1+from)+"&nbsp");
				enumeration.id='enum'+(i+1+from);
				//INCREMENT LIVE UPDATES
				if(!syncmode) $("#"+entryblock.id).fadeIn();
				$("#"+entryblock.id).mouseenter(function(evt){
					if(enable_entriesseen && entriesseen.indexOf($(this).attr("id"))==-1) entriesseen.push("entry"+extract_numbers($(this).html().match(/dbindex=\"[0-9]+?\"/g)[0]));
					$("#"+$(this).attr("id")+" > #innerContent").stop().animate({opacity:1},100);
				});
				$("#"+entryblock.id).mouseleave(function(){
					if(entriesseen.indexOf($(this).attr("id"))>-1)
						$("#"+$(this).attr("id")+" > #innerContent").stop().animate({opacity:0.4},100);	
				});
			}
			if(syncmode)update_live_alerts();
			//CHECK FOR EXCEDED ENTRIES:
			var entrycount=getEntryCount();
			if(entrycount>max_entries_per_page) while(entrycount>max_entries_per_page){ hide_entries(entrycount,"","",true); entrycount--;}				
		}
		function update_page(going_home){
			if(interval>max_entries_per_page) $("#togglesync").hide(); else $("#togglesync").show();
			update_title("clear",0,0);
			update_title("setpagination",0,interval/max_entries_per_page);
			is_loading=true;
			$("#lastentryviewed").text("");
			$("#footer").hide();
			$("#nextpage").hide();
			$("#nextpage").val("Next page (Page "+((interval+max_entries_per_page)/max_entries_per_page)+")");
			if(page1){$("#paging_fixed").text("Home");
				//$("#lastentryviewed").text("Resume last post");
			}
			else {$("#paging_fixed").text("Page "+(interval/max_entries_per_page));$("#gohome").css({"display":"inline-table"});
			}
			$(".entryclass").fadeOut(); 
			setTimeout(function(){
				$(".entryclass").remove();
				$.ajax({
					type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"interval_fetch",page:(interval/max_entries_per_page),maxentries:max_entries_per_page,entries_seen:entriesseen},
					success:function(data){
						var page_next=remove_new_label(data.split(";;;"),"array");
						for(var i=0;i<page_next.length;i++) $("#entrycontainer").append(page_next[i]);//INSERT NEW ENTRY BLOCKS:
						update_ids(interval-max_entries_per_page,false);
						$('#nextpage').appendTo("#entrycontainer");
						$("#nextpage").wrap("<center></center>");
						if(page_next.length>=max_entries_per_page) $("#nextpage").show();
						if(page1){$("#prevpage").css({"display":"none"});$("#gohome").css({"display":"none"});}
						else if(!going_home){$("#gohome").css({"display":"inline-table"});$("#prevpage").css({"display":"inline-block"});}
						setTimeout(function(){is_loading=false;},3000);
						$("#footer").show();
					}});
			},200);
		}
		function resume_lastseen(){
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"lastentry",maxentries:max_entries_per_page},
					success:function(data){
						if(parseInt(data)>max_entries_per_page){
							interval=parseInt(data);
							page1=false;
							update_page(false);
						}
					}	
			});
		}
		function goto_home(){
			page1=true;
			interval=max_entries_per_page;
			$("#prevpage").css({"display":"none"});
			$("#prevpage").remove();
			$("#gohome").css({"display":"none"});
			update_page(true);
		}
		function goto_nextPage(){
			page1=false;
			if(interval==max_entries_per_page) $("<center><input id='prevpage' type='button' onclick='goto_previousPage()'></center>").insertBefore("#nextpage");
			interval+=max_entries_per_page;
			$("#prevpage").val("Previous page (Page "+((interval-max_entries_per_page)/max_entries_per_page)+")");
			update_page(false);
		}
		function goto_previousPage(){
			interval-=max_entries_per_page;
			if(interval==max_entries_per_page){$("#prevpage").remove(); page1=true;}
			else $("#prevpage").val("Previous page (Page "+((interval-max_entries_per_page)/max_entries_per_page)+")");
			update_page(false);
		}
		function scrollToBlockIntId(id){
			if(typeof $("#entry"+id).offset() !="undefined")
				$("html,body").animate({scrollTop:($("#entry"+id).offset().top)},500);
		}
		function scrollToBlockElemId(elemId,mode){
			if(typeof $("#"+elemId).offset() !="undefined")
				if(mode=="instant") $("html,body").animate({scrollTop:($("#"+elemId).offset().top)},1);
				else $("html,body").animate({scrollTop:($("#"+elemId).offset().top)},500);
		}
		function hide_entry_click(elem){
			var local_element=elem.parent().parent().parent().parent().attr("id");
			var database_element="hide"+elem.parent().parent().parent().parent().html().match(/dbindex=\"[0-9]+\"/g)[0].match(/[0-9]+/g);
			$.ajax({
				type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"hide",maxentries:max_entries_per_page,hideentries:database_element},
					success:function(data){}
			});
			hide_entries(extract_numbers(local_element),1000,'slidenshow',false);
		}
		function restore_block(elem){
			//TODO: RESTORE ORIGINAL BLOCK USING DATABASE_ELEMENT
			var local_element=elem.attr("id");
			var database_element=extract_numbers(elem.html().match(/dbindex="[0-9]+"/g)[0]).replace("\"","");
			elem.html("<div id='innerContent' style='border-radius: 3px; box-shadow: 0px 0px 10px rgb(0, 0, 0);"+
				"background-color: rgba(0, 0, 0, 0.5); margin-bottom: 3px; width: 750px; font-family: arial;"+ 
				"border-style: solid; border-color: rgb(18, 22, 48); border-width: 1px;''>"+
				"<span class='showbutton'>Loading...</span></div><div class='child'></div><div class='clearleft'></div>");

			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
				data:{username:username,password:password,sitename:user_websites,mode:"single_fetch",maxentries:max_entries_per_page,entrytofetch:database_element},
				success:function(data){
					data=remove_new_label(data,"single");
					hide_entries(extract_numbers(local_element),0,'clear','');
					var entry_numbers=extract_numbers(local_element)-(interval-max_entries_per_page);
					if(entry_numbers>=max_entries_per_page && entry_numbers!=1) $(data).insertAfter($("#entry"+(parseInt(extract_numbers(local_element)-1))));
					else $(data).insertBefore($("#entry"+(parseInt(extract_numbers(local_element))+1)));
					update_ids(interval-max_entries_per_page,false);
				}
			});
		}
		function synchronize_clientserver(startup){
			//TODO: PUT A LOADING SYMBOL UNDER prevpage
			if(startup){
				//LOAD FIRST BLOCKS FROM THE SERVER
				var first;
				$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"first",maxentries:max_entries_per_page},
					success:function(data){
						newentry=remove_new_label(data.split(";;;"),"array");
						for(var i=0;i<newentry.length;i++) $("#entrycontainer").append(newentry[i]);//INSERT NEW ENTRY BLOCKS:
						update_ids(0,false); //UPDATE ID ENTRIES:
						if(getEntryCount()>=max_entries_per_page && !nextpage_appended){
							$("#entrycontainer").append("<center><input id='nextpage' type='button' value='Next Page (Page "+((interval+max_entries_per_page)/max_entries_per_page)+")' onclick='goto_nextPage()'></center>");
							nextpage_appended=true;
						}
						$("#footer").css({"display":"block"});
						handle_entriesseen();
					}
				});
			}else{
				//ASK FOR DATABASE IF THERE'S ANYTHING NEW
				if(page1){
					var newentry;
					$.ajax({
						type:'GET',async:true,timeout:30000,url:'static/client_util.php',
						data:{username:username,password:password,sitename:user_websites,mode:"sync",maxentries:max_entries_per_page,entries_seen:entriesseen},
						success:function(data){
							newentry=data.split(";;;");
							if(newentry[0].indexOf("FLAG_IS_SYNCED")>-1) return; // CLIENT IS SYNCED WITH THE SERVER
							//INSERT NEW ENTRY BLOCKS:
							var currently_viewing_block;
							if($("#entry1").length==0) for(var i=0;i<newentry.length;i++) $("#entrycontainer").append(newentry[i]);
							else{
								currently_viewing_block=getElementInViewport();
								for(var i=0;i<newentry.length;i++)
									if(automatic_insertion) $(newentry[i]).insertBefore("#entry1");
									else if(newentry[i].length>0) entries_to_insert_buffer.push(newentry[i]);
							}
							//UPDATE ID ENTRIES:
							if(automatic_insertion)update_ids(0,false);	else update_ids(0,true);
							//UPDATE DOCUMENT TITLE:
							if(newentry.length-1>0)	update_title("add",newentry.length-1,0);
							//SCROLL TO WHATEVER BLOCK THE CLIENT WAS SEEING
							//if(typeof currently_viewing_block != 'undefined') scrollToBlockElemId(currently_viewing_block.attr("id"),"instant");
							//ADD NEXT PAGE IF NECESSARY:
							if(getEntryCount()>=max_entries_per_page && !nextpage_appended){
								$("#entrycontainer").append("<center><input id='nextpage' type='button' value='Next Page (Page "+((interval+max_entries_per_page)/max_entries_per_page)+")' onclick='goto_nextPage()'></center>");
								nextpage_appended=true;
							}
						}});
				}
			}
		}
		function isElementInViewport(el){
    		if (el instanceof jQuery) el = el[0];
    		var rect = el.getBoundingClientRect();
    		return(rect.top >= 0 &&
        		   rect.left >= 0 &&
        		   rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
        		   rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
    		);
		}
		function getElementInViewport(){
			for(var i=interval-max_entries_per_page+1;i<interval;i++) if(isElementInViewport($("#entry"+i))) return $("#entry"+i);
		}
		function runtimeout(){
			setTimeout(function(){
				if(is_syncing) synchronize_clientserver(false);
				runtimeout();}, 7000);
		}
		function handle_entriesseen(){
			for(var i=0;$($("#entrycontainer").children()[i]).length>0;i++){
				var elem_in_viewport=$($("#entrycontainer").children()[i]);
				if(isElementInViewport(elem_in_viewport))
				{
					var elem_for_db="entry"+extract_numbers(elem_in_viewport.html().match(/dbindex=\"[0-9]+?\"/g)[0]);
					setTimeout(function(){
						if(enable_entriesseen && entriesseen.indexOf(elem_for_db)==-1) entriesseen.push(elem_for_db);
						if(enable_entriesseen) $("#"+elem_in_viewport.attr("id")+" > #innerContent").stop().animate({opacity:0.4},400);
					},8000);
					break;
				}
			}
		}
		function handle_scroll(automaticnext_enable,entriesseen_enable){
			$(window).scroll(function(){
				if(isElementInViewport($("#nextpage"))){
					if(!is_loading && automaticnext_enable){
						//TODO: HANDLE ANIMATIONS SAYING LOADING
						setTimeout(function(){if(isElementInViewport($("#nextpage")))goto_nextPage();},2500);
					}
				}
				if(entriesseen_enable) handle_entriesseen();
			});
		}
		function handle_limit_scroll(){
			$(document).scroll(function(){
				//FIXED HEADER:
				if($(this).scrollTop()>50){ //ADD FIXED HEADER
					$("#header_fixed").slideDown(100);
					$("#lastentryviewed").css({"margin-top":"33px"});
					$("#togglesync").css({"margin-top":"-20px"});
					$("#live_alert_container").css({"margin-top":"30px"});
					$("#config_submenu").appendTo(".config_btn_fixed");
				}else{ //REMOVE FIXED HEADER
					$("#header_fixed").hide();
					$("#lastentryviewed").css({"margin-top":"60px"});
					$("#togglesync").css({"margin-top":"7px"});	
					$("#live_alert_container").css({"margin-top":"60px"});
					$("#config_submenu").appendTo(".config_btn_header");
				}
			});
		}
		function handle_keystroke(){
			$(document).keydown(function(e){
    			switch(e.keyCode){
    				case 37:if(!page1) goto_previousPage();break;
			        case 38://UP
			        	break;
			        case 39:goto_nextPage();break;
			        case 40://DOWN
			            break;
    			}
    		});
		}
		var animating_dropdown=false;
		function handle_misc(){
			$(".config_btn_fixed").mouseenter(function(){
				animating_dropdown=true;$("#config_submenu").css({"display":"block"});$("#config_submenu").stop().animate({opacity:1},250);
			});
			$(".config_btn_fixed").mouseleave(function(){
				animating_dropdown=false;setTimeout(function(){if(!animating_dropdown){$("#config_submenu").animate({opacity:0},250,function(){$("#config_submenu").css({"display":"none"});});}},1000);
			});
			//CFG BTN FIX:
			$(".config_btn_header").mouseenter(function(){
				animating_dropdown=true;$("#config_submenu").css({"display":"block"});$("#config_submenu").stop().animate({opacity:1},250);
			});
			$(".config_btn_header").mouseleave(function(){
				animating_dropdown=false;setTimeout(function(){if(!animating_dropdown){$("#config_submenu").animate({opacity:0},250,function(){$("#config_submenu").css({"display":"none"});});}},1000);
			});
		}
		$(document).ready(function(){ //INIT:
			synchronize_clientserver(true);
			runtimeout();
			handle_limit_scroll();
			handle_keystroke();
			handle_scroll(false,true);
			handle_misc();
		});
		</script>
	</body>
</html>