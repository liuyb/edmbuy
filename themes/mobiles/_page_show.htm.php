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
<?php echo tplholder('HEAD_CSS');?>
<?php echo tplholder('HEAD_JS');?>
<script>var SESS_UID=parseInt('<?=$theuid?>');var isSubscribe=parseInt('<?=$isSubscribe?>');</script>
<style>
#wrapper {height:100%;}
<?php if ($ntype=='card'):?>
  /** 预定义样式 */
  #card {margin: 0 auto;position: relative;}
  #card .card_bg{z-index: -100;}
  #card_img {}
  #card_frame{}
  #card_to {width:100px;height:30px;line-height:30px;color:#000;font-size:12px;position: absolute;}
  #card_from {width:100px;height: 30px;line-height:30px;color:#000;font-size:14px;position:absolute;text-align: right;}
  #card_content {width: 150px;height: 60px;line-height:20px;color:#000;font-size:13px;overflow: hidden;}
  
  /*#card {background: url('<?=$node_info['card_url']?>') no-repeat;background-size:320px;}*/
  #card_img {
    position: absolute;
    <?php if($node_info['has_img']): ?>
    <?php else: ?>display: none;
    <?php endif?>
    <?php if(!empty($node_info['img_url'])):?>
    background:url(<?=$node_info['img_url']?>) no-repeat;
    background-size:100%;
    <?php endif?>
    <?php if(!empty($node_info['img_style']['top'])):?>
    top:<?=$node_info['img_style']['top']?>px;
    <?php endif?>
    <?php if(!empty($node_info['img_style']['left'])):?>
    left:<?=$node_info['img_style']['left']?>px;
    <?php endif?>
    <?php if(!empty($node_info['img_style']['width'])):?>
    width:<?=$node_info['img_style']['width']?>px;
    <?php endif?>
    <?php if(!empty($node_info['img_style']['height'])):?>
    height: <?=$node_info['img_style']['height']?>px;
    <?php endif?>
  }
  #card_frame {
    position: absolute;
    background-color: transparent;
    <?php if($node_info['has_frame']): ?>
    <?php else: ?>display: none;
    <?php endif?>
    <?php if(!empty($node_info['frame_url'])):?>
    background:url(<?=$node_info['frame_url']?>) no-repeat;
    background-size:100%;
    <?php endif?>
    <?php if(!empty($node_info['frame_style']['top'])):?>
    top:<?=$node_info['frame_style']['top']?>px;
    <?php endif?>
    <?php if(!empty($node_info['frame_style']['left'])):?>
    left:<?=$node_info['frame_style']['left']?>px;
    <?php endif?>
    <?php if(!empty($node_info['frame_style']['width'])):?>
    width:<?=$node_info['frame_style']['width']?>px;
    <?php endif?>
    <?php if(!empty($node_info['frame_style']['height'])):?>
    height: <?=$node_info['frame_style']['height']?>px;
    <?php endif?>
  }
  #card_to {
    position: absolute;
    <?php if(!$node_info['has_to']):?>
    display:none;
    <?php endif?>
    <?php if(!empty($node_info['to_style']['top'])):?>
    top:<?=$node_info['to_style']['top']?>px;
    <?php endif?>
    <?php if(!empty($node_info['to_style']['left'])):?>
    left:<?=$node_info['to_style']['left']?>px;
    <?php endif?>
    <?php if(!empty($node_info['to_style']['width'])):?>
    width:<?=$node_info['to_style']['width']?>px;
    <?php endif?>
    <?php if(!empty($node_info['to_style']['height'])):?>
    height: <?=$node_info['to_style']['height']?>px;
    <?php endif?>
    <?php if(!empty($node_info['to_style']['color'])):?>
    color:<?=$node_info['to_style']['color']?>;
    <?php endif?>
    <?php if(!empty($node_info['to_style']['fontSize'])):?>
    font-size:<?=$node_info['to_style']['fontSize']?>px;
    <?php endif?>
  }
  #card_from {
    <?php if(!$node_info['has_from']):?>
    display:none;
    <?php endif?>
    position: absolute;
    <?php if(!empty($node_info['from_style']['top'])):?>
    top:<?=$node_info['from_style']['top']?>px;
    <?php endif?>
    <?php if(!empty($node_info['from_style']['left'])):?>
    left:<?=$node_info['from_style']['left']?>px;
    <?php endif?>
    <?php if(!empty($node_info['from_style']['left'])):?>
    width:<?=$node_info['from_style']['width']?>px;
    <?php endif?>
    <?php if(!empty($node_info['from_style']['height'])):?>
    height: <?=$node_info['from_style']['height']?>px;
    <?php endif?>
    <?php if(!empty($node_info['from_style']['color'])):?>
    color:<?=$node_info['from_style']['color']?>;
    <?php endif?>
    <?php if(!empty($node_info['from_style']['fontSize'])):?>
    font-size:<?=$node_info['from_style']['fontSize']?>px;
    <?php endif?>
  }
  #card_content {
    position: absolute;
    <?php if(empty($node_info['content'])):?>
    display:none;
    <?php endif;?>
    <?php if(!empty($node_info['content_style']['top'])):?>
    top:<?=$node_info['content_style']['top']?>px;
    <?php endif?>
    <?php if(!empty($node_info['content_style']['left'])):?>
    left:<?=$node_info['content_style']['left']?>px;
    <?php endif?>
    <?php if(!empty($node_info['content_style']['width'])):?>
    width:<?=$node_info['content_style']['width']?>px;
    <?php endif?>
    <?php if(!empty($node_info['content_style']['height'])):?>
    height: <?=$node_info['content_style']['height']?>px;
    <?php endif?>
    <?php if(!empty($node_info['content_style']['color'])):?>
    color:<?=$node_info['content_style']['color']?>;
    <?php endif?>
    <?php if(!empty($node_info['content_style']['fontSize'])):?>
    font-size:<?=$node_info['content_style']['fontSize']?>px;
    <?php endif?>
  }
  
<?php elseif ($ntype=='music'):?>

<?php endif;?>
</style>
</head>
<body>
<div id="rtWrap">

<div id="main" style="overflow-y:auto">

<?php if ($ntype=='card'):?>
  <div id="card">
    <img src="<?=$node_info['card_url']?>" class="card_bg" />
    <div id="card_img" class=""></div>
    <div id="card_frame" class="canEditableImg"></div>
    <div id="card_to" class="canEditable"><?php if($node_info['to']!=''):?><?=$node_info['to']?><?php else:?><?php endif;?></div>
    <div id="card_from" class="canEditable"><?php if($node_info['from']!=''):?><?=$node_info['from']?><?php else:?><?php endif;?></div>
    <div id="card_content" class="canEditable"><?=$node_info['content'] ?></div>
  </div>
<script>
  var origin_card_bg_width = 320;
  var origin_card_content = {
    width:<?=$node_info['content_style']['width']?>,
    height:<?=$node_info['content_style']['height']?>,
    top:<?=$node_info['content_style']['top']?>,
    left:<?=$node_info['content_style']['left']?>
  };
  var ratio = 1;

function getRatio(){
  var fact_card_bg_width = parseFloat($('.card_bg').css('width'));
  ratio = origin_card_bg_width/fact_card_bg_width;
}
function resize_content(){
  var width = Math.round(origin_card_content.width/ratio);
  var height = Math.round(origin_card_content.height/ratio);
  var top = Math.round(origin_card_content.top/ratio);
  var left = Math.round(origin_card_content.left/ratio);
  $('#card_content').css({width:width,height:height,top:top,left:left});
}

getRatio();
resize_content();

$(window).resize(function(){
  getRatio();
  resize_content();  
});

<?php if($node_info['has_from']):?>
var origin_card_from = {
    width:<?=$node_info['from_style']['width']?>,
    height:<?=$node_info['from_style']['height']?>,
    top:<?=$node_info['from_style']['top']?>,
    left:<?=$node_info['from_style']['left']?>
  }
function resize_from(){
  var width = Math.round(origin_card_from.width/ratio);
  var height = Math.round(origin_card_from.height/ratio);
  var top = Math.round(origin_card_from.top/ratio);
  var left = Math.round(origin_card_from.left/ratio);
  $('#card_from').css({width:width,height:height,top:top,left:left}); 
}
resize_from();
$(window).resize(resize_from);
<?php endif;?>

<?php if($node_info['has_to']):?>
var origin_card_to = {
    width:<?=$node_info['to_style']['width']?>,
    height:<?=$node_info['to_style']['height']?>,
    top:<?=$node_info['to_style']['top']?>,
    left:<?=$node_info['to_style']['left']?>
  }
function resize_to(){
  var width = Math.round(origin_card_to.width/ratio);
  var height = Math.round(origin_card_to.height/ratio);
  var top = Math.round(origin_card_to.top/ratio);
  var left = Math.round(origin_card_to.left/ratio);
  $('#card_to').css({width:width,height:height,top:top,left:left}); 
}
resize_to();
$(window).resize(resize_to);
<?php endif;?>

<?php if($node_info['has_img']):?>
var origin_card_img = {
    width:<?=$node_info['img_style']['width']?>,
    height:<?=$node_info['img_style']['height']?>,
    top:<?=$node_info['img_style']['top']?>,
    left:<?=$node_info['img_style']['left']?>
  }
function resize_img(){
  var width = Math.round(origin_card_img.width/ratio);
  var height = Math.round(origin_card_img.height/ratio);
  var top = Math.round(origin_card_img.top/ratio);
  var left = Math.round(origin_card_img.left/ratio);
  $('#card_img').css({width:width,height:height,top:top,left:left}); 
}
resize_img();
$(window).resize(resize_img);
<?php endif;?>

<?php if($node_info['has_frame']):?>
var origin_card_frame = {
    width:<?=$node_info['frame_style']['width']?>,
    height:<?=$node_info['frame_style']['height']?>,
    top:<?=$node_info['frame_style']['top']?>,
    left:<?=$node_info['frame_style']['left']?>
  }
function resize_frame(){
  var width = Math.round(origin_card_frame.width/ratio);
  var height = Math.round(origin_card_frame.height/ratio);
  var top = Math.round(origin_card_frame.top/ratio);
  var left = Math.round(origin_card_frame.left/ratio);
  $('#card_frame').css({width:width,height:height,top:top,left:left}); 
}
resize_frame();
$(window).resize(resize_frame);
<?php endif;?>


</script>

<?php elseif ($ntype=='music'):?>
  <div id="music">
    <img src="<?=$node_info['bg_url']?>" class="bg" />
    <div id="music_infos">
      <ul>
        <li id="music_img">
          <img src="<?=$node_info['icon_url']?>" alt="">
        </li>
        <li id="music_info">
          <p id="music_name"><?=$node_info['title']?></p>
          <p ><?=$node_info['singer_name']?></p>
        </li>
        <li id="music_btn" data-source="<?=$node_info['music_url']?>">
          <button id="play" class="play_icon play"></button>
        <!--  <img src="/themes/mobiles/img/music_btn.png" alt="分享按钮"> -->
        </li>
      </ul>
    </div>
    <audio id="media" width="0px" height="0px">你的手机不支持音乐播放</audio>
  </div>
<script type="text/javascript">
  //添加播放控制事件
  var player =  document.getElementById("media");
  var canPlayType   = function( file ){
    return !!( player.canPlayType && player.canPlayType( 'audio/' + file.split( '.' ).pop().toLowerCase() + ';' ).replace( /no/, '' ) );
  };
  player.addEventListener('loadeddata',function(){
    loading('stop');
    player.play();
  });
  player.addEventListener('play',function(){
    $('.play_icon').removeClass('play').addClass('hault');
  });
  player.addEventListener('ended',function(){
    $('.play_icon').removeClass('hault').addClass('play');
  });

  $(function(){
    // 设置底部#share的宽度等于包含它的div的宽度
    var width=$('#main').width();
     $('#share').css('width',width);
     //当浏览器大小改变时,#share 的宽度跟随改变.
    window.onresize=function(){
      var width=$('#main').width();
      
      $('#share').css('width',width);
    }

    //播放
    $('.play_icon').click(function(e){
      e.stopPropagation();
      var isPlaying = !player.paused;
      if(isPlaying){
        player.pause();
        $(this).toggleClass('hault play');
      }else if(!isPlaying){
        var src = $(this).parent('#music_btn').attr('data-source');
        if(src && src.substring(src.lastIndexOf(".")+1,src.length) == "mp3" ){
          player.src = src;
          //加载中
          loading('start');
          player.load();
        }else{
          alert('不支持该音乐格式');
        };
      }
    });
   
  })
</script>  
<?php elseif ($ntype=='word'):?>
  <div class="word_bg">
    <div id="word" >
      <p id="word_header"></p>
      <p id="word_txt"><?=$node_info['content']?></p>
      <p id="word_footer"></p>
    </div>
  </div>
<?php if(empty($node_info['nid'])):?>
<script>$(function(){
  var $btn = $('#share_btn');
  $btn.html('&nbsp;&nbsp;&nbsp;送出我的感恩');
  if (isSubscribe) {
	  $btn.attr('href','http://'+location.hostname+'/activity/detail/17');
  }
});
</script>
<?php endif;?>  

<?php elseif($ntype=='gift'):?>
  <div id="gift">
    <span id="gift_img">
      <img src="<?=$node_info['goods_url']?>" alt="">
    </span>
    <span id="gift_btn">
      <button id="gift_open" class="gift_open" onclick="detail()"></button>
    </span>
  </div>
<script type="text/javascript">
  function detail () {
    $('#gift_open').addClass('gift_detail');
    $('#gift_img').css('display','block');
    $('.gift_detail').bind('click', function(){
        var base = 'http://'+location.hostname;
        location.href=base+'/#/mall/detail/<?=$node_info['nid']?>';
    });
  }
  </script>
<?php endif;?>

</div>
<div class="cover loading"></div>
<nav id="share" >
  <a id="share_btn" href="http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTUwNw==&mid=200959624&idx=1&sn=f673a77cac15cca627fdc1c82a86c2fa">&nbsp;&nbsp;&nbsp;送出我的祝福</a>
</nav>

</div>
<script type="text/javascript">
function loading(act){
  if(act=='start'){
    $('.cover').show();
  }else if(act=='stop'){
    $('.cover').hide();
  }
}
$(function(){
	$('#share').bind('touchmove',function(e){e.preventDefault();});
});
</script>
<?php tplholder('FOOT_JS');?>
<script>var FST=new Object();FST.autostart=0;</script>
<script type="text/javascript" src="<?=$contextpath;?>misc/js/fst.min.js"></script>
</body>
</html><?php
add_css('c.min.css',['scope'=>'global','ver'=>1]);
add_css('m.css',['scope'=>'global']);
add_css('pc.css',['scope'=>'global','media'=>'only screen and (min-width: 1025px)']);
add_css('share.css',['scope'=>'global']);
//add js file
add_js('ext/jquery-2.1.1.min.js',['pos'=>'head','ver'=>'none']);
?>