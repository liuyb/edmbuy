<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php if(in_array('blk_1', $show_blk)):?>

<script>gData.referURI='/';</script>
<!-- 
<div class="mainb">
  <div class="swipe">
    <ul id="slider" class="slider">
      <li style="display:block"><a href="#/news/detail/<?=$recommend_news['nid']?>"><img src="misc/images/activity/12.jpg" alt="" /></a></li>
      <li><img src="misc/images/zfy/banner.jpg" alt="" /></li>
      <li><img src="misc/images/zfy/banner.jpg" alt="" /></li>
    </ul>
    <div id="pagenavi" class="pagenavi clearfix">
      <a href="javascript:void(0);" class="active">1</a>
      <a href="javascript:void(0);">2</a>
      <a href="javascript:void(0);">3</a>
     </div>
  </div>
</div>
<script type="text/javascript">
var t2;
$(function(){
	var _active = 0,$_ap = $('#pagenavi a');
  
  t2 = new TouchSlider({
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
      t2.slide(index);
      return false;     
    });
  });
  
  setTimeout(function(){t2.resize();},500);
});
</script>
-->
<?php endif;?>

<?php foreach($list as $it):?>
<div class="active_b">
	<dl class="active_main bbsizing clearfix" onclick="window.location.href='#/activity/detail/<?=$it['aid']?>'" style="border:1px solid #e9e9e9">
    	<dt style="width:100%;"><a href="#/activity/detail/<?=$it['aid']?>"><img src="<?=$it['img']?>" /></a></dt>
    	<!-- 
      <dd><?=$it['title']?></dd>
      <dd class="time"><?php if(!$it['start_time']){ echo '即日起'; }else{ echo date('Y-m-d', $it['start_time']); }?> 至 <?php if (!$it['end_time']) { echo '不限'; } else { echo date('Y-m-d', $it['end_time']); }?></dd>
      <dd class="look"><a href="#/activity/detail/<?=$it['aid']?>">查看活动规则&gt;&gt;</a></dd>
      <dd class="user">已参与用户(<?=$it['jNum']?>)</dd>
      <dd class="userphoto" style="display:none;"><?php foreach($it['jList'] as $j):?><a href="javascript:void(0);" class="userimg"><img src="<?=$j['logo']?>" /></a><?php endforeach;?></dd>
      <dd class="user"><?php echo date('Y-m-d H:i:s', $it['created']);?> 火热进行中</dd>
      -->
    </dl>
</div>
<?php endforeach;?>

<?php if(in_array('blk_1', $show_blk)):?>
<?php if($total_page>=$next_page):?>
<div class="more" data-next-page="<?=$next_page?>" data-total-page="<?=$total_page?>" onclick="see_more(this,showMore)" style="padding:20px 0 30px;">更多</div>
<?php endif;?>

<?php include T($tpl_footer);?>
<script>
//更多，返回数据的显示位置
function showMore(data){
  $('.active_b').last().after(data);
}
</script>
<?php endif;?>