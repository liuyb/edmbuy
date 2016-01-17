<?php defined('IN_SIMPHP') or die('Access Denied');?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title><?php echo $seo['title']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="keywords" content="<?php echo $seo['keyword']?>">
<meta name="description" content="<?php echo $seo['desc']?>">
<meta name="author" content="Edmbuy Technical Team">
<meta name="apple-mobile-web-app-title" content="<?php echo L('appname')?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<!-- <meta name="apple-mobile-web-app-status-bar-style" content="black"> -->
<meta name="format-detection" content="telephone=no">
<link rel="dns-prefetch" href="merchant.edmbuy.com" />
<link rel="dns-prefetch" href="res.wx.qq.com" />
<?php if (C('env.usecdn')):?>
<link rel="dns-prefetch" href="fdn.edmbuy.com" />
<?php endif;?>
<link rel="shortcut icon" href="<?=$contextpath;?>favicon.ico" type="image/x-icon" />
<link rel="apple-touch-icon" sizes="114x114" href="<?=$contextpath;?>misc/images/napp/touch-icon-114.png" />
<link rel="apple-touch-icon" sizes="144x144" href="<?=$contextpath;?>misc/images/napp/touch-icon-144.png" />
<?php tplholder('HEAD_CSS');?>
<?php tplholder('HEAD_JS');?>
<?php headscript();?>
<script>gData.page_render_mode=parseInt('<?=$page_render_mode?>');
function show_mtop(html) {
	if (typeof(show_mtop.dom)=='undefined' || !show_mtop.dom) {
		show_mtop.dom = $('#Mtop');
	}
	show_mtop.dom.html(html).show();
}
function show_topnav(html) {
	if (typeof(show_topnav.dom)=='undefined' || !show_topnav.dom) {
		show_topnav.dom = $('#topnav');
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
	}else if(append>0) {
		show_mnav.dom.append(html).show;
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
</head>
<body style="background: #eee;">
<div id="Mtop"></div>
<nav id="Mnav" class="nav no-bounce nav-<?=$nav_no?>"><?php include T('_nav');?></nav>
<div id="Mbody">
  <nav id="topnav" class="topnav no-bounce topnav-<?=$topnav_no?>"></nav>
  <div id="activePage" class="useTopNav-<?=$topnav_no?> useNav-<?=$nav_no?>"><section class="scrollArea<?php if(isset($extra_css)&&!empty($extra_css)) echo ' '.$extra_css?>">
  	<?php if(1==$page_render_mode):?>
  		<?php include T($tpl_content);?>
  		<script>$(window).load(function(){F.set_scroller(false,100)})</script>
  	<?php endif;?>
  	</section>
    <div class="pageBg">品质生活从这里开始🔛</div>
  </div>
  <div id="loadingCover" class="useTopNav-<?=$topnav_no?> useNav-<?=$nav_no?>"></div>
  <?php include T('inc/popalert');?>
</div>
<?php include T('inc/popdlg');?>
<!-- 微信操作提示 -->
<div id="cover-wxtips" class="cover" style="display:none;"><img alt="" src="<?=$contextpath;?>themes/mobiles/img/guide.png"/></div>
<div style="display:none;"><img src="<?php echo ploadingimg()?>" alt=""/></div>
</body>
<?php tplholder('FOOT_JS');?>
<script>var FST=new Object();FST.autostart=1;FST.uid=parseInt(gUser.uid);</script>
<script type="text/javascript" src="<?=$contextpath;?>misc/js/fst.min.js"></script>
<?php shareinfo(isset($share_info) ? $share_info : array());?>
<?php footscript();?>
</html><?php

//: add css & js files
if (C('env.usecdn')):
add_css('http://fdn.edmbuy.com/css/c.min.css',['scope'=>'global','ver'=>'none']);
add_js('http://fdn.edmbuy.com/js/jquery-2.1.3.min.js',['pos'=>'head','ver'=>'none']);
add_js('http://fdn.edmbuy.com/js/fm.min.js',['pos'=>'foot','ver'=>'none']);
else:
add_css('c.min.css',['scope'=>'global','ver'=>'none']);
add_js('ext/jquery-2.1.3.min.js',['pos'=>'head','ver'=>'none']);
add_js('fm.min.js',['pos'=>'foot','ver'=>'none']);
endif;
add_css('m.css',['scope'=>'global']);
add_js('g.js',['pos'=>'head']);
add_js('m.js',['pos'=>'foot']);

?>