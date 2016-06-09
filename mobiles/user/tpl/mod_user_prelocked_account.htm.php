<?php defined('IN_SIMPHP') or die('Access Denied');?>

<!--[HEAD_CSS]-->
<style>
.emptyrow { height: 40px;line-height: 40px;font-size: 13px;font-size:1.3rem;text-align: center;color: #999; }
.ctli { border-bottom: 1px solid #ddd;padding: 10px; }
.lirow { height: 38px;line-height: 38px; }
.lirow img { max-height: 100%;float: left;margin-right: 10px; }
.lirow span { height: 19px;line-height:19px }
.lirow .btn { margin-top: 4px; }
</style>
<!--[/HEAD_CSS]-->

<script id="forTopnav" type="text/html">
<div class="header">我的预锁定用户(<?=$totalnum?>)<a href="<?php echo backscript()?>" class="back"></a></div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="bod">
	<ul id="ulistbod">
<?php if(!empty($pending_list)):?>
	<?php foreach ($pending_list AS $it):?>
		<li class="ctli">
			<div class="lirow clearfix">
				<img src="<?php echo default_logo()?>" data-loaded="0" onload="imgLazyLoad(this,'<?=$it['logo']?>')" alt=""/>
				<span class="fl"><?=$it['nick']?><br/><?=$it['touch_time']?></span>
				<?php if($it['is_reg']):?>
				<a href="javascript:;" class="btn btn-green fr" data-isreg="<?=$it['is_reg']?>" data-ta="<?php echo show_ta($it['gender'])?>">已注册</a>
				<?php else:?>
				<a href="javascript:;" class="btn btn-red fr" data-isreg="<?=$it['is_reg']?>" data-ta="<?php echo show_ta($it['gender'])?>">没注册</a>
				<?php endif;?>
			</div>
		</li>
	<?php endforeach;?>
<?php else:?>
		<li><div class="emptyrow">暂没有锁到的伙伴，赶紧去推广吧:-)</div></li>
<?php endif;?>
	</ul>
</div>
<?php include T('inc/paging');?>
<script>
function remind_reg(is_reg, ta) {
	is_reg = parseInt(is_reg);
	if (is_reg) {
		weui_alert(ta+'已注册，无需再提醒。');
	}
	else {
		weui_alert(ta+'还没注册，请尽快通知'+ta+'注册。');return false;
		weui_confirm('提醒'+ta+'注册能更快确定关系，但可能会打扰到对方。确定提醒?','',function(){
			weui_dialog_close();
			weui_alert('已提醒');
		});
	}
	return false;
}
$(function(){
	$('#ulistbod').on('click','a.btn',function(){
		var ta=$(this).attr('data-ta');
		var isreg=$(this).attr('data-isreg');
		remind_reg(isreg, ta);
	});
});
</script>