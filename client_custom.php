<?php session_start(); if(!isset($_SESSION['myusername'])) header("location:static/login.php"); ?>
<?php 
	$db_name="scrapfeed_website";
	$tbl_name="members";
	$botuser="botadmin";
	$botpass="a2790121aecb468122a0e2e0f2240e68";
	$username=$_SESSION["myusername"];
	$password=$_SESSION["mypassword"];
	mysql_connect("localhost","root","") or die("Couldn't connect to database");
	mysql_select_db($db_name) or die("Couldn't select database");
	$_SESSION["websites"]=mysql_fetch_array(mysql_query("SELECT websites FROM members WHERE username='".$_SESSION['myusername']."' AND password='".$_SESSION['mypassword']."'"))[0];	
	$_SESSION["entriesperpage"]=mysql_fetch_array(mysql_query("SELECT entriesperpage FROM members WHERE username='".$_SESSION['myusername']."' AND password='".$_SESSION['mypassword']."'"))[0];
?>
<script>
	var username="<?php echo $_SESSION['myusername']; ?>";
	var password="<?php echo $_SESSION['mypassword']; ?>";
	var user_websites="<?php echo $_SESSION['websites']?>";
</script>
<html>
	<header>
		<title>Feedblock | Account Options</title>
		<script type="text/javascript" src="../scripts/jquery-1.11.1.min.js"></script>
		<script>
		function IsNumeric(input){
		    return (input - 0) == input && (''+input).replace(/^\s+|\s+$/g, "").length > 0;
		}
		function deleteSite(elem){
			$("#alertChanges").html("<br>Removing "+elem.parent().attr('id').replace(";","")+" ...");
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"deletesite",sitetodelete:elem.parent().attr('id')},
					success:function(){location.reload();}
			});
		}
		function addSite(){
			var websiteSelector=document.getElementById("addWebsite");
			var websiteToAdd=websiteSelector.options[websiteSelector.selectedIndex].text;
			$("#alertChanges").html("<br>Adding "+websiteToAdd+" ...");
			if(user_websites.indexOf(websiteToAdd)==-1){
				$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
						data:{username:username,password:password,sitename:user_websites,mode:"addsite",sitetoadd:websiteToAdd},
						success:function(){location.reload();}
				});
			}else{
				$("#alertChanges").html("<br>Already using this website");
				setTimeout(function(){$("#alertChanges").html("");},2000);
			}
		}
		function alterEntriesPerPage(){
			var user_entered_entriespp=$("#newentriesperpage").val();
			if(!IsNumeric(user_entered_entriespp)){
				$("#alertChangesentriespp").html("<br>Invalid input");
				setTimeout(function(){$("#alertChangesentriespp").html("");	$("#newentriesperpage").val("");return;},2000);
			}
			if(!user_entered_entriespp.length) return; 
			if(parseInt(user_entered_entriespp)>200){
				$("#alertChangesentriespp").html("<br>That number is too high");
				setTimeout(function(){$("#alertChangesentriespp").html("");},2000);
			}else{
				$("#alertChangesentriespp").html("<br>Changing entries per page to "+user_entered_entriespp+" ...");
				$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
						data:{username:username,password:password,sitename:user_websites,mode:"alterentriesperpage",newentriesperpage:user_entered_entriespp},
						success:function(){location.reload();}
				});
			}
		}
		function toggle_entriesseen(elem){
			var bool_to_send="0";
			switch(elem.val()){
				case "Disable": bool_to_send="0"; break;
				case "Enable": bool_to_send="1"; break;
			}
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"setentriesseen_enableflag",setentryseenenable:bool_to_send},
					success:function(){location.reload();}
			});	
		}
		function toggle_automatic_insertion(elem){
			var bool_to_send="0";
			switch(elem.val()){
				case "Disable": bool_to_send="0"; break;
				case "Enable": bool_to_send="1"; break;
			}
			$.ajax({type:'GET',async:true,timeout:30000,url:'static/client_util.php',
					data:{username:username,password:password,sitename:user_websites,mode:"setautomaticentry_enableflag",setautomaticentry:bool_to_send},
					success:function(){location.reload();}
			});	
		}
		</script>
	</header>
	<body background="img/background.png">
		<form action="index.php">
			<input type="submit" value='Return'>
		</form>
		<!--//HANDLE USER WEBSITES:-->
		<div style='float:left;width:250;height:200;background-color:white;font-family:Arial;border-radius:5px;box-shadow:0px 0px 10px 0px #000000'>
		<b><center style='font-size:20px'>User websites</center></b>
			<!--//REMOVE WEBSITES FROM USER ACCOUNT:-->
			<?php
				$sitearray=explode(";",$_SESSION["websites"]);
				echo "<br><center>";
				for($i=0;$i<count($sitearray);$i++) if(strlen($sitearray[$i])!=0) echo "<span id='$sitearray[$i]".(($i!=count($sitearray)-1)?";":"")."'>".$sitearray[$i]." <a href='#' style='font-size:10px' onclick='deleteSite($(this))'>Remove</a></span><br>";
			?>
			<!--//ADD WEBSITES FROM USER ACCOUNT:-->
			<br>Add:<br>
			<select id='addWebsite'>
				<?php
					$db_sitesavailable=explode(";",mysql_fetch_array(mysql_query("SELECT websites FROM members WHERE username='$botuser' AND password='$botpass'"))[0]);
					for($i=0;$i<count($db_sitesavailable);$i++) echo "<option value='$db_sitesavailable[$i]'>$db_sitesavailable[$i]</option>";
				?>
			</select>
			<input id='addWebsiteButtonId' type='button' onclick='addSite()' value='Add'>
			<span id='alertChanges'></span>
			</center>	
		</div>
		<!--HANDLE BLOCKS PER PAGE-->
		<div style='margin-left:10px;float:left;width:300;height:200;background-color:white;font-family:Arial;border-radius:5px;box-shadow:0px 0px 10px 0px #000000'>
			<center><b>Entries per page</b></center><br>
			<?php echo "<center><br><span>Currently viewing: <b>".$_SESSION["entriesperpage"]."</b> entries per page</span>";?>
			<br><br><span>Change entries per page: <input id='newentriesperpage' type='text' name='alterentriesperpage'></span>
			<input type='button' onclick='alterEntriesPerPage()' value='Apply'>
			<br><span style='font-size:10px'>(Write number. Eg.: 100)</span>
			<span id='alertChangesentriespp'></span>
			
			</center>
		</div>
		<!-- HANDLE AUTOMATIC INSERTION OF ENTRIES -->
		<div style='margin-left:10px;float:left;width:300;height:200;background-color:white;font-family:Arial;border-radius:5px;box-shadow:0px 0px 10px 0px #000000'>
			<center><b>Automatic entry insertion</b></center>	
			<?php 
				echo "<center><br><span>Currently:<b>"; 
				$isenabled=mysql_fetch_array(mysql_query("SELECT entryautomatic FROM members WHERE username='$username' AND password='$password'"))[0];
				if($isenabled)
					echo "Enabled</b><br><input type='button' onclick='toggle_automatic_insertion($(this))' value='Disable'>";
				else echo "Disabled</b><br><input type='button' onclick='toggle_automatic_insertion($(this))' value='Enable'>";
				echo "</center>";
			?>
		</div>
		<!-- HANDLE ENTRIES SEEN ENABLE -->
		<div style='margin-left:10px;float:left;width:300;height:200;background-color:white;font-family:Arial;border-radius:5px;box-shadow:0px 0px 10px 0px #000000'>
			<center><b>Entries seen transparency</b></center>	
			<?php 
				echo "<center><br><span>Currently:<b>"; 
				$isenabled=mysql_fetch_array(mysql_query("SELECT entriesseen_enable FROM members WHERE username='$username' AND password='$password'"))[0];
				if($isenabled)
					echo "Enabled</b><br><input type='button' onclick='toggle_entriesseen($(this))' value='Disable'>";
				else echo "Disabled</b><br><input type='button' onclick='toggle_entriesseen($(this))' value='Enable'>";
				echo "</center>";
			?>
		</div>
	</body>
</html>