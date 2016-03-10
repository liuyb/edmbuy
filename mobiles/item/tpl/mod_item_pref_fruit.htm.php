<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<?php 
$page_head_pic = '/themes/mobiles/img/fruit3.png';
$category = 'fruit';

include T('inc/pref_popup_menu');?>

<div class="teawinecomment">
	<div class="tea_pro_list">
		<!-- <a href="javescript:;"><img src="/themes/mobiles/img/fruit1.png"></a> -->
		<a href="javascript:gotoItem(1045);"><img src="/themes/mobiles/img/gnqc.png"></a>
	</div>
	
	<div class="tea_info_list" style="margin-top:5px;">
		<ul id="goods_list" class="clearfix">
		</ul>
		<div class="clear"></div>
	</div>
</div>

<div class="click_more" data-cat='fruit' onclick="pulldata(this);">
	点击加载更多......
</div>
<script>
$(document).ready(function(){
	getGoodsList(1, 'fruit', true);
});

function contructGoodsHTML(ret, isinit){
	if(!ret || !ret.result || !ret.result.length){
		//var emptyTR = "<li style=\"text-align:center;width: 100%;margin: 0px;line-height: 50px;\">还没有商品！</li>";
		//handleGoodsListAppend(emptyTR);
		return;
	}
	var LI = buildGoodsListLI(ret);
	handleGoodsListAppend($("#goods_list"), LI, isinit);
}
</script>

<?php endif;?>