<?php defined('IN_SIMPHP') or die('Access Denied');?><!DOCTYPE html>
<html lang="zh-CN" class="SPA">
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
<script type="text/javascript">gData.page_render_mode=1;</script>
</head>
<body class="SPA<?php if(isset($extra_css)&&!empty($extra_css)) echo ' '.$extra_css?>">
<?php include T($tpl_content);?>
<?php include T('inc/popalert');?>
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
add_js('http://fdn.edmbuy.com/js/fm.min.js',['pos'=>'head','ver'=>'none']);
else:
add_css('c.min.css',['scope'=>'global','ver'=>'none']);
add_js('ext/jquery-2.1.3.min.js',['pos'=>'head','ver'=>'none']);
add_js('fm.min.js',['pos'=>'head','ver'=>'none']);
endif;
add_css('m.css',['scope'=>'global']);
add_js('m.js',['pos'=>'head']);

?>