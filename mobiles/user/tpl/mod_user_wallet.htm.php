<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	我的钱包
<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div class="wallet_top">
	<div class="wallet_infos"><span class="ye_account">账户余额</span><span class="ye_infos" onclick="showDetail();">明细</span></div>
	<div class="wallet_money">￥<?=$commision['1'] ?></div>
</div>

<div class="income_money">
	<ul>
		<li><p>累计收入</p><p class="in_com_money">￥<?=$commision['1'] ?></p></li>
		<li><p>未生效收入</p><p class="no_force_money">￥<?=$commision['0'] ?></p></li>
		<div class="clear"></div>
		<span class="line7"></span>
	</ul>
</div>

<div class="wallet_type_list">
	<ul>
		<li><p>已提现</p><p class="wallet_li_font">￥<?=$commision['2'] ?></p></li>
		<li><p>返利收入</p><p class="wallet_li_font">￥<?=$rebate_commision ?></p></li>
		<li><p>商品分销收入</p><p class="wallet_li_font">￥<?=$type_commision['0'] ?></p></li>
		<li><p>代理收入</p><p class="wallet_li_font">￥<?=$type_commision['1'] ?></p></li>
		<li><p>商家入驻收入</p><p class="wallet_li_font">￥<?=$type_commision['2'] ?></p></li>
		<li><p>商家交易收入</p><p class="wallet_li_font">￥<?=$type_commision['3'] ?></p></li>
		<div class="clear"></div>
		<span class="line9"></span>
		<span class="line10"></span>
		<span class="line5"></span>
		<span class="line6"></span>
	</ul>
</div>

<div class="guest_list">
	<ul>
		<a href='/distribution/spread'>
			<li>
				<h1><img src="/themes/mobiles/img/tgsc.png"></h1>
				<p>推广素材</p>
			</li>
		</a>
		<a href="add_backID.html">
			<li>
				<h1><img src="/themes/mobiles/img/sqtx.png"></h1>
				<p>申请提现</p>
			</li>
		</a>
		<a href="cash_list_info.html">
			<li>
				<h1><img src="/themes/mobiles/img/txmx.png"></h1>
				<p  class="del_bottom">提现明细</p>
			</li>
		</a>
	</ul>
</div>
<script>
function showDetail(){
	window.location.href='/user/income/detail';
}
</script>
<?php endif;?>