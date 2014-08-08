<?php session_start(); if(!isset($_SESSION['myusername'])) header("location:login.php");if($_SESSION['myusername']!="botadmin" && $_SESSION['mypassword']!='a2790121aecb468122a0e2e0f2240e68') header("location:logout.php");?>
<script>
	var username="<?php echo $_SESSION['myusername']; ?>";
	var password="<?php echo $_SESSION['mypassword']; ?>";
	var user_websites="<?php echo $_SESSION['websites']?>";
	var latest_titles=new Array();
	var max_entries_per_page=100;
	var first_entry_to_show=0;
	var blockcounter=0;
	var is_fetching=false;
</script>
<html>
	<header>
		<title>Feedblock Admin Page</title>
		<script type="text/javascript" src="../scripts/jquery-1.11.1.min.js"></script>
	</header>
		<span style='font-family:arial'>Logged in</span>
		<form action="logout.php">
			<input type="submit" value='Logout'>
		</form>
		<div id='blockcounter'>0 blocks added</div>
		<input type='button' id='fetchbuttontoggler' value='Begin fetch' onclick='toggle_fetching()'>
		<input type='button' id='cleardatabase' value='Clear database' onclick='clear_db()'>
		<script>
			function clear_db(){
				$.ajax({type:'POST',async:false,timeout:30000,url:"util_entries.php",
						data:{username:username,password:password,mode:"cleardb",html:""}
				});
				blockcounter=0;$("#blockcounter").text(blockcounter+" blocks added");
				$("#cleardatabase").val("Cleared");setTimeout(function(){$("#cleardatabase").val("Clear database");},3000);
			}
			function toggle_fetching(){
				if(is_fetching){is_fetching=false;$("#fetchbuttontoggler").val("Continue fetch");
				}else{is_fetching=true;$("#fetchbuttontoggler").val("Stop fetch");runtimeout();}
			}
			function get_block_count(){
				var count=0;
				$.ajax({type:'POST',async:false,timeout:30000,url:"util_entries.php",
						data:{username:username,password:password,mode:"getcount",html:""},
						success:function(data){count=parseInt(data);}
				});return count;
			}
			function update_block_counter(count){
				blockcounter+=count; $("#blockcounter").text(blockcounter+" blocks added"); //UPDATE COUNTER
				$.ajax({type:'POST',async:false,timeout:30000,url:"util_entries.php",
					data:{username:username,password:password,mode:"addcount",html:"",entrycount:count}
				});
			}
			function add_user_entries(html_to_add){
				$.ajax({type:'POST',async:false,timeout:30000,url:"util_entries.php",
						data:{username:username,password:password,mode:"add",html:html_to_add,entrytype:html_to_add.match(/url='.+?'/g)[0]}
				});
			}
			function fetch(recent){
				var result="";
				$.ajax({type:'get',async:false,timeout:30000,url:"fetcher.php",
						data: "sitename="+user_websites+"&recent="+recent,
						success:function(data){result=data;}
				});
				return result;
			}
			function update_database(newentry){
				if(newentry[0].length<=0) return;
				var newentry_strformat="";
				for(var i=0;i<newentry.length-1;i++) add_user_entries(newentry[i]+";;;");
				update_block_counter(newentry.length-1);
			}
			function runtimeout(){
				setTimeout(function () {
					var newentry=fetch(1).split(";;;"); //FETCH NEW BLOCKS
					update_database(newentry); //UPDATE ENTRY BLOCKS ON DATABASE
					if(is_fetching) runtimeout();
				}, 7000);
			}
			$(document).ready(function(){ //INIT:
				blockcounter=get_block_count();	$("#blockcounter").text(blockcounter+" blocks added");
			});
		</script>
	</body>
</html>