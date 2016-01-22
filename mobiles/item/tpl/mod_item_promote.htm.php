<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	去推广
<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="guest_list" style="border-bottom:0;background:#eee;">
	<ul>
		<a href="/riceplan">
			<li>
				<h1><img src="/themes/mobiles/img/tui.png"></h1>
				<p>米商计划</p>
			</li>
		</a>
		<a href="/comeon">
			<li>
				<h1><img src="/themes/mobiles/img/tewm.png"></h1>
				<p class="noborder">推广二维码</p>
			</li>
		</a>
		<!-- <a href="/item/promote/product">
			<li style="margin-top:20px;">
				<h1><img src="/themes/mobiles/img/tcp.png"></h1>
				<p class="noborder">推荐产品</p>
			</li>
		</a> -->
	</ul>
</div>
<?php endif;?>
