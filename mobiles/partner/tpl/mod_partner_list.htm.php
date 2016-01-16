<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	<?=$title ?>
<a href="/partner" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<?php foreach ($list as $item):?>
<div class="copartner_list">
	<table cellspacing="0" cellpadding="0" class="copartner_list_table">
		<tr>
			<td class="list_td1">
			<?php if($item['logo']):?>
				<img src="<?=$item['logo'] ?>" class="list_img1">
			<?php else: ?>
				<img src="/themes/mobiles/img/mt.png" class="list_img1">
			<?php endif;?>
			</td>
			<td class="list_td2">	
				<?php 
				    $bg = ($item['level'] == 1) ? "user_bg_sha" : (($item['level'] == 2) ? "user_bg_he" : "user_bg_ke");
				?>
				<span class="list_nickname <?=$bg ?>" ><?=$item['nickname'] ?></span>
				<span class="list_address"><?=$item['province'] ?> <?=$item['city'] ?></span>
				<span class="list_number">
					推荐人数：<span style="color:#ff6d14;margin-right:5px;"><?=$item['childnum1'] ?></span>
					<?php if($item['parentnick']):?>
					上级：<span style="color:#ff6d14;"><?=$item['parentnick'] ?></span>
					<?php endif;?>
				</span>
			</td>
			<td class="list_td3">
				<button class="td3_btn" onclick="getAddFriendInstance().showFriend('<?=$item['wxqr'] ?>','<?=$item['mobilephone'] ?>');">加好友</button>
			</td>
		</tr>
	</table>
</div>
<?php endforeach;?>
<?php include T('inc/pager');?>

<?php include T('inc/add_as_friend');?>

<?php endif;?>
