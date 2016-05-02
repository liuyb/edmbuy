<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<div class="agency_become">
	<table cellspacing="0" cellpadding="0" class="become_a_tab">	
		<tr>
			<td width="70px;"><img src="<?=$user->logo ?>"></td>
			<td>
				<p class="become_name"><?=$user->nickname ?><img src="<?=AgentPayment::getAgentIconByLevel($user->level) ?>" class="jin_z">
				</p>
				<p class="become_name_id">多米号：<?=$user->uid ?></p>
			</td>
		</tr>
	</table>
</div>

<div class="agency_index_infos">
	<?php if(isset($parent) && $parent):?>
	<a href="<?php echo U('distribution/my/parent')?>">
	<div class="in_common_agency">
		<img src="/themes/mobiles/img/tuijianr.png"><span>我的推荐人</span><i><?=$parent->nickname ?></i>
	</div>
	</a>
	<?php endif;?>
	<a href="<?php echo U('distribution/my/child/agent')?>">
	<div class="in_common_agency">
		<img src="/themes/mobiles/img/daili.png"><span>我发展的代理</span><i id="myChildAgency"><?=$agentTotal ?></i>
	</div>
	</a>
	<a href="<?php echo U('distribution/my/child/shop')?>">
	<div class="in_common_agency border_common">
		<img src="/themes/mobiles/img/dianp.png"><span>我发展的店铺</span><i id="myChildShop"><?=$shopTotal ?></i>
	</div>
	</a>
</div>

<div class="deve_index_agency">
	<a href='/distribution/spread'>
	<div class="in_common_agency border_common">
		<img src="/themes/mobiles/img/tuiguang.png"><span>推广素材</span>
	</div>
	</a>
</div>

<div class="agency_index_infos">
	<div class="teamword_use">合作应用</div>
	<div class="mem_my_applylist">
		<ul>
			<li><img src="/themes/mobiles/img/yiduomi.png"></li>
			<li><img src="/themes/mobiles/img/yiqix.png"></li>
			<li> </li>
			<li style="border-right:0;"> </li>
		</ul>
	</div>
</div>

<?php endif;?>