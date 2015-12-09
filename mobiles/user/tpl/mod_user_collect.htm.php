<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if (!$collect_num):?>

<div class="list-empty">
  <h1 class="list-empty-header">还没收藏的东西</h1>
  <div class="list-empty-content"><a href="<?php echo U('explore')?>">去逛逛</a></div>
</div>

<?php else :?>

<div class="list-container list-container-single">
  
  <div class="list-head list-head-gray">
    <span>我的收藏</span>
    <a href="<?php echo U('user');?>" class="fr op">☜返回</a>
  </div>
  
  <div class="list-body list-body-collect">
  
  <?php foreach($collect_list AS $g):?>
    <div class="it clearfix" data-url="<?=$g['goods_url']?>">
      <div class="c-24-5 col-2 withclickurl"><img src="<?=$g['goods_thumb']?>" alt="" class="goods_pic" /></div>
      <div class="c-24-14 col-3 withclickurl">
        <p><?=$g['goods_name']?></p>
        <p><span class="tip">收藏时间：</span><br><?php echo date('Y-m-d H:i:s',$g['add_time']);?></p>
      </div>
      <div class="c-24-5 col-4"><a href="javascript:;" class="op cancel-collect" data-rid="<?=$g['rec_id']?>">取消收藏</a></div>
    </div>
  <?php endforeach;?>
    
  </div>
  
</div>
<?php require_scroll2old();?>
<script>
$(function(){
	var $lbod = $('.list-body');
	var thisctx = {};
	
	$('.withclickurl',$lbod).click(function(){
		window.location.href = $(this).parent().attr('data-url');
		return false;
	});

	$('.cancel-collect', $lbod).click(function(){
		if (typeof(thisctx.ajaxing_cancel)=='undefined') {
			thisctx.ajaxing_cancel = 0;
		}
		if (thisctx.ajaxing_cancel) return false;
		thisctx.ajaxing_cancel = 1;

		if (confirm('确定取消该收藏？')) {
  		var pdata = {"rec_id": parseInt($(this).attr('data-rid'))};
  		var _this = this;
  		F.post('<?php echo U('user/collect/cancel')?>',pdata,function(ret){
  			thisctx.ajaxing_cancel = undefined;
  			if (ret.flag=='SUC') {
  				$(_this).parents('.it').fadeOut();
  			}
  			else {
  	  			alert(reg.msg);
  			}
  		});
		}
		else {
			thisctx.ajaxing_cancel = undefined;
		}
		return false;
	});
	
});
</script>

<?php endif;?>