<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
      <?php for($i=0;$i<count($ad);$i++):?>
      <?php if($i==0):?>
      <li style="display:block"><a href="<?=$ad[$i]['link']?>"><img src="<?=$ad[$i]['pic_path']?>" alt="<?=$ad[$i]['title']?>" /></a></li>
      <?php else:?>
      <li ><a href="<?=$ad[$i]['link']?>"><img src="<?=$ad[$i]['pic_path']?>" alt="<?=$ad[$i]['title']?>" /></a></li>
      <?php endif;?>
      <?php endfor;?>
    </ul>
    <div id="pagenavi" class="pagenavi clearfix">
      <?php for($i=0;$i<count($ad);$i++):?>
      <?php if($i==0):?>
      <a href="javascript:void(0);" class="active"><?php echo $i+1;?></a>
      <?php else:?>
      <a href="javascript:void(0);"><?php echo $i+1;?></a>
      <?php endif;?>
      <?php endfor;?>
     </div>
  </div>
</div>
<script type="text/javascript">
var t1;
$(function(){
	var _active = 0, $_ap = $('#pagenavi a');
  
  t1 = new TouchSlider({
     id:'slider',
     speed:600,
     timeout:6000,
     before:function(newIndex, oldSlide){
       $_ap.get(_active).className = '';
       _active = newIndex;
       $_ap.get(_active).className = 'active';
     }
  });
  
  $_ap.each(function(index,ele){
    $(ele).click(function(){
      t1.slide(index);
      return false;     
    });
  });
  
  setTimeout(function(){t1.resize();},500);
});
</script>

<div class="block">
  <h1  class="tit">最新到货</h1>
  <div class="cont">
    <ul class="liset">
    <?php foreach($goods_latest AS $it):?>
      <li class="liit">
        <a href="<?php echo U('item/'.$it['goods_id'])?>" class="liit-content">
          <img src="<?php echo ploadingimg()?>" alt="<?=$it['goods_name']?>" class="gpic"  onload="imgLazyLoad(this,'<?=$it['goods_img']?>')" />
          <h3 class="gt"><?=$it['goods_name']?></h3>
          <p class="gp"><em>￥<?=$it['shop_price']?></em></p>
        </a>
      </li>    
    <?php endforeach;?>
    </ul>
  </div>
</div>

<?php foreach($category_top AS $top):?>
<div class="block">
  <h1  class="tit"><?=$top['cat_name']?></h1>
  <div class="cont">
    <ul class="liset">
    <?php foreach($top['goods_set'] AS $it):?>
      <li class="liit">
        <a href="<?php echo U('item/'.$it['goods_id'])?>" class="liit-content">
          <img src="<?php echo ploadingimg()?>" alt="<?=$it['goods_name']?>" class="gpic" onload="imgLazyLoad(this,'<?=$it['goods_img']?>')" />
          <h3 class="gt"><?=$it['goods_name']?></h3>
          <p class="gp"><em>￥<?=$it['shop_price']?></em></p>
        </a>
      </li>    
    <?php endforeach;?>
    </ul>
  </div>
</div>
<?php endforeach;?>

<script type="text/javascript">
<!--
$(function(){
	$('.dmore').bind('click',function(){
		  alert('more');
		  return false;
	});
});
//-->
</script>

<?php include T($tpl_footer);?>
