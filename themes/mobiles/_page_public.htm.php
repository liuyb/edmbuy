<?php defined('IN_SIMPHP') or die('Access Denied');?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title><?php echo L('appname')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="keywords" content="<?php echo $seo['keyword']?>">
<meta name="description" content="<?php echo $seo['desc']?>">
<meta name="author" content="Donglong Technical Team">
<meta name="apple-mobile-web-app-title" content="<?php echo L('appname')?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<!-- Android -->
<meta name="mobile-web-app-capable" content="yes">
<link rel="shortcut icon" sizes="196x196" href="<?=$contextpath;?>misc/images/napp/touch-icon-196.png" />
<link rel="shortcut icon" sizes="152x152" href="<?=$contextpath;?>misc/images/napp/touch-icon-152.png" />
<link rel="shortcut icon" sizes="144x144" href="<?=$contextpath;?>misc/images/napp/touch-icon-144.png" />
<link rel="shortcut icon" sizes="128x128" href="<?=$contextpath;?>misc/images/napp/touch-icon-128.png" />
<link rel="shortcut icon" sizes="120x120" href="<?=$contextpath;?>misc/images/napp/touch-icon-120.png" />
<link rel="shortcut icon" sizes="114x114" href="<?=$contextpath;?>misc/images/napp/touch-icon-114.png" />
<link rel="shortcut icon" sizes="76x76" href="<?=$contextpath;?>misc/images/napp/touch-icon-76.png" />
<link rel="shortcut icon" sizes="72x72" href="<?=$contextpath;?>misc/images/napp/touch-icon-72.png" />
<link rel="shortcut icon" sizes="60x60" href="<?=$contextpath;?>misc/images/napp/touch-icon-60.png" />
<link rel="shortcut icon" sizes="57x57" href="<?=$contextpath;?>misc/images/napp/touch-icon-57.png" />
<link rel="shortcut icon" href="<?=$contextpath;?>favicon.ico" type="image/x-icon" />
<link rel="icon" href="<?=$contextpath;?>favicon.ico" type="image/x-icon" />
<!-- iOS -->
<link rel="apple-touch-icon" sizes="57x57" href="<?=$contextpath;?>misc/images/napp/touch-icon-57.png" />
<link rel="apple-touch-icon" sizes="60x60" href="<?=$contextpath;?>misc/images/napp/touch-icon-60.png" />
<link rel="apple-touch-icon" sizes="72x72" href="<?=$contextpath;?>misc/images/napp/touch-icon-72.png" />
<link rel="apple-touch-icon" sizes="76x76" href="<?=$contextpath;?>misc/images/napp/touch-icon-76.png" />
<link rel="apple-touch-icon" sizes="114x114" href="<?=$contextpath;?>misc/images/napp/touch-icon-114.png" />
<link rel="apple-touch-icon" sizes="120x120" href="<?=$contextpath;?>misc/images/napp/touch-icon-120.png" />
<link rel="apple-touch-icon" sizes="144x144" href="<?=$contextpath;?>misc/images/napp/touch-icon-144.png" />
<link rel="apple-touch-icon" sizes="152x152" href="<?=$contextpath;?>misc/images/napp/touch-icon-152.png" />
<?php tplholder('HEAD_CSS');?>
<?php tplholder('HEAD_JS');?>
<?php
//add css file
add_css('c.min.css',['scope'=>'global','ver'=>1]);
add_css('m_public.css',['scope'=>'global']);
?>
<?php if (Request::isIE() && Request::ieVer()<'9.0'):?>
<?php add_js('ext/jquery-1.8.3.min.js',['pos'=>'head','ver'=>'none'])?>
<?php else:?>
<?php add_js('ext/jquery-2.1.1.min.js',['pos'=>'head','ver'=>'none'])?>
<?php endif?>
</head>
<body>
<div id="rtWrap"><?php include T($tpl_content);?></div>
<script>var FST=new Object();FST.autostart=1;</script>
<script type="text/javascript" src="<?=$contextpath;?>misc/js/fst.min.js"></script>
</body>
</html>