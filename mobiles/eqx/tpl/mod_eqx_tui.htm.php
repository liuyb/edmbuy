<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css('eqx.css',['scope'=>'module', 'mod'=>'eqx']);?>

<script type="text/html" id="forTopNav">
<div class="header">去推广<a href="javascript:history.back();" class="back">返回</a></div>
</script>
<script type="text/javascript">show_topnav($('#forTopNav').html());</script>

<div class="develop_tw">二维码推广</div>

<div class="weix_w">
	<a class="weix_code blka" href="<?php echo U('eqx/tuiqr')?>">
		<img src="<?=$contextpath?>mobiles/eqx/img/erwm.png">
		<div class="weix_right">
			<p class="r_top">一起共享人脉和项目，就在一起享</p>
			<p class="r_bottom">你的专属二维码推广页面</p>
		</div>
		<div class="clear"></div>
	</a>
</div>

<div class="develop_tw">图文链接推广</div>

<div class="weix_w">
	<a class="weix_code blka" href="<?php echo U('eqx/intro')?>">
		<img src="<?=$contextpath?>mobiles/eqx/img/yqx1.png">
		<div class="weix_right">
			<p class="r_top">一起共享人脉和项目，就在一起享</p>
			<p class="r_bottom">你的专属二维码推广页面</p>
		</div>
		<div class="clear"></div>
	</a>
</div>

<div class="develop_btn"><div class="login_btn">一起享首页</div></div>

<div class="develop_bottom">
	已有<i class="enter_nums_deve">4658</i>位小伙伴已经入驻一起享，你还在犹豫什么呢？
</div>