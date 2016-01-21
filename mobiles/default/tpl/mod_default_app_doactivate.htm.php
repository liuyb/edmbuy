<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<style>
.state_tit_imt{
	text-align:center;
	margin:25px 0 15px;
}
.state_tit_imt img{
	vertical-align: middle;
	width:100px;
	height:100px;
	border-radius:50%;
}
.state_tit_neme{
	text-align:center;
	font-size:18px;
	color:#000;
}
.stat_bottom_wx{
	text-align:center;
  text-align: center;
	margin-bottom:20px;
}
.stat_bottom_wx img{
	width:172px;
	height:172px;
}
.state_one{
	margin-top:9px;
	text-align:center;
}
.state_two{
	/*display:none;*/
	margin-top:9px;
	text-align:center;
}
.state_three{	
	/*display:none;*/
	margin-top:30px;
	width:100%;
}
.satate_comment_phone{
	font-size:15px;
	color:#333;
}
.state_comment_ts{
	font-size:17px;
	color:#E54040;
	font-weight:bold;
}
.state_comment_info{
	font-size:15px;
	color:#333;
	margin-bottom:5px
}
td.state_comment_info {
  text-align: right;
}
.state_comment_info1{
	margin:10px 0 30px;
	font-size:12px;
	color:#333;
}
.three_tab{
	margin:0 5% ;	
	width:90%;
}
.three_tab1 {
  margin: 0;
  width: 100%;
}
.three_tab td{
	padding-bottom:10px;
}
.three_weight{
	font-weight:bold;
	font-size: 15px;
	color:#333;
}
td.three_weight {text-align: left;padding-left: 5px;}
</style>

<div class="state_tit_imt"><img src="<?=$user->logo?>" alt=""></div>
<div class="state_tit_neme"><?=$user->nickname?></div>

<?php if(2==$situation):?>
<div class="state_one">
	<table cellspacing="0" cellpadding="0" class="three_tab three_tab1">	
		<tr>	
			<td class="state_comment_info">多米号：</td>
			<td class="three_weight"><?=$user->uid?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">关联手机号：</td>
			<td class="three_weight"><?=$user->mobilephone?></td>
		</tr>
		<tr>	
			<td style="margin:20px 0;" colspan="2" class="state_comment_ts">恭喜你，你的益多米账号已激活。</td>
		</tr>
		<?php if($appParent->is_exist()):?>
		<tr>	
			<td class="state_comment_info">你的甜玉米推荐人：</td>
			<td class="three_weight"><?=$appParent->nick?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">推荐人手机号：</td>
			<td class="three_weight"><?=$appParent->mobile?></td>
		</tr>
		<tr><td colspan="2" style="font-weight: bold">TA还未激活益多米账号，请邀请TA激活</td></tr>
		<?php endif;?>
	</table>
</div>
<?php endif;?>
<?php if(3==$situation || 4==$situation):?>
<div class="state_two">
	<p class="state_comment_ts" style="margin:30px 0;">你已经是益多米的会员，无需再激活。</p>
	<table cellspacing="0" cellpadding="0" class="three_tab">	
		<tr>	
			<td class="state_comment_info">多米号：</td>
			<td class="three_weight"><?=$user->uid?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">关联手机号：</td>
			<td class="three_weight"><?=$user->mobilephone?></td>
		</tr>
		<?php if($usrParent->is_exist()):?>
		<tr>	
			<td class="state_comment_info">益多米推荐人：</td>
			<td class="three_weight"><?=$usrParent->nickname?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">推荐人多米号：</td>
			<td class="three_weight"><?=$usrParent->uid?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">推荐人手机号：</td>
			<td class="three_weight"><?=$usrParent->mobilephone?></td>
		</tr>
		<?php endif;?>
	</table>
</div>
<?php endif;?>
<?php if(1==$situation):?>
<div class="state_three">
	<table cellspacing="0" cellpadding="0" class="three_tab">	
		<tr>	
			<td class="state_comment_info">多米号：</td>
			<td class="three_weight"><?=$user->uid?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">关联手机号：</td>
			<td class="three_weight"><?=$user->mobilephone?></td>
		</tr>
		<?php if($usrParent->is_exist()):?>
		<tr>	
			<td class="state_comment_info">益多米推荐人：</td>
			<td class="three_weight"><?=$usrParent->nickname?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">推荐人多米号：</td>
			<td class="three_weight"><?=$usrParent->uid?></td>
		</tr>
		<tr>	
			<td class="state_comment_info">推荐人手机号：</td>
			<td class="three_weight"><?=$usrParent->mobilephone?></td>
		</tr>
		<?php endif;?>
	</table>
	<p class="state_comment_ts" style="margin:30px 0;text-align:center;">恭喜你，你的益多米账号已激活。</p>
</div>
<?php endif;?>
<div class="stat_bottom_wx">
	<img src="/misc/images/qrcode/edmbuy_258.jpg">
	<p style="font-size:11px;color:#333;">长按二维码，关注益多米</p>
</div>

<script>
F.onWxReady(function(){
	wx.hideOptionMenu();
});
setTimeout(function(){
	window.location.href='<?=$refer?>';
},30000);
</script>

<?php endif;?>