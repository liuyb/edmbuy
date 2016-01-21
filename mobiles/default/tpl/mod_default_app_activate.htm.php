<?php defined('IN_SIMPHP') or die('Access Denied');?>
<style>
.relation_tit{
	font-size:17px;
	color:#333;
	text-align:center;
	padding:24px 0;
}
.relation_cen{
	background:#f0f3f5;
	padding:35px 35px 27px;
}
.relation_cen img{
	max-width:100%;
}
.relation_phone{
	background:#fff;
	font-size:17px;
	color:#333;
	text-align:center;
	line-height:48px;
	border-radius:4px;
	margin-top:35px;
}
.relation_bot{
	text-align:center;
	margin-top:27px;
}
.relation_bot p{
	font-size:15px;
	color:#333;
	line-height:23px;
}
.relation_btn{
	background:#e54040;
	height:40px;
	font-size:17px;
	color:#fff7f7;
	font-weight:bold;
	margin:15px;
	border-radius:4px;
	text-align:center;
	line-height:40px; 
	cursor:pointer;
	display: block;
}
.relation_t {
  border-radius: 4px;
  height: 125px;
  width: 250px;
  position: fixed;
  top: 50%;
  left: 50%;
  margin-left: -125px;
  margin-top: -79px;
  background: #fff;
  z-index: 70000;
  display: none;
}
.goods_btn {
  border-top: 1px solid #ddd;
  text-align: center;
}
.order_comm {
  float: right;
  height: 44px;
  width: 50%;
  font-size: 17px;
  border: 0;
  color: #ff6d14;
  cursor: pointer;
  background: #fff;
  border-left: 1px solid #ddd;
  font-weight:normal !important;
}
.relat_t_tit{
	font-size:16px;
	color:#000;
}
.btn_No {
  color: #999 !important;
  border-bottom-left-radius: 4px;
}
.btn_Yes {
  border-bottom-right-radius: 4px;
  color: #e54040 !important;
}
</style>

<div class="relation_tit">
	<p><span style="color:#ff6d14;font-weight: bold;">益多米</span>是最有温度的社交电商</p><p>平台， 正在免费激活益多米的账号。</p>
</div>

<div class="relation_cen">
	<img src="/themes/mobiles/img/img1.png" >
	<div class="relation_phone">
		当前电话号码：<span class="phone_tit"><?=$appUser->mobile?></span>
	</div>
	<div class="relation_bot">
		<p>请确认本手机号码和微信号都属于</p>
		<p>本人，激活后不可以更改</p>
	</div>
</div>

<a class="relation_btn" href="javascript:;">我要激活</a>
<div style="text-align:center;">
	<p style="font-size:12px;color:#616161;"><span style="color:#ff6d14;">提示：</span>如果帮助伙伴激活，请使用伙伴的</p>
	<p style="font-size:12px;color:#616161;">微信，登录甜玉米后激活。</p>
</div>


<!-- 遮罩层 -->
<a href="javascript:;"><div class="mask"></div></a>

<div class="relation_t">
	<div style="text-align:center;margin:17px 0;">
		<p class="relat_t_tit">确认本手机号和微信号</p>
		<p class="relat_t_tit">属于本人，激活后不可以更改。</p>
	</div>
	<div class="goods_btn">
		<button type="button" class="order_comm btn_Yes">激活</button>
		<button type="button" class="order_comm btn_No">取消</button>
	</div>
</div>

<script>
var curmobile = '<?=$appUser->mobile?>';
$(function(){
	$(".relation_btn").bind("click",function(){
		if (!curmobile) {
			myAlert('请从甜玉米登录过来激活');
			return false;
		}
		$(".mask").show();
		$(".relation_t").show();
	})
	$('.btn_Yes').bind('click',function(){
		if (curmobile) {
			window.location.href = '/app_doactivate?cid=<?=$cid?>&refer=<?=$refer?>';
		}
	});
	$('.btn_No').bind('click',function(){
		$(".mask").hide();
		$(".relation_t").hide();
	});
});
F.onWxReady(function(){
	wx.hideOptionMenu();
});
</script>
