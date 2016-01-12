<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<div class="header">
	卖家常见问题
</div>

<div style="background:#fff;">
	<div class="vendor_comm">
		<span class="oper_tu"></span><span style="padding-left:14px;">需要什么样的条件才能入住？</span>
	</div>
	<div class="info_comm">
		根据《益多米商家入驻登记表》提供相关资料，提交给益多米运营团队审核，通过后为开通商家入驻。
	</div>
</div>

<div style="background:#fff;margin-top:10px;">
	<div class="vendor_comm">
		<span class="oper_tu"></span><span style="padding-left:14px;">入住后，如何上架产品？</span>
	</div>
	<div class="info_comm">
		根据益多米提供的后台链接、登录账号，登录进入“商家管理中心”，完善相关信息，上架产品。
	</div>
</div>

<div style="background:#fff;margin-top:10px;">
	<div class="vendor_comm">
		<span class="oper_tu"></span><span style="padding-left:14px;">入驻后，产品的售前和售后如何处理？</span>
	</div>
	<div class="info_comm">
		买家如有疑问会通过客服系统联系卖家，卖家与买家沟通处理。
	</div>
</div>

<div style="background:#fff;margin-top:10px;">
	<div class="vendor_comm">
		<span class="oper_tu"></span><span style="padding-left:14px;">买家下单后买错了卖家怎么处理？</span>
	</div>
	<div class="info_comm">
		如果买家付款两小时后需要退款，会直接拨打卖家电话进行沟通，如果卖家未发货，协商一致同意退款后，卖家在“商家管理中心”的订单管理进行退款操作，并把订单的微信支付交易号和订单号提供给益多米客服。退款操作处理完成后，益多米客服根据订单号，后台关闭订单交易。
	</div>
</div>

<div style="background:#fff;margin-top:10px;">
	<div class="vendor_comm">
		<span class="oper_tu"></span><span style="padding-left:14px;">下单后，如何查看订单和发货？</span>
	</div>
	<div class="info_comm">
		卖家在“商家管理中心”的订单管理查看订单和发货。
	</div>
</div>

<div style="background:#fff;margin-top:10px;">
	<div class="vendor_comm">
		<span class="oper_tu"></span><span style="padding-left:14px;">买家退货卖家如何处理？</span>
	</div>
	<div class="info_comm">
		<p>如买家需退货，在收到货7天内（以快递牵手时间为准过期无效），联系卖家提供退货信息。</p>
		<p>卖家与买家沟通协商后，同意退货。卖家在后台订单管理，点击“退货”，订单状态进入“退货中”。结束后，卖家在后台点击退货成功（关闭交易）。
	</div>
</div>

<div style="background:#fff;margin:10px 0;">
	<div class="vendor_comm">
		<span class="oper_tu"></span><span style="padding-left:14px;">卖家后台出现问题如何处理？</span>
	</div>
	<div class="info_comm">
		<p>如问题是卖家自行操作错误引起，请直接在后台修改相关数据；</p>
		<p>如问题是平台系统出错引起，请联系益多米平台客服进行处理。</p>
	</div>
</div>
<?php include T('inc/followbox');?>

<?php endif;?>
