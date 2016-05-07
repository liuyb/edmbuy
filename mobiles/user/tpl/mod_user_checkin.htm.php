<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="gains_top">
	<img src="/themes/mobiles/img/tp_02.png">
	<img src="/themes/mobiles/img/_02.png">
	<img src="/themes/mobiles/img/_03.png">
	<img src="/themes/mobiles/img/_04.png">
	<img src="/themes/mobiles/img/_05.png">
	<img src="/themes/mobiles/img/_06.png">
	<img src="/themes/mobiles/img/_07.png">
	<img src="/themes/mobiles/img/_08.png">
	<img src="/themes/mobiles/img/_09.png">
	<img src="/themes/mobiles/img/_10.png">
</div>
<script id="forMnav" type="text/html">
<div id="gains_list">
<!-- <ul>
	<li>
		<img src="/themes/mobiles/img/t1.png">
		<span>什么事益多米分销</span>
	</li>
	<li>
		<img src="/themes/mobiles/img/t2.png">
		<span>分销资格与奖励制度</span>
	</li>
	<li>
		<img src="/themes/mobiles/img/t3.png">
		<span>商家入驻流程</span>
	</li>
</ul> -->

<button id="gains_enter" onclick="window.location.href='/user/merchant/payment';">我要入驻</button>
</div>
</script>
<script>
$(function(){
	show_mnav($('#forMnav').html());
	$("#Mbody").css("background","#fff");
});
</script>
