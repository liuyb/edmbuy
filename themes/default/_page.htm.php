<?php defined('IN_SIMPHP') or die('Access Denied');?><!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title><?php echo L('appname')?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="keywords" content="<?php echo $seo['keyword']?>">
  <meta name="description" content="<?php echo $seo['desc']?>">
  <meta name="author" content="Donglong Technical Team">
  <?php tplholder('HEAD_CSS');?>
  <?php tplholder('HEAD_JS');?>
  <?php
  //add css file
  add_css('/themes/mobiles/css/c.css',['scope'=>'global','ver'=>1]);
  add_css('index.css',['scope'=>'global']);
  add_css('mobile.css',['scope'=>'global','media'=>'only screen and (max-width:1025px)']);
  //add js file
  add_js('ext/jquery-1.8.3.min.js',['pos'=>'head','ver'=>1]);
  //add_js('vendor.js',['pos'=>'foot','ver'=>1]);
  //add_js('browser.js',['pos'=>'foot','ver'=>1]);
  ?>
</head>
<body >
<div id="wrapper" >
  <div id="item-1" class="page">
    <div class="w1000 header">
      <ul id="shareto" class=" clearfix">
        <li><a href="/blog/" target="_blank">官方博客</a></li>
        <li>分享到:</li>
        <li><a href="http://weibo.com/fxmapp" id="st1" target="_blank"></a></li>
        <li><a href="http://t.qq.com/fxmapp" id="st2" target="_blank"></a></li>
      </ul>
      <div class="clearfix" id="h_img">
        <div id="h_left">
          <a href="" id="logo">
            <img src="<?=$contextpath;?>themes/default/img/logo.png" alt="">
          </a>  
          <img src="<?=$contextpath;?>themes/default/img/wechat.png" alt="" id="h_wechat">   
        </div>
        <div id="h_right">
          <img src="<?=$contextpath;?>themes/default/img/h_right.png" alt="">
        </div>
      </div>
    </div>    
  </div>
  <div id="item-2" class="page">
    <div class="w1000">
      <ul id="fulist">
        <li>
          <div>
            <i class="li_bg li1"></i>
            <span class="li_title t1"></span>
            <p>真正想念一个人的时候，是超级严肃的时刻，调动你所有的内功，化成几行字，那是你最淳朴和原始地表达。</p>
          </div>
        </li>
        <li>
          <div>
            <i class="li_bg li2"></i>
            <span class="li_title t2"></span>
            
            <p>打开就有雪花般美妙的音乐响起？夸张吓人也说不准哦！你珍视这种穿越的祝福方式么？利用眼下这高科技再整一次吧！</p>
          </div>
        </li>
        <li>
          <div>
            <i class="li_bg li3"></i>
            <span class="li_title t3"></span>
            <p>太多的情绪无法传达至彼岸，但你此刻就想和一个人对饮、共叙、同醉！那不如就选一首歌或直接哼一段旋律，给TA送过去！</p>
          </div>
        </li>
        <li>
          <div>
            <i class="li_bg li4"></i>
            <span class="li_title t4"></span>
            <p>礼尚往来？我们如果总以同样的态度回敬，又有谁会知道你很有趣呢？你精心挑选的那一刻，将会成为你和TA生命记忆中一道闪电！</p>
          </div>
        </li>
        <li>
          <div>
            <i class="li_bg li5"></i>
            <span class="li_title t5"></span>
            <p>这是一列通往祝福的列车、车上载着很多人经过了很多站，沿途的礼物缤纷不断又美不胜收。想知道什么时候又出发吗？关注福小秘的活动告示吧！</p>
          </div>
        </li>
        <li>
          <div>
            <i class="li_bg li6"></i>
            <span class="li_title t6"></span>
            <p>用交流和分享来记录你拥有福小秘以来的日子和有趣的话题。在这里还潜藏着很多礼物专属方案，你可以为你在乎的人私人定制哦。</p>
          </div>
        </li>
        <li>
          <div>
            <i class="li_bg li7"></i>
            <span class="li_title t7"></span>
            <p>礼品甄选，就是艺术家创作和发现的乐园。只要你说明送礼物的重点，福小秘就会请他们帮你打理好一切。从品质到应时应景都帮你表达得妥当可心儿，多套方案让你挑！</p>
          </div>
        </li>
        <li>
          <div>
            <i class="li_bg li8"></i>
            <span class="li_title t8"></span>
            <p>一声“布谷！”就表示有人想送你礼物啦！当然，如果你想送出的礼物很多，还需要准时，那么也记得让它提醒你哦！</p>
          </div>
        </li> 
      </ul>
    </div>
  </div>
  <div id="item-3" class="page">
    <div class="w1000">
      <img src="<?=$contextpath;?>themes/default/img/wechat.png" alt="" id="wechat">
      <div>
      <h2 class="fh2">
        <img src="<?=$contextpath;?>themes/default/img/footer_h.png" alt="">
      </h2>
      <div class="center">
         <a target="_blank" href="http://shang.qq.com/wpa/qunwpa?idkey=7fa204959611d885514baaa360aae2b1c31475204a8e805bf1fda47bf2ccd26f">
            <i class="f1"></i>
          </a>
          <a href="http://weibo.com/fxmapp" target="_blank">
            <i class="f2"></i>
          </a>
            <a href="http://t.qq.com/fxmapp" target="_blank">
            <i class="f3"></i>
          </a>
      </div> 
      </div>
      <p id="copyright">Copyright © <?php echo date('Y')?> Donglong. All Rights Reserved </p>
    </div>    
  </div>
</div>
<!--
  <ul id="nav">
      <li class="nav-item"> <a href="#item-1" class="current item-1"></a> </li>
      <li class="nav-item"><a class="" href="#item-2"></a></li>
      <li class="nav-item"><a class="" href="#item-3" ></a></li>
      <li id="nav-item-back"><a href="javascript:;" class="sprite back"></a></li>
  </ul>
-->
<ul class="page-nav" id="page-nav">
  <li class="nav-item"><a href="#item-1" class="active"></a></li>
  <li class="nav-item"><a href="#item-2"></a></li>
  <li class="nav-item"><a href="#item-3"></a></li>
  <li class="nav-item-back"><a href="javascript:;"></a></li>
</ul>
<?php 
//add js file
add_js('ext/touchslider.min.js',['pos'=>'head','ver'=>1]);
?>
<script type="text/javascript">
$(function(){
	var _active = 0, $_ap = $('#page-nav a');
  var t1 = new TouchSlider({
     id:'wrapper',
     auto: false,
     speed:600,
     timeout:6000,
     direction:'down',
     before:function(newIndex, oldSlide){
       $_ap.parent().removeClass('redbg');
       $_ap.get(_active).className = '';
       _active = newIndex;
       if(1==newIndex) $_ap.parent().addClass('redbg');
       $_ap.get(_active).className = 'active';
     }
  });
  
  $_ap.each(function(index,ele){
    $(ele).click(function(){
      if($(ele).parent().hasClass('nav-item-back')) {
    	  t1.slide(0);
      }else{
    	  t1.slide(index);
      }
      return false;     
    });
  });
  
  setTimeout(function(){t1.resize();},500);
});
</script>
</body>
<script>var FST=new Object();FST.autostart=1;</script>
<script type="text/javascript" src="<?=$contextpath;?>misc/js/fst.min.js"></script>
<?php tplholder('FOOT_JS');?>
</html>