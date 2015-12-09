<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(isset($nav_flag1) && 'explore'==$nav_flag1):?>

<nav class="topnav">
  <div class="listyle"><a href="javascript:;" onclick="change_list_style(this)"><i class="micon"></i></a></div>
  <div class="mbar clearfix">
    <a href="javascript:;" class="fl <?php if($type!='new_arrival'): echo 'on'; endif;?>" onclick="change_sortorder(this)" rel="1" id="topnav-btn-change-order"><?php echo $order_set[$order]['name']?><b class="triangle"></b></a>
    <a href="javascript:;" class="fr" onclick="to_filter(this)" rel="1" id="topnav-btn-filter">筛选<b class="triangle"></b></a>
    <a href="<?php echo U('explore','t=new_arrival')?>" <?php if($type=='new_arrival'): echo 'class="on"'; endif; ?>>新品&nbsp;&nbsp;&nbsp;</a>
  </div>
</nav>
<script>
function change_list_style(ele) {
	if ($(ele).hasClass('ls2')) {
		$(ele).removeClass('ls2');
		$('#listyle-2').hide();
		$('#listyle-1').show();
	} else {
		$(ele).addClass('ls2');
		$('#listyle-1').hide();
		$('#listyle-2').show();
	}
	F.set_scroller(false,0);
}
function change_sortorder(ele) {
	var me = change_sortorder;
	if (typeof(me._target)!='object') {
		me._target = $('#down-sortmenu');
	}
	if (typeof(me._cover)!='object') {
		me._cover = $('#pageCover');
	}
	to_show_downmenu(ele, me._target, me._cover);
}
function to_filter(ele) {
	var rel = $(ele).attr('rel');
	if (rel=='1') {
		rel = '2';
		$(ele).find('.triangle').addClass('triangle-up');
	}
	else {
		rel = '1';
		 $(ele).find('.triangle').removeClass('triangle-up');
	}
	$(ele).attr('rel', rel);
	if ($('#topnav-btn-change-order').attr('rel')=='2') {
		$('#topnav-btn-change-order').click();
	}
	show_popdlg('筛选', $('#popfilter-html').html());
	return false;
}
function to_show_downmenu(ele,target,cover) {
	if (typeof(target)!='object') {
		target = $('#'+target);
	}
	if (typeof(cover)!='object') {
		cover = $('#'+cover);
	}
	
	var rel = $(ele).attr('rel');
	var inPreClass = 'ui-page-pre-in',
         inClass = 'slidedown in',
        outClass = 'slidedown out reverse';
  if (rel=='1') {
	  rel = '2';
	  $(ele).find('.triangle').addClass('triangle-up');
	  cover.show();
  	target.addClass(inPreClass).show();
  	target.animationComplete(function(){
  		target.removeClass(inClass);
    });
  	target.removeClass(inPreClass).addClass(inClass);
  }
  else {
	  rel = '1';
	  $(ele).find('.triangle').removeClass('triangle-up');
	  target.animationComplete(function(){
		  target.removeClass(outClass).hide();
		  cover.hide();
	  });
	  target.addClass(outClass);
  }
  $(ele).attr('rel',rel);
}
</script>

<?php elseif (isset($nav_flag1) && 'cart'==$nav_flag1):?>

<nav class="topnav topnav-cart clearfix" id="topnav-cart">
  <a class="c-3-1<?php if('cartlist'==$nav_flag2):echo ' on';endif;?>" href="<?php echo U('trade/cart/list')?>">购物车</a>
  <a class="c-3-1<?php if('buyrecord'==$nav_flag2):echo ' on';endif;?>" href="<?php echo U('trade/order/record','showwxpaytitle=1')?>">购买记录</a>
  <a class="c-3-1" href="<?=$backurl?>">☜返回</a>
</nav>
<script>
$(function(){
	$('#topnav-cart a').click(function(){
		$('#topnav-cart a').removeClass('on');
		$(this).addClass('on');
	});
});
</script>

<?php endif;?>