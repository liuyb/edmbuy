<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<div class="agency_infos_pay">
	<img src="/themes/mobiles/img/icon.png">
	<p style="font-size:18px;color:#323232;margin-top:30px;">订单支付成功</p>
	<p style="font-size:14px;color:#323232;">付款金额：<i style="color:#f65d00;">￥<?=AgentPayment::getAgentPaidMoney($order_type, 2) ?></i></p>
	<p style="font-size:16px;color:#323232;margin-top:30px;">恭喜你，已成为<?=AgentPayment::getAgentNameByLevel($order_type) ?>牌代理！</p>
	<div class="at_get_combo" onclick="window.location.href='/distribution/agent/package';">立即领取<?=AgentPayment::getAgentPaidMoney($order_type) ?>套餐</div>
	<div class="agency_my_develop">我要推广</div>
</div>

<?php endif;?>