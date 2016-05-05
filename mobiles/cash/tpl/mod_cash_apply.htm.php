<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script type="text/html" id="forTopnav">
<div class="header">申请提现<a href="javascript:history.back();" class="back"></a></div>
</script>
<script type="text/javascript">show_topnav($('#forTopnav').html())</script>

<?php if(!$is_ms):?>

<div class="apply_tx">
	<ul>
		<li><p class="apply_price"><?=$available_cash?>元</p><p style="font-size:14px ;color:#666;">可提现金额</p></li>
		<li><p class="apply_price"><?=$true_cash?>元</p><p style="font-size:14px ;color:#666;">实际到账金额<br/>(1%手续费)</p></li>
	</ul>
	<span class="apply_x"></span>
</div>

<div style="margin-top:126px;font-size:16px;color:#333;text-align: center;">
	成为“米商”，才能申请提现。
</div>

<div class="apply_btn" onclick="location.href='<?php echo U('/riceplan')?>'">
	我要成为米商	
</div>

<div style="text-align:center;font-size:13px;color:#666;">
	在益多米购买任意商品，满<span style="color:#ff0000;">98元</span>即可成为米商
</div>

<?php elseif ($available_cash < $cash_threshold):?>

<div style="margin-top:80px;font-size:16px;text-align:center;">
	可提现金额须<?=$cash_threshold?>元起
</div>
<div class="apply_btn" onclick="location.href='<?=$backurl?>'">返回</div>

<?php elseif (!$is_rel_bank):?>

<div style="margin-top:80px;font-size:16px;text-align:center;">
	当前支持提现到你的微信钱包<br/>
	你还没填写与微信钱包关联的实名，请填写:<br/><br/>
	<input type="text" name="bank_uname" id="bank_uname" value="" placeholder="微信钱包持卡人姓名" style="border:none;width:60%;height:30px;padding:2px 2px 2px 4px;" />
</div>
<div class="apply_btn" onclick="fill_wxpay_uname()">确定</div>
<script>
function fill_wxpay_uname() {
	var _bn = $('#bank_uname').val().trim();
	if (!_bn) {
		myAlert('请填写微信钱包持卡人姓名');
		return;
	}
	if (confirm('确保所填姓名与微信钱包持卡人姓名完全一致？')) {
		F.post('<?php echo U('cash/savebank')?>',{bank_code: 'WXPAY',bank_uname: _bn}, function(ret){
			if (ret.flag=='OK') {
				location.reload();
			}
			else {
				myAlert(ret.msg);
			}
		});
	}
}
</script>

<?php else:?>

<div class="apply_cash_money">
	<div class="c-3-1">
		<h1><?=$available_cash?>元</h1>
		<p>可提现金额</p>
	</div>
	<div class="c-3-1">
		<h1><img src="/themes/mobiles/img/apply_01.png"></h1>
	</div>
	<div class="c-3-1">
		<h1><?=$true_cash?>元</h1>
		<p>实际到帐金额</p>
	</div>
</div>

<div class="apply_cash_wx">（微信钱包查看提现余额）</div>

<button type="button" class="apply_cash_btn" id="apply_cash_btn">立即提现</button>

<div class="apply_cash_tips"><span>提醒：</span>每笔提现扣除<?=$fee_rate_percent?>的手续费，个人所得税暂时由益多米平台承担。</div>

<div class="mask" style="display: none;"></div>

<!--提现等候动画-->
<div class="apply_cash_waiting" style="display: none;">
	<h1><img src="/themes/mobiles/img/apply_02.gif"></h1>
	<h2>提现金额安全检查！</h2>
	<p>请耐心等候0~3分钟。</p>
</div>

<script>
var ids  = '<?=$available_ids?>';
var cash = '<?=$available_cash?>';
$(document).on("click", "#apply_cash_btn", function(){
	var _this = this;
	if (confirm('确定现在提现？')) {
		$(_this).attr('disabled',true);
		showWaiting();
		F.post('<?php echo U('cash/doapply','step=1')?>',{cashing_amount:cash,commision_ids:ids},function(ret){
			setTimeout(function(){
				hideWaiting();
				if(ret.flag=='SUCC') {
					showAlert(ret.msg, ret.detail,'<?php echo U('cash/detail')?>');
				}
				else {
					/*$(_this).removeAttr('disabled');*/
					showAlert(ret.msg, ret.detail);
				}
			}, 1000);
		});
	}
});

/**
 * 显示弹层
 * @param {string} title 标题
 * @param {string} info  内容
 * @param {string} url   回调跳转url
 * @param {string} id    弹层id
 */
function showAlert(title, info, url, id){
	var html = '<div class="alert_main" id="'+ (typeof(id) == 'undefined' ? '' : id) +'">';
	var fun = '';
	if(title){
		html += '<div class="alert_main_title">'+ title +'</div>';
	}
	if(info){
		html += '<div class="alert_main_info">'+ info +'</div>';
	}
	html += '<div class="alert_main_btn" onclick="hideAlert(\''+ (typeof(url) == 'undefined' ? '' : url) +'\')">确定</div></div>';
	$(".mask").show();
	$("body").append(html);
}

/**
 * 消失弹层
 * @param {string} url 跳转url
 */
function hideAlert(url){
	if(url){
		location.href = url;
	}else{
		$(".mask").hide();
		$(".alert_main").remove();
	}
}

//显示等候
function showWaiting(){
	$(".apply_cash_waiting,.mask").show();
}

//消失等候
function hideWaiting(){
	$(".apply_cash_waiting,.mask").hide();
}
</script>

<?php endif;?>