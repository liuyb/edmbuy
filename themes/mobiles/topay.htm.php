<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?php echo $supported_paymode[$pay_mode]?> - 益多米</title>
  <style type="text/css">html, body, div, p, span, em, strong, h1, h2, h3, h4, h5, h6, a, center, form, table { margin: 0;padding: 0;border: 0;font-size: 100%;font: inherit;vertical-align: baseline; } html {font-size: 62.5%;} #pay-msg{ padding: 10px;text-align: center;font-size: 16px;font-size: 1.6rem;color: #333; }</style>
  <script type="text/javascript">var back_url = '<?=$back_url?>',order_id = '<?=$order_id?>';
  
<?php if ('wxpay'==$pay_mode): ?>
  //调用微信JS api 支付
  function jsApiCall()
  {
  	WeixinJSBridge.invoke(
  		'getBrandWCPayRequest',
  		<?php echo $jsApiParams; ?>,
  		function (res){
  	  	var e = document.getElementById('pay-msg');
  	  	if ("get_brand_wcpay_request:ok" == res.err_msg) {
  	  	  e.innerHTML = '<font color="green">支付成功</font>';
  	  	}
  	  	else if ("get_brand_wcpay_request:cancel" == res.err_msg) {
  	  	  F.post('<?php echo U('trade/order/chpaystatus')?>',{"order_id":order_id,"status_to":4});
  	  	  e.innerHTML = '<font color="red">取消支付</font>';
  	  	}
  	  	else {
  	  	  F.post('<?php echo U('trade/order/chpaystatus')?>',{"order_id":order_id,"status_to":5});
  	  	  e.innerHTML = '<font color="red">支付失败！</font>';
  	  	}
  	  	if (''!=back_url) {
  	  	  setTimeout(function(){ window.location.href = back_url }, 500);
  	  	}
  	  	else {
  	  	  setTimeout(function(){ WeixinJSBridge.invoke("closeWindow"); },1000);
  	  	}
  		}
  	);
  }
<?php elseif ('alipay'==$pay_mode): ?>
  //支付宝支付
<?php endif;?>

  //调用支付控件统一入口
  function goPay(pay_mode)
  {
	  if ('wxpay'==pay_mode) {
    	if (typeof WeixinJSBridge == "undefined"){
    	    if( document.addEventListener ){
    	        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
    	    }else if (document.attachEvent){
    	        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
    	        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
    	    }
    	}else{
    	    jsApiCall();
    	}
	  }
	  else if('alipay'==pay_mode) {
		  
	  }
  }
  </script>
</head>
<body>
<div id="pay-msg">安全支付中...</div>
</body>
<script type="text/javascript">setTimeout(function(){ goPay('<?=$pay_mode?>'); },0);</script>
</html>