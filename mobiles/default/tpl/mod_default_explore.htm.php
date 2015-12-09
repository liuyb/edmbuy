<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="block block2<?php if(!$goods_num): echo ' block-empty'; endif;?>" id="listyle-1">
  <ul class="liset">
<?php if ($goods_num):?>
  <?php foreach($goods_list AS $it):?>
    <li class="liit">
      <div data-href="<?php echo U('item/'.$it['goods_id'])?>" class="liit-content">
        <img src="<?php echo ploadingimg()?>" alt="<?=$it['goods_name']?>" class="gpic" data-loaded="0" onload="imgLazyLoad(this,'<?=$it['goods_img']?>')" />
        <h3 class="gt"><?=$it['goods_name']?></h3>
        <p class="gp gbtmclick">
          <em>￥<?=$it['shop_price']?></em>
          <span class="tip">
          <?php if($order=='click'):?>
          <?=$it['click_count']?>查看
          <?php elseif($order=='collect'):?>
          <?=$it['collect_count']?>收藏
          <?php else:?>
          共售<?=$it['paid_order_count']?>笔
          <?php endif;?>
          </span>
          <span class="dmore">...</span>
        </p>
        <div class="itcover">
          <div class="itcover-cont">
            <p class="btnrow"><button class="btn add-coll" data-goods_id="<?=$it['goods_id']?>" <?php if($it['collected']): echo 'data-ajaxing="1"';else: echo 'data-ajaxing="0"'; endif;?>><i>⭐️</i><?php if($it['collected']): echo '<span class="collected">已收藏</span>';else: echo '<span>加入收藏</span>'; endif;?></button></p>
            <p class="btnrow last"><button class="btn add-cart" data-goods_id="<?=$it['goods_id']?>"><i></i><span>放入购物车</span></button></p>
          </div>
        </div>
      </div>
    </li>    
  <?php endforeach;?>
<?php else:?>
  <li class="liempty">没找到相应的产品</li>
<?php endif?>
  </ul>
</div>

<div class="block block3<?php if(!$goods_num): echo ' block-empty'; endif;?>" id="listyle-2">
  <ul class="liset">
<?php if ($goods_num):?>
  <?php foreach($goods_list AS $it):?>
    <li class="bbsizing liit">
      <div data-href="<?php echo U('item/'.$it['goods_id'])?>" class="liit-content clearfix">
        <div class="left"><img src="<?php echo ploadingimg()?>" alt="<?=$it['goods_name']?>" class="gpic" data-loaded="0" onload="imgLazyLoad(this,'<?=$it['goods_img']?>')" /></div>
        <div class="right">
          <h3 class="gt"><?=$it['goods_name']?></h3>
          <p class="gp"><em>￥<?=$it['shop_price']?></em></p>
          <p class="gbtm gbtmclick">
            <span class="tip">
          <?php if($order=='click'):?>
          查看数: <?=$it['click_count']?>
          <?php elseif($order=='collect'):?>
          收藏数: <?=$it['collect_count']?>
          <?php else:?>
          共售 <?=$it['paid_order_count']?> 笔
          <?php endif;?>
            </span>
            <span class="dmore">...</span>
          </p>
        </div>
        <div class="itcover">
          <div class="itcover-cont itcover-cont2">
            <button class="btn add-coll" data-goods_id="<?=$it['goods_id']?>" <?php if($it['collected']): echo 'data-ajaxing="1"';else: echo 'data-ajaxing="0"'; endif;?>><i>⭐️</i><?php if($it['collected']): echo '<span class="collected">已收藏</span>';else: echo '<span>加入收藏</span>'; endif;?></button>
            <button class="btn add-cart last" data-goods_id="<?=$it['goods_id']?>"><i></i><span>放入购物车</span></button>
          </div>
        </div>
      </div>
    </li>    
  <?php endforeach;?>
<?php else:?>
  <li class="liempty">没找到相应的产品</li>
<?php endif?>
  </ul>
</div>

<script type="text/html" id="downmenu-html">
<div class="pageCover" id="pageCover"></div>
<ul class="bbsizing downmenu" id="down-sortmenu">
<?php foreach ($order_set AS $it):?>
  <?php if(!$it['is_show']): continue; endif;?>
  <li class="mit<?php if($it['is_last']): echo ' last';endif;if($order==$it['id']): echo ' on';endif;?>">
    <a href="<?php echo U('explore',$qstr.($qstr==''&&'zonghe'==$it['id']?'':'o='.$it['id']))?>" rel="<?=$it['id']?>"><?=$it['name']?></a>
    <?php if($order==$it['id']): echo '<span>√</span>';endif;?>
  </li>
<?php endforeach;?>
</ul>
</script>

<script type="text/html" id="popfilter-html">
<dl id="pop-filter">
  <dt class="first">商品分类</dt>
  <dd>
    <select name="filter_category" id="filter_category" onchange="filter_change(this)">
      <option value="0">所有分类</option>
<?php $i=0; foreach ($filter_category AS $it):?>
      <option value="<?=$it['cat_id']?>" <?php if($the_cat_id==$it['cat_id']): echo 'selected="selected"'; endif; ?>><?=$it['cat_name']?></option>
<?php endforeach;?>
    </select>
  </dd>
  <dt>商品品牌</dt>
  <dd>
    <select name="filter_brand" id="filter_brand" onchange="filter_change(this)">
      <option value="0">所有品牌</option>
<?php $i=0; foreach ($filter_brand AS $it):?>
      <option value="<?=$it['brand_id']?>" <?php if($the_brand_id==$it['brand_id']): echo 'selected="selected"'; endif; ?>><?=$it['brand_name']?></option>
<?php endforeach;?>
    </select>
  </dd>
  <dt>价格区间</dt>
  <dd>
    从 <select name="filter_price_from" id="filter_price_from" onchange="filter_change(this)" style="width:40%">
<?php for($i=0; $i<=1000; $i+=50):?>
      <option value="<?=$i?>" <?php if($the_price_from===$i): echo 'selected="selected"'; endif; ?>><?=$i?></option>
<?php endfor;?>
			<option value="1001">1000以上</option>
    </select> 到 <select name="filter_price_to" id="filter_price_to" onchange="filter_change(this)" style="width:40%">
<?php for($i=0; $i<=1000; $i+=50):?>
      <option value="<?=$i?>" <?php if($the_price_to===$i): echo 'selected="selected"'; endif; ?>><?=$i?></option>
<?php endfor;?>
    </select>
  </dd>
</dl>
<div id="pop-btm">
<button class="btn btn-white" id="pop-clear" onclick="finish_filter(this)">清除条件</button>
<button class="btn btn-orange" id="pop-finish" onclick="finish_filter(this)">完 成</button>
</div>
</script>

<?php include T($tpl_footer);?>
<?php require_scroll2old();?>
<script>F.isDownpullDisplay=false;</script>
<script>
function show_dmore_cover(target, is_hide) {
	if (typeof(is_hide)=='undefined') {
		is_hide = false;
	}
	
	//js方式(在这里，居然js方式比css3方式效果更好(css3很卡顿，见下面))
	var w = 0, h = 0;
	if (!target.attr('data-width')) { //确保只写一次则可
		target.attr('data-width',target.parent().width()+'px');
		target.attr('data-height',target.parent().height()+'px');
		w = target.attr('data-width');
		h = target.attr('data-height');
		target.css({'width': w, 'height': h});
	}
	else {
		h = target.attr('data-height');
	}
	if (is_hide) {
		target.find('>.itcover-cont').hide();
		target.animate({'top': h}, 100, function(){ $(this).hide(); });
	}
	else {
		$('#listyle-1 .itcover,#listyle-2 .itcover').hide().css({'top': h}).find('>.itcover-cont').hide(); //hide all first
		target.show().animate({'top': 0}, 200, function(){ $(this).find('>.itcover-cont').show(); });
	}
	
	/*
	//css3方式
	var inPreClass = 'ui-page-pre-in',
         inClass = 'slideup in',
        outClass = 'slideup out reverse';
  if (is_hide) {
	  target.animationComplete(function(){
		  target.removeClass(outClass).hide();
	  });
	  target.addClass(outClass);
  }
  else {
	  $('.liset .itcover').hide();
  	target.addClass(inPreClass).show();
  	target.animationComplete(function(){
  		target.removeClass(inClass);
    });
  	target.removeClass(inPreClass).addClass(inClass);
  }
	*/
}
var current_order = '<?=$order?>';
var form_changed = false;
function filter_change(ele) {
	form_changed = true;
	var eid = $(ele).attr('id');
	eid = typeof(eid)=='undefined'?'':eid;
	if (eid==='filter_price_from') {
		if ($(ele).val()>1000) {
			$('#filter_price_to').val('0');
		}
	}
	else if (eid==='filter_price_to') {
		
	}
}
function finish_filter(ele) {
	var me = finish_filter;
	if (typeof (me._wrap) == 'undefined') {
		me._wrap = $('#pop-filter');
	}
	$(ele).attr('disabled',true);

	var is_clear = $(ele).attr('id')=='pop-clear' ? true : false;
	if (is_clear) {
		$('#filter_category', me._wrap).val('0');
		$('#filter_brand', me._wrap).val('0');
		$('#filter_price_from', me._wrap).val('0');
		$('#filter_price_to', me._wrap).val('0');
		form_changed = true;
	}
	
	var cid = $('#filter_category', me._wrap).val();
	var bid = $('#filter_brand', me._wrap).val();
	var price_from = $('#filter_price_from', me._wrap).val();
	var price_to   = $('#filter_price_to', me._wrap).val();
	cid        = parseInt(cid);
	bid        = parseInt(bid);
	price_from = parseFloat(price_from);
	price_to   = parseFloat(price_to);
	if (price_from && price_to && price_from > price_to) {
		t = price_from;
		price_from = price_to;
		price_to = t;
	}
	$('#filter_price_from', me._wrap).val(price_from);
	$('#filter_price_to', me._wrap).val(price_to);

	var gurl = '<?php echo U('explore')?>';
	if (form_changed) {
		gurl += '?cid='+cid+'&bid='+bid+'&price_from='+price_from+'&price_to='+price_to;
		gurl += '&o='+current_order;
		hide_popdlg(function(){
			window.location.href = gurl;
		});
	}
	else {
		hide_popdlg(function(){
			$('#topnav-btn-filter').attr('rel',1).find('.triangle').removeClass('triangle-up');
		});
	}
}
$(function(){
	F.pageactive.append($('#downmenu-html').html());
	$('#pageCover').click(function(){
		if ($('#down-sortmenu').css('display')!='none') {
			$('#topnav-btn-change-order').click();
		}
		else {
			$('#topnav-btn-filter').click();
		}
		return false;
	});

	var $liset = $('.liset');
	$('.gbtmclick', $liset).click(function(){
		show_dmore_cover($(this).parents('.liit-content').find('>.itcover'));
		return false;
	});
	$('.itcover',$liset).click(function(){
		show_dmore_cover($(this), true);
		return false;
  });
	$('.liit-content',$liset).click(function(){
		window.location.href = $(this).attr('data-href');
	});

	//加入购物车
	$('.add-cart', $liset).click(function(){
		var me = $(this);
		if (me.attr('data-ajaxing')=='1') return;
		me.attr('data-ajaxing', '1');
		var goods_id = me.attr('data-goods_id'),
		    goods_num= 1;
		F.post('<?php echo U('trade/cart/add')?>', {'goods_id':goods_id,'goods_num':goods_num}, function(ret){
			me.attr('data-ajaxing', '0');
			if (ret.code > 0) {
				$('#global-cart').text(ret.cart_num);
				$('#right-icon').show();
				alert(ret.msg);
			}else{
				alert(ret.msg);
			}
		});
		return false;
	});

	//加入收藏
	$('.add-coll', $liset).click(function(){
		var me = $(this);
		if (me.attr('data-ajaxing')=='1') return false;
		me.attr('data-ajaxing', '1');
		var goods_id = me.attr('data-goods_id');
		F.post('<?php echo U('item/collect')?>', {'goods_id':goods_id}, function(ret){
			me.find('span').text('已收藏').addClass('collected');
		});
		return false;
	});
});
</script>
