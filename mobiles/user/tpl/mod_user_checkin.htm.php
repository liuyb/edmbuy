<?php defined('IN_SIMPHP') or die('Access Denied');?>
	<div id="gains_top">
		<img src="/themes/mobiles/img/tp_02.png">
	</div>

	<div id="gains_list">
	<ul>
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
	</ul>
	<button id="gains_enter">我要入驻</button>
		<script>
				$("#gains_enter").click(function(){
					window.location.href="/user/merchart/payment";
				})
		</script>
