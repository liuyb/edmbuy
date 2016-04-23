<?php defined('IN_SIMPHP') or die('Access Denied');?>

<!--[HEAD_CSS]-->
<style type="text/css">
.myqr { padding: 10px;text-align: center; }
.myqr h1 { font-size:16px;font-size:1.6rem;margin-top: 10px; }
.myqr strong { font-weight: bold; }
.myqr .qrp { margin-top: 10px; }
.myqr .qrp span { color: #999; }
.myqr img { max-width: 100%; }
.myqr img.img1 { margin-bottom: 10px; }
</style>
<!--[/HEAD_CSS]-->

<div class="myqr">
	<h1><strong><?=$user->nickname?>(<?=$user->uid?>)</strong>，您好！</h1>
	
	<p>以下是您的益多米推广二维码，<br/>均带有您的个人推广信息，可以长按保存。</p>
	
	<p class="qrp">
	<?php if ($user->wxqrimg):?>
	  <span>进公众号版本：</span><br/>
		<img src="<?=$user->wxqrimg?>" alt="QR Code" class="img1"/>
	<?php endif;?>
	  <span>链接版本(进商城首页)：</span>
		<img src="<?=$qrcode?>" alt="QR Code"/>
	</p>
</div>