function getelemtitle(entry_html,entry_name){
	var title="empty";
	switch(entry_name){
		case "reddit": title=new RegExp("tabindex=\"1\".+?</a>").exec(entry_html); break;
	}
	return title;
}