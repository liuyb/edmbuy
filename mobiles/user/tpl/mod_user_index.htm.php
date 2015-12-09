<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="uc-logo">
  <img src="<?php echo Fn::default_logo();?>" onload="imgLazyLoad(this,'<?=$userInfo['logo']?>')" alt="头像" /><br/>
  <?=$userInfo['nickname']?>
</div>
<ul class="uc-funlist">
  <li class="funit"><a class="funbtn" href="<?php echo U('user/collect')?>"><i>⭐️</i><span>我的收藏</span></a></li>
  <li class="funit"><a class="funbtn" href="<?php echo U('trade/order/record','showwxpaytitle=1')?>"><i>🕒</i><span>购买记录</span></a></li>
  <li class="funit"><a class="funbtn" href="<?php echo U('user/feedback')?>"><i>📧</i><span>问题反馈</span></a></li>
  <li class="funit last"><a class="funbtn" href="http://mp.weixin.qq.com/s?__biz=MzAwNjQyNzA2NA==&mid=205641974&idx=1&sn=d21c0b265b021ce6e6f9b693551d83b1#rd" target="_blank"><i>💌</i><span>关于小蜜</span></a></li>
</ul>
<?php include T($tpl_footer);?>
<script>
$(function(){
	$('.uc-funlist .funit').bind('click',function(){
		$(this).prev().addClass('nobb');
		$(this).addClass('clickbg');
		return true;
	});
});
</script>