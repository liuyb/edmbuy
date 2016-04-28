<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<script id="forTopnav" type="text/html">
<div class="header">
	推广素材
	<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
</script>
<div class="gene_list">
	<div class="g_list_infos">
		<ul>	
			<li id="li1" onclick="f(1)" class="navig_on">招商素材</li>
			<li id="li2" onclick="f(2)">代理素材</li>
			<li id="li3" onclick="f(3)">米商素材</li>
		</ul>
	</div>
</div>

<div class="gener_list_t" id="list1">
	<div class="list_t_info">招商素材招商素材招商素材</div>
	<div class="list_t_info">招商素材招商素材招商素材</div>
	<div class="list_t_info">招商素材招商素材招商素材</div>
	<div class="list_t_info">招商素材招商素材招商素材</div>
	<div class="list_t_info">招商素材招商素材招商素材</div>
	<div class="list_t_info">招商素材招商素材招商素材</div>
	<div class="list_t_info">招商素材招商素材招商素材</div>
</div>

<div class="gener_list_t" id="list2" style="display:none">
	<div class="list_t_info">米商素材米商素材米商素材商素材米商素材</div>
	<div class="list_t_info">米商素材米米商素材米材米商素材</div>
	<div class="list_t_info">米商素材米商素商素材米</div>
	<div class="list_t_info">米商素材米商素材米材米商素材</div>
	<div class="list_t_info">米商素材米商素米商素材米商素材</div>
	<div class="list_t_info">米商素材米商素材材</div>
	<div class="list_t_info">米商素材米商素材米商商素材米商商素材米商米商素材</div>
	<div class="list_t_info">米商素材米商素材米米商素材</div>
	<div class="list_t_info">米商素材米商素商素材米商商素材米商商素材米商材材</div>
	<div class="list_t_info">米商素材米商素材材米商素材</div>
</div>

<div class="gener_list_t" id="list3" style="display:none">
	<div class="list_t_info">推广素推广素材推广素材</div>
	<div class="list_t_info">推广素材推广广素材推广素材</div>
	<div class="list_t_info">推广素材推广广素材推广素材</div>
	<div class="list_t_info">推广素材推广广素材推广素材</div>
	<div class="list_t_info">推广素材推广广素材推广素材</div>
</div>

<script>
//切换
function f(a){
	var _li = "#li" + a;
	var _list = "#list" + a;
	
	$(".gener_list_t").hide();
	$(".g_list_infos li").removeClass("navig_on");
	
	$(_li).addClass("navig_on");
	$(_list).show();
}
</script>
<?php endif;?>