<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div id="right-icon" class="no-text hide">
  <a href="<?php echo U('trade/cart/list')?>" id="global-cart"><?=$user->ec_cart_num?></a>
</div>

<?php if ((!isset($no_display_cart) || !$no_display_cart) && $user->ec_cart_num):?>
<script>$(function(){$('#right-icon').show();});</script>
<?php endif?>

<?php if ($nav_no==1):?>

<nav id="nav-1" class="nav no-bounce">
 <div class="c-3-1 nav-it"><a href="<?php echo U()?>" <?php if('home'==$nav_flag1):?>class="cur"<?php endif;?> rel="home">首页</a></div>
 <div class="c-3-1 nav-it"><a href="<?php echo U('explore')?>" <?php if('explore'==$nav_flag1):?>class="cur"<?php endif;?> rel="explore">所有宝贝</a></div>
 <div class="c-3-1 nav-it last"><a href="<?php echo U('user')?>" <?php if('user'==$nav_flag1):?>class="cur"<?php endif;?> rel="user">我的</a></div>
</nav>
<script>
$('.nav a').click(function(){
	$(this).parents('.nav').find('a').removeClass('cur');
	$(this).addClass('cur');
});
</script>

<?php elseif($nav_no==2):/*To: if ($nav_no==1)*/?>

<nav id="nav-2" class="nav nav-<?=$nav_no?> nav-<?=$nav_flag1?> no-bounce">
  <div class="nav-body clearfix">
<?php if ('item'==$nav_flag1):?>
    <div class="c-3-1 nav-it"><a href="<?=$backurl?>" class="btn">☜返回</a></div>
    <div class="c-3-1 nav-it"><a href="javascript:void(0);" class="btn" id="btn-collect" data-goods_id="<?=$the_goods_id?>">收藏</a></div>
    <div class="c-3-1 nav-it"><a href="javascript:void(0);" class="btn btn-orange" id="btn-add-to-cart" data-goods_id="<?=$the_goods_id?>">加入购物车</a></div>
<?php elseif ('cart'==$nav_flag1):?>
    <div class="c-lt checked" id="cart-checkall"><span class="check checked"></span>全选</div>
    <div class="c-rt">
      <button class="btn btn-orange" id="cart-btncheckout">结账<span>(0)</span></button>
      <button class="btn btn-red hide" id="cart-btndelete" disabled="disabled">删除</button>
    </div>
    <div class="c-md" id="cart-totalwrap">合计：<span id="cart-totalprice">0</span>元</div>
<?php endif;?>
  </div>
</nav>
<script>
var nav_disabled = false;
function nav_set_disabled() {
	var nav_no    = '<?=$nav_no?>';
	var nav_flag1 = '<?=$nav_flag1?>';
	if (nav_no=='2' && nav_flag1=='item') {
		nav_disabled = true;
		$('#btn-collect').addClass('txt-disabled');
		$('#btn-add-to-cart').addClass('disabled');
	}
}
$(function(){

<?php if ('item'==$nav_flag1):?>
$('#btn-add-to-cart').click(function(){
	if (nav_disabled) return false;
	var _this = $(this);
	if (_this.attr('data-ajaxing')=='1') return;
	_this.attr('data-ajaxing', 1);
	var goods_id = _this.attr('data-goods_id'),
	    goods_num= 1;
	F.post('<?php echo U('trade/cart/add')?>', {goods_id:goods_id,goods_num:goods_num}, function(ret){
		_this.attr('data-ajaxing', 0);
		if (ret.code > 0) {
			$('#global-cart').text(ret.cart_num);
			$('#right-icon').show();
			var old_stock = parseInt($('#stock-num').text());
			old_stock -= ret.added_num;
			$('#stock-num').text(old_stock);
			alert(ret.msg);
		}else{
			alert(ret.msg);
		}
	});
	return false;
});

$('#btn-collect').click(function(){
	if (nav_disabled) return false;
	var _this = $(this);
	if (_this.attr('data-ajaxing')=='1') return false;
	_this.attr('data-ajaxing', 1);
	var goods_id = _this.attr('data-goods_id');
	F.post('<?php echo U('item/collect')?>', {goods_id:goods_id}, function(ret){
		_this.text('已收藏');
	});
	return false;
});
<?php endif;?>

}); //END $(function(){
</script>

<?php endif;/*End: elseif($nav_no==2)*/?>

<!-- 微信操作提示 -->
<div id="cover-wxtips" class="cover"><img alt="" src="<?=$contextpath;?>themes/mobiles/img/guide.png"/></div>