<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<!--[HEAD_CSS]-->
<style>
.wallet_top{
	height:100px;
	background:#ff6d14;
	text-align:center;
}
.wallet_infos{
	font-size:12px;
	color:#FFF;
	padding:18px 0 15px;
}
.wallet_money{
	font-size:30px;
	color:#fff;
  	margin-top: 15px;
  	line-height: 0;	
}
.income_money{
	height:60px;
	position:relative;
	background:#F5650c;
}
.income_money li{
	float:left;
	width:50%;
}
.line7{
  	position: absolute;
  	height: 40px;
  	border-right: 1px solid #ff9452;
  	top: 12px;
  	right: 50%;	
}
.income_money img{
	position:absolute;	
	top:0;
	width:13px;
	height:8px;
}
.income_money p{
	line-height:1;
	color:#fff;
	font-size:12px;	
	padding:12px 0 8px 15px;	
}
.in_com_money{
	line-height:1;	
	font-size:20px !important;	
	color:#fff !important;	
	padding-top:0 !important;
}
.no_force_money{	
	font-size:20px !important;	
	color:#fff !important;	
	padding-top:0 !important;	
}
.wallet_type_list{
  	background: #fff;
  	position: relative;	
}
.wallet_type_list li {
  	border-bottom: 1px solid #e6e6e6;
  	width: 50%;
  	float: left;
	height:70px;
	line-height:1;
}
.line5{
  	position: absolute;
  	height: 50px;
  	border-right: 1px solid #e6e6e6;
  	top: 80px;
  	right: 50%;
}	
.line6{
  	position: absolute;
  	height: 50px;
  	border-right: 1px solid #e6e6e6;
  	top: 150px;
  	right: 50%;
}	
.line9{
  	position: absolute;
  	height: 50px;
  	border-right: 1px solid #e6e6e6;
  	top: 10px;
  	right: 50%;
}
.wallet_type_list p {
  	font-size: 12px;
  	color: #999;
	padding:15px 0 10px 15px;
}
.wallet_li_font{
  	color: #111 !important;
  	font-size: 20px !important;	
	padding-top:0 !important;
}
.new_guest_list{
	background:#fff;
	margin-top:10px;
	position:relative;
}
.new_guest_list li{
	height:70px;
	float:left;
	width:50%;
}
.new_r_wa{
	float:left;
}
.new_img_wal{
	float:left;	
	width:31px;
	height:32px;
	vertical-align:top;
	margin:19px 0 0 15px;
}
.new_img_war{
	float:left;	
	width:28px;
	height:30px;	
	vertical-align:top;
	margin:20px 0 0 15px;	
}
.line10{
  	position: absolute;
  	height: 50px;
  	border-right: 1px solid #e6e6e6;
  	top: 10px;
  	right: 50%;
}
</style>
<!--[/HEAD_CSS]-->

<script id="forTopnav" type="text/html">
<div class="header">
	我的钱包
<a href="<?php echo U("user")?>" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>
<a href="<?php echo U('cash/apply')?>">
<div class="wallet_top">
	<div class="wallet_infos">账户余额（元）</div>
	<div class="wallet_money"><?=$totalye ?></div>
</div>
</a>
<div class="income_money">
	<ul>
		<li onclick="showDetail(-1,-1);"><p>累计收入（元）</p><p class="in_com_money"><?=$totalIncome ?></p></li>
		<li onclick="showDetail(0,-1);"><p>未生效收入（元）</p><p class="no_force_money"><?=$commision['0'] ?></p></li>
		<img src="/themes/mobiles/img/san.png">
		<div class="clear"></div>
		<span class="line7"></span>
	</ul>
</div>

<div class="wallet_type_list">
	<ul>
		<li onclick="showDetail(2,-1);"><p>已提现</p><p class="wallet_li_font"><?=$commision['2'] ?></p></li>
		<li onclick="showDetail(9,-1, 1);"><p>返利收入</p><p class="wallet_li_font"><?=$rebate_commision ?></p></li>
		<li onclick="showDetail(9,0);"><p>商品分销收入</p><p class="wallet_li_font"><?=$type_commision['0'] ?></p></li>
		<li onclick="showDetail(9,1);"><p>代理收入</p><p class="wallet_li_font"><?=$type_commision['1'] ?></p></li>
		<li onclick="showDetail(9,2);"><p>商家入驻收入</p><p class="wallet_li_font"><?=$type_commision['2'] ?></p></li>
		<li onclick="showDetail(9,3);"><p>商家交易收入</p><p class="wallet_li_font"><?=$type_commision['3'] ?></p></li>
		<div class="clear"></div>
		<span class="line10"></span>
		<span class="line5"></span>
		<span class="line6"></span>
	</ul>
</div>

<div class="new_guest_list">
	<ul>
		<a href="<?php echo U('cash/apply')?>">
		<li>
			<img src="/themes/mobiles/img/sqtx1.png" class="new_img_wal">
			<div class="new_r_wa">
				<p style="font-size:17px;color:#111;line-height:1;margin:18px 0 5px 10px;">申请提现</p>
				<p style="font-size:12px;color:#999;line-height:1;margin:0 0 5px 10px;">微信，银联提现</p>
			</div>
		</li>
		</a>
		<a href="<?php echo U('cash/detail')?>">
		<li>
			<img src="/themes/mobiles/img/txmx1.png" class="new_img_war">
			<div class="new_r_wa">
				<p style="font-size:17px;color:#111;line-height:1;margin:18px 0 5px 10px;">提现明细</p>
				<p style="font-size:12px;color:#999;line-height:1;margin:0 0 5px 10px;">账单一目了然</p>
			</div>
		</li>
		</a>
		<div class="clear"></div>
		<span class="line10"></span>
	</ul>
</div>
<script>
$(function(){
	var _width = ($(document).width() - 15) / 2;

	$(".income_money img").css("left",_width)
});

var _detail = "<?=U('user/income/detail?1=1')?>";
function showDetail(state,type,rebate){
	window.location.href=_detail+'&type='+type+'&state='+state+'&rebate='+rebate;
}
</script>
<?php endif;?>