<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<?php if(0 == $user->level):?>
<div class="partner_ad">
	<span class="ad_l">你还未加入米商计划</span>
	<a href='/riceplan'><span class="ad_r">了解详情</span></a>
</div>
<?php endif;?>
<div class="my_guest">
	<div class="guest_tit">
		我的人脉（单位：人）
	</div>
	<div class="guest_main">
		<ul>
				<li onclick="showLevelList('/partner/list',1);">
					<h1 id="LevelCount1">0</h1>
					<p>一层</p>
				</li>
				<li onclick="showLevelList('/partner/list',2);">
					<h1 id="LevelCount2">0</h1>
					<p>二层</p>
				</li>
				<li onclick="showLevelList('/partner/list',3);">
					<h1 id="LevelCount3">0</h1>
					<p>三层</p>
				</li>
		</ul>
	</div>
	<div class="guest_prompt">
		提示：获取人脉请点击上方数字
	</div>
</div>

<div class="partner_money">
	<div class="money_main">
		<ul>
			<li>
			<a href="<?php echo U('cash/apply')?>" class="blka" onclick="return cash_available(this)">
				<p>可提现金额（元）</p>
				<h1 id="activeIncome">￥0</h1>
			</a>
			</li>
			<li>
			<a href="<?php echo U('partner/commission','status=0')?>" class="blka">
				<p>未生效收入</p>
				<h1 class="money_noforce" id="inactiveIncome">￥0</h1>
			</a>
			</li>
		</ul>
		<div class="money_main_x"></div>
	</div>
	<div class="money_main">
		<ul>
			<li>
			<a href="<?php echo U('cash/detail')?>" class="blka" onclick="return cash_already(this)">
				<p>已提现金额</p>
				<h1 id="cashedIncome">￥0</h1>
			</a>
			</li>
			<li>
			<a href="<?php echo U('partner/commission','status=1')?>" class="blka">
				<p>总收入（元）</p>
				<h1 id="totalIncome">￥0</h1>
			</a>
			</li>
		</ul>
		<div class="money_main_x"></div>
	</div>
</div>

<div class="guest_list">
	<ul>
		<li>
			<a href="/item/promote" class="blka">
				<h1><img src="/themes/mobiles/img/tui.png"></h1>
				<p>我要推广</p>
			</a>
		</li>
		<li>
			<a href="<?php echo U('cash/apply')?>" class="blka" onclick="return cash_apply(this)">
				<h1><img src="/themes/mobiles/img/shen.png"></h1>
				<p>申请提现</p>
			</a>
		</li>
		<li>
			<a href="<?php echo U('cash/detail')?>" class="blka" onclick="return cash_detail(this)">
				<h1><img src="/themes/mobiles/img/ming.png"></h1>
				<p>提现明细</p>
			</a>
		</li>
		<li>
			<a href="<?php echo U('partner/commission','status=1')?>" class="blka">
				<h1><img src="/themes/mobiles/img/icon_jjm.png"></h1>
				<p class="noborder">佣金明细</p>
			</a>
		</li>
	</ul>
</div>

<script>
	$().ready(function(){
		var url = '/partner/ajax';
		F.get(url, {}, function(ret){
			if(ret){
				$("#LevelCount1").text(ret.firstLevelCount);
				$("#LevelCount2").text(ret.secondLevelCount);
				$("#LevelCount3").text(ret.thirdLevelCount);
				$("#activeIncome").html("￥"+ret.activeIncome);
				$("#cashedIncome").html("￥"+ret.cashedIncome);
				$("#inactiveIncome").html("￥"+ret.inactiveIncome);
				$("#totalIncome").html("￥"+ret.totalIncome);
			}  
		});
	});
	function showLevelList(url, level){
		var count = $("#LevelCount"+level).text();
		if(!count || isNaN(count)){
			count = 0;
		}
		window.location = url+"?level="+level+"&count="+count;
	}
	function cash_available(obj) {
		if (gUser.is_debug_user) return true;
		else {
			myAlert('暂无可提现金额<br><span style="font-size:12px">（提现功能内测中，将于近期正式开放，敬请期待）</span>');
			return false;
		}
	}
	function cash_already(obj) {
		if (gUser.is_debug_user) return true;
		else {
			myAlert('暂无提现明细<br><span style="font-size:12px">（提现功能内测中，将于近期正式开放，敬请期待）</span>');
			return false;
		}
	}
	function cash_total(obj) {
		if (gUser.is_debug_user) return true;
		else {
			myAlert('尚未有有效收入<br><span style="font-size:12px">（提现功能内测中，将于近期正式开放，敬请期待）</span>');
			return false;
		}
	}
	function cash_apply(obj) {
		if (gUser.is_debug_user) return true;
		else {
			myAlert('暂无可提现金额<br><span style="font-size:12px">（提现功能内测中，将于近期正式开放，敬请期待）</span>');
			return false;
		}
	}
	function cash_detail(obj) {
		if (gUser.is_debug_user) return true;
		else {
			myAlert('暂无提现明细<br><span style="font-size:12px">（提现功能内测中，将于近期正式开放，敬请期待）</span>');
			return false;
		}
	}
</script>
<?php endif;?>