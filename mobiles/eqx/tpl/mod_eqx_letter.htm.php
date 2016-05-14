<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>
<!--[HEAD_CSS]-->
<style type="text/css">
.slinav {bottom: 60px;}
.slinav a { background: #999;border: 1px solid #999;opacity:.85; }
.slinav a.active { background: #fff;border: 1px solid #fff;opacity:1; }
</style>
<!--[/HEAD_CSS]-->
<div class="letter_bg">
  <div class="swipe">
    <ul id="slider" class="slider">
    	<li style="display:block">
    		<img src="<?=$contextpath?>mobiles/eqx/img/letter_1.png" alt=""/>
    		<div class="letter_rzbtn"><a href="javascript:;" class="blka" onclick="return __go_next();">一起享怎么玩？</a></div>
    	</li>
    	<li>
    		<img src="<?=$contextpath?>mobiles/eqx/img/letter_2.png" alt=""/>
    		<div class="letter_rzbtn"><a href="<?php echo U('eqx/reg')?>" class="blka">入驻一起享</a></div>
    	</li>
    </ul>
    <div id="slinav" class="slinav clearfix">
	    <a href="javascript:void(0);" class="active">1</a>
	    <a href="javascript:void(0);" class="">2</a>
    </div>
  </div>
</div>
<script type="text/javascript">
var t1;
$(function(){
	var _active = 0, $_ap = $('#slinav a');
  t1 = new TouchSlider({
     id:'slider',
     auto: false,
     speed:300,
     timeout:6000,
     before:function(newIndex, oldSlide){
         $_ap.get(_active).className = '';
         _active = newIndex;
         $_ap.get(_active).className = 'active';
     }
  });
  setTimeout(function(){t1.resize();},500);
});
function __go_next() {
	t1.slide(1);
	return false;
}
</script>