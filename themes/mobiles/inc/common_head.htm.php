<?php defined('IN_SIMPHP') or die('Access Denied');?>
<script>gData.page_render_mode=parseInt('<?=$page_render_mode?>');
gData.page_use_iscroll = 1==gData.page_render_mode ? 0 : 1;
function show_mtop(html) {
	if (typeof(show_mtop.dom)=='undefined' || !show_mtop.dom) {
		show_mtop.dom = $('#Mtop');
	}
	show_mtop.dom.html(html).show();
}
function show_topnav(html) {
	if (typeof(show_topnav.dom)=='undefined' || !show_topnav.dom) {
		show_topnav.dom = $('#Mhead');
	}
	show_topnav.dom.html(html).show();
}
function show_mnav(html, append) {
	if (typeof(show_mnav.dom)=='undefined' || !show_mnav.dom) {
		show_mnav.dom = $('#Mnav');
	}
	if (typeof(append)=='undefined') append = 0;
	
	if (append<0) { 
		show_mnav.dom.prepend(html).show();
	} else if(append>0) {
		show_mnav.dom.append(html).show();
	} else {
		show_mnav.dom.html(html).show();
	}
}
function append_to_body(html) {
	$(document.body).append(html);
}
function required_uinfo_empty() {
	return gUser.nickname==''&&gUser.logo=='' ? true : false;
}
</script>