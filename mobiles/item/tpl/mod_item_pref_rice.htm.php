<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/receal.png';
$category = 'rice';

include T('inc/pref_popup_menu');?>

<div class="tea_info_list">
	<ul id="goods_list">
	</ul>
</div>

<div class="click_more" data-cat='rice' onclick="pulldata(this);">
	点击加载更多......
</div>
<script>
$(document).ready(function(){
	getGoodsList(1, 'rice', true);
});

function contructGoodsHTML(ret, isinit){
	if(!ret || !ret.result || !ret.result.length){
		var emptyTR = "<li style=\"text-align:center;width: 100%;margin: 0px;line-height: 35px;background:#fff;\">还没有商品！<br/>招商座机：0755-26418979</li>";
		handleGoodsListAppend($("#goods_list"), emptyTR, isinit);
		return;
	}
	var LI = buildGoodsListLI(ret);
	handleGoodsListAppend($("#goods_list"), LI, isinit);
}
</script>

<?php endif;?>