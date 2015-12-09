<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script>gData.referURI='/activity';</script>
<div class="bbsizing info">
  <h2><?=$info['title']?></h2>
  <div class="datetime"><?php if(!$info['start_time']){ echo '即日起'; }else{ echo date('Y-m-d H:i:s', $info['start_time']); }?> 至 <?php if (!$info['end_time']) { echo '不限'; } else { echo date('Y-m-d H:i:s', $info['end_time']); }?></div>
  <article>
  <img src="<?=$info['img']?>" alt="" />
  <?=$info['content']?>
  </article>
  <div class="relation">
    <?=$relation?>
  </div>
</div>

<?php include T($tpl_footer);?>
<script>
var aid = '<?=$info['aid']?>';
$(document).ready(function(){
	
    var foreign_url = '<?=$info['link']?>';
    var is_voted = parseInt('<?=$is_voted?>');
    var $join = $('#join');
    var $vote = $('#vote');

    if(is_voted) $vote.addClass('on');
    else $vote.removeClass('on');
    
    $join.unbind()
    .bind('click', function(e){
        var _this = this;
    	  F.loadingStart();
        if(!$(_this).hasClass('clicking')){
            $(_this).addClass('clicking');
        }else{
            return false;
        }
        $.post('/activity/join',{aid:aid,act:'join'}, function(data){
            $(_this).removeClass('clicking');
            F.loadingStop();
            if(foreign_url!='') window.location.href=foreign_url;
            /*alert(data.msg);*/
        });
    });
    $vote.unbind()
    .bind('click', function(){
        var _this = this;
        F.loadingStart();
        if(!$(_this).hasClass('clicking')){
            $(_this).addClass('clicking');
        }else{
            return false;
        }
        $.post('/activity/join',{aid:aid,act:'vote'}, function(data){
            $(_this).removeClass('clicking');
            F.loadingStop();
            /*alert(data.msg);*/
            F.hashReload();
        })
    });
});
</script>