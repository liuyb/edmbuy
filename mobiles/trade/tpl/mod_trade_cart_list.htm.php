<?php defined('IN_SIMPHP') or die('Access Denied');?>

<div class="list-container cart-goods-list">

<?php if($cartRecNum):?>
  <div class="list-head">
    <span>购物车中商品</span>
    <a href="javascript:;" class="fr edit" data-editmode="0" id="cart-edit">编辑</a>
  </div>
<?php endif;?>

  <ul class="list-body" id="cart-list-body">
<?php if($cartRecNum):?>
  <?php foreach($cartGoods AS $g):?>
    <li class="it cart-goods-it clearfix" data-url="<?=$g['goods_url']?>" data-rid="<?=$g['rec_id']?>">
      <div class="c-24-2 col-1"><span class="check checked" data-rid="<?=$g['rec_id']?>" data-goods_id="<?=$g['goods_id']?>" data-gnum="<?=$g['goods_number']?>" data-gprice="<?=$g['goods_price']?>"></span></div>
      <div class="c-24-5 col-2 withclickurl"><img src="<?=$g['goods_thumb']?>" alt="" class="goods_pic" /></div>
      <div class="c-24-10 col-3 withclickurl"><?=$g['goods_name']?></div>
      <div class="c-24-7 col-4">
        ￥<span class="gprice"><?=$g['goods_price']?></span>
        <div class="gnum cart-gnum">
          <span class="gnum-show">x<?=$g['goods_number']?></span>
          <div class="quantity gnum-change hide">
            <button class="minus<?php if(1==$g['goods_number']) echo ' disabled'?>" type="button"></button>
            <input type="text" class="txt" value="<?=$g['goods_number']?>" readonly="readonly"/>
            <button class="plus" type="button"></button>
            <div class="response-area response-area-minus"></div>
            <div class="response-area response-area-plus"></div>
            <div class="txtCover"></div>
          </div>
        </div>
      </div>
    </li>
  <?php endforeach;?>
<?php else:?>
    <li class="list-empty">
      <h1 class="list-empty-header">购物车空空如也  o(╯□╰)o</h1>
      <div class="list-empty-content"><a href="<?php echo U('explore')?>">去逛逛，我要血拼！</a></div>
    </li>
<?php endif;?>

  </ul>
</div>
<?php require_scroll2old();?>
<script>
$(function(){
	$('#cart-list-body .withclickurl').click(function(){
		window.location.href = $(this).parent().attr('data-url');
		return false;
	});
});
</script>
<script>set_cart_action('<?=$cartNum?>','<?=$cartRecNum?>');/*see m.js*/</script>