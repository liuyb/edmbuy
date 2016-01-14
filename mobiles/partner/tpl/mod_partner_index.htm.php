<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<?php if(0 == $userLevel):?>
<div class="partner_ad">
	<span class="ad_l">你还未加入米商计划</span>
	<a href='/riceplan'><span class="ad_r">了解详情</span></a>
</div>
<?php endif;?>
<div class="my_guest">
	<div class="guest_tit">
		我的米客（单位：人）
	</div>
	<div class="guest_main">
		<ul>
			<a href="/partner/list/1">
				<li>
					<h1 id="firstLevelCount">...</h1>
					<p>一层</p>
				</li>
			</a>
			<a href="/partner/list/2">
				<li>
					<h1 id="secondLevelCount">...</h1>
					<p>二层</p>
				</li>
			</a>
			<a href="/partner/list/3">
				<li>
					<h1 id="thirdLevelCount">...</h1>
					<p>三层</p>
				</li>
			</a>
		</ul>
	</div>
	<div class="guest_prompt">
		提示：获取人脉请点击上方数字
	</div>
</div>

<div class="partner_money">
	<div class="money_main">
		<ul>
			<a href="add_backID.html">
			<li>
				<p>可提现金额（元）</p>
				<h1>￥0.00</h1>
			</li>
			</a>
			<a href="brokerage.html?broke=2">
			<li>
				<p>未生效收入</p>
				<h1 class="money_noforce" id="inactiveIncome">￥0.00</h1>
			</li>
			</a>
		</ul>
		<div class="money_main_x"></div>
	</div>
	<div class="money_main">
		<ul>
			<a href="brokerage.html?broke=1">
			<li>
				<p>总收入（元）</p>
				<h1>￥0.00</h1>
			</li>
			</a>
			<a href="cash_list_info.html">
			<li>
				<p>已提现金额</p>
				<h1>￥0.00</h1>
			</li>
			</a>
		</ul>
		<div class="money_main_x"></div>
	</div>
</div>

<div class="guest_list">
	<ul>
		<li>
			<a href="/item/promote">
				<h1><img src="/themes/mobiles/img/tui.png"></h1>
				<p>我要推广</p>
			</a>
		</li>
		<li>
			<a href="add_backID.html">
				<h1><img src="/themes/mobiles/img/shen.png"></h1>
				<p>申请提现</p>
			</a>
		</li>
		<li>
			<a href="cash_list_info.html">
				<h1><img src="/themes/mobiles/img/ming.png"></h1>
				<p>提现明细</p>
			</a>
		</li>
		<li>
			<a href="brokerage.html?broke=1">
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
				$("#firstLevelCount").text(ret.firstLevelCount);
				$("#secondLevelCount").text(ret.secondLevelCount);
				$("#thirdLevelCount").text(ret.thirdLevelCount);
				$("#inactiveIncome").html("￥"+ret.inactiveIncome);
			}
		});
	});
</script>
<?php endif;?>