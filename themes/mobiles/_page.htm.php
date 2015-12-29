<?php defined('IN_SIMPHP') or die('Access Denied');?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title><?php echo L('appname')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="keywords" content="<?php echo $seo['keyword']?>">
<meta name="description" content="<?php echo $seo['desc']?>">
<meta name="author" content="Donglong Technical Team">
<meta name="apple-mobile-web-app-title" content="<?php echo L('appname')?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<!-- <meta name="apple-mobile-web-app-status-bar-style" content="black"> -->
<meta name="format-detection" content="telephone=no">
<link rel="dns-prefetch" href="fp.xurl.cn" />
<link rel="dns-prefetch" href="res.wx.qq.com" />
<?php if (C('env.usecdn')):?>
<link rel="dns-prefetch" href="fcdn.qiniudn.com" />
<?php endif;?>
<link rel="shortcut icon" href="<?=$contextpath;?>favicon.ico" type="image/x-icon" />
<link rel="apple-touch-icon" sizes="114x114" href="<?=$contextpath;?>misc/images/napp/touch-icon-114.png" />
<link rel="apple-touch-icon" sizes="144x144" href="<?=$contextpath;?>misc/images/napp/touch-icon-144.png" />
<?php tplholder('HEAD_CSS');?>
<?php tplholder('HEAD_JS');?>
<?php headscript();?>
</head>
<body>
<div id="gtop"></div>
<div id="root">
  <nav id="topnav" class="topnav topnav-<?=$topnav_no?>"></nav>
  <div id="activePage" class="useTopNav-<?=$topnav_no?> useNav-<?=$nav_no?>"><section class="scrollArea"></section>
    <div class="pageBg"><em>“正品保证”</em>不仅仅是一句口号</div>
  </div>
  <div id="loadingCanvas" class="useTopNav-<?=$topnav_no?> useNav-<?=$nav_no?>"></div>
  <div class="hide"><img src="<?php echo ploadingimg()?>" alt=""/></div>
</div>
<?php include T('_nav');?>
<?php include T('_popdlg');?>
</body>
<?php /*footscript();*/?>
<?php tplholder('FOOT_JS');?>
<script>
function show_gtop(html) {
	if (typeof(show_gtop.dom)=='undefined') {
		show_gtop.dom = $('#gtop');
	}
	show_gtop.dom.html(html).show();
}
function show_topnav(html) {
	if (typeof(show_topnav.dom)=='undefined') {
		show_topnav.dom = $('#topnav');
	}
	show_topnav.dom.html(html).show();
}
function show_nav(html) {
	if (typeof(show_nav.dom)=='undefined') {
		show_nav.dom = $('#nav').show();
	}
	show_nav.dom.html(html);
}
function append_to_body(html) {
	$(document.body).append(html);
}
</script>
<script>var FST=new Object();FST.autostart=1;FST.uid=parseInt(gUser.uid);</script>
<script type="text/javascript" src="<?=$contextpath;?>misc/js/fst.min.js"></script>
</html><?php

//: add css & js files
if (C('env.usecdn')):
add_css('http://fcdn.fxmapp.com/css/c.min.css',['scope'=>'global','ver'=>'none']);
add_js('http://fcdn.fxmapp.com/js/jquery-2.1.3.min.js',['pos'=>'head','ver'=>'none']);
add_js('http://fcdn.fxmapp.com/js/fm.min.js',['pos'=>'foot','ver'=>'none']);
else:
add_css('c.min.css',['scope'=>'global','ver'=>'none']);
add_js('ext/jquery-2.1.3.min.js',['pos'=>'head','ver'=>'none']);
add_js('fm.min.js',['pos'=>'head','ver'=>'none']);
endif;
add_css('m.css',['scope'=>'global']);
add_js('g.js',['pos'=>'head']);
add_js('m.js',['pos'=>'foot']);

?>