<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if (''!=$errmsg):?>

<div class="list-empty">
  <h1 class="list-empty-header"><?=$errmsg?></h1>
</div>

<?php else :?>

<div class="order-express">
<?php if($user_addrs_num>0):?>
  <?php $i=0; foreach($user_addrs AS $addr):?>
    <?php if ($i++>0): break; endif;/*只显示第一行*/?>
  <div class="express-it" id="express-it"
    data-addrid="<?=$addr['address_id']?>"
    data-consignee="<?=$addr['consignee']?>"
    data-contact_phone="<?=$addr['contact_phone']?>"
    data-country="<?=$addr['country']?>"
    data-country_name="<?=$addr['country_name']?>"
    data-province="<?=$addr['province']?>"
    data-province_name="<?=$addr['province_name']?>"
    data-city="<?=$addr['city']?>"
    data-city_name="<?=$addr['city_name']?>"
    data-district="<?=$addr['district']?>"
    data-district_name="<?=$addr['district_name']?>"
    data-address="<?=$addr['address']?>"
    data-zipcode="<?=$addr['zipcode']?>"
    >
    <div class="express-detail">
      <p class="express-people"><em>收货人　：</em><span><?=$addr['show_consignee']?></span></p>
      <p class="express-addr"><em>收货地址：</em><span><?=$addr['show_address']?></span></p>
      <p class="express-add hide"><span>添加收货地址</span></p>
    </div>
  </div>
  <?php endforeach; unset($i);?>
<?php else:?>
  <div class="express-it express-noicon" id="express-it" data-addrid="0">
    <div class="express-detail">
      <p class="express-people hide"><em>收货人　：</em><span></span></p>
      <p class="express-addr hide"><em>收货地址：</em><span></span></p>
      <p class="express-add"><span>添加收货地址</span></p>
    </div>
  </div>
<?php endif;?>
</div>

<div class="list-container order-goods-list">

<?php if($order_goods_num):?>
  <div class="list-head">
    <span>结账商品</span>
  </div>
<?php endif;?>

  <ul class="list-body" id="cart-list-body" data-cart_rids="<?=$cart_rids_str?>">
  
  <?php foreach($order_goods AS $g):?>
    <li class="it clearfix" data-url="<?=$g['goods_url']?>" data-rid="<?=$g['rec_id']?>">
      <div class="c-24-5 col-2 withclickurl"><img src="<?=$g['goods_thumb']?>" alt="" class="goods_pic" /></div>
      <div class="c-24-14 col-3 withclickurl"><?=$g['goods_name']?></div>
      <div class="c-24-5 col-4">
        ￥<span class="gprice"><?=$g['goods_price']?></span>
        <div class="gnum cart-gnum">
          <span class="gnum-show">x<?=$g['goods_number']?></span>
        </div>
      </div>
    </li>
  <?php endforeach;?>
    <li class="it">
      <div><textarea name="remark" placeholder="有话跟商家说..." class="order-message" id="order-message"></textarea></div>
      <div class="order-total-price clearfix">总价<span class="fr">￥<?=$total_price?></span></div>     
    </li>
  </ul>
</div>

<div class="order-topay">
  <div class="row"><button class="btn btn-block btn-green" id="btn-wxpay" data-payid="2">微信安全支付</button></div>
  <div class="row"><a class="btn btn-block btn-white" href="<?php echo U('trade/cart/list')?>">返回购物车修改</a></div>
  <div class="row row-last">支付完成后，如需退换货请及时联系商家</div>
</div>

<?php form_topay_script(U('trade/order/record'));?>

<script>
function wxEditAddressCallback(res) {
  if (res) { //有返回
    //alert(res.err_msg);
    if (res.err_msg=='edit_address:ok') {
      var $expr = $('#express-it');
      $('.express-add', $expr).hide();
      $('.express-people span', $expr).text(res.userName+'（'+res.telNumber+'）').parent().show();
      $('.express-addr span', $expr).text(res.proviceFirstStageName+res.addressCitySecondStageName+res.addressCountiesThirdStageName+res.addressDetailInfo)
                                    .parent().show();
      if ($expr.hasClass('express-noicon')) $expr.removeClass('express-noicon');

      //填充数据
      $expr.attr('data-consignee', res.userName)
           .attr('data-contact_phone', res.telNumber)
           .attr('data-country', 1)
           .attr('data-country_name', '中国')
           .attr('data-province', 0)
           .attr('data-province_name', res.proviceFirstStageName)
           .attr('data-city', 0)
           .attr('data-city_name', res.addressCitySecondStageName)
           .attr('data-district', 0)
           .attr('data-district_name', res.addressCountiesThirdStageName)
           .attr('data-address', res.addressDetailInfo)
           .attr('data-zipcode', res.addressPostalCode);

      //更新到服务器
      var updata = {};
      updata.address_id    = parseInt($expr.attr('data-addrid'));
      updata.consignee     = $expr.attr('data-consignee');
      updata.contact_phone = $expr.attr('data-contact_phone');
      updata.country       = parseInt($expr.attr('data-country'));
      updata.country_name  = $expr.attr('data-country_name');
      updata.province      = parseInt($expr.attr('data-province'));
      updata.province_name = $expr.attr('data-province_name');
      updata.city          = parseInt($expr.attr('data-city'));
      updata.city_name     = $expr.attr('data-city_name');
      updata.district      = parseInt($expr.attr('data-district'));
      updata.district_name = $expr.attr('data-district_name');
      updata.address       = $expr.attr('data-address');
      updata.zipcode       = $expr.attr('data-zipcode');
      F.post('<?php echo U('trade/order/upaddress')?>',updata,function(ret){
  			if (ret.flag=='SUC') {
  	  		if (!updata.address_id) {
  	  		  $expr.attr('data-addrid', ret.address_id);
  	  		}
  			}
  			else{
  				//alert(ret.msg);
  			}
			});
    }
    
  }else{ //空，用户取消
	  
  }
}

$(function(){
	$('#express-it').click(function(){
		if(typeof(wxEditAddress)=='function') wxEditAddress(wxEditAddressCallback);
		return false;
	});
	$('#cart-list-body .withclickurl').click(function(){
		window.location.href = $(this).parent().attr('data-url');
		return false;
	});
	$('#btn-wxpay').click(function(){
		var pay_id   = parseInt($(this).attr('data-payid'));
		var addr_id  = parseInt($('#express-it').attr('data-addrid'));
		var order_msg= $('#order-message').val();
		var cart_rids= $('#cart-list-body').attr('data-cart_rids');
		if (!addr_id) {
			alert('请填写收货地址');
			return false;
		}

		var _this = this;
		$(this).text('努力加载中, 请稍候...').attr('disabled',true);
		F.post('<?php echo U('trade/order/submit')?>',{"address_id":addr_id,"cart_rids":cart_rids,"order_msg":order_msg,"pay_id":pay_id},function(ret){
  			if (ret.flag=='SUC') {
  				$(_this).text('支付跳转中...');
  				form_topay_submit(ret.order_id, 'wxpay');
  			}
  			else{
  				$(_this).text('微信安全支付').removeAttr('disabled');
  				alert(ret.msg);
  			}
	  });
		
		return false;
	});
});
</script>

<?php endif;?>