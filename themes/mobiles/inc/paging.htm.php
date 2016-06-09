<?php defined('IN_SIMPHP') or die('Access Denied');?>
<!--[HEAD_CSS]-->
<style>
.paging { margin: 15px 8px;overflow: auto;zoom:1;border-radius: 5px;/*-webkit-box-shadow: 0 0 10px rgba(0, 0, 0, .4);-moz-box-shadow: 0 0 10px rgba(0, 0, 0, .4);box-shadow: 0 0 10px rgba(0, 0, 0, .4);*/}
.paging .pgbtn { display: block;float: left;width: 20%;height: 40px;line-height: 40px;text-align: center;background: #fff;color: #333;border: none;padding: 0;margin: 0; }
.paging .pgbtn.disable { color: #ccc; }
</style>
<!--[/HEAD_CSS]-->

<!-- BEGIN pager -->
<div class="paging" data-qurl="<?php echo U('user/prelocked_account','theuid='.$theuid)?>" data-target="#player-list" data-curpage="<?=$curpage?>" data-maxpage="<?=$maxpage?>">
	<a href="javascript:;" rel="begin" class="pgbtn<?php if(1==$curpage){echo ' disable';}?>" onclick="gopage(this)">首页</a>
	<a href="javascript:;" rel="last" class="pgbtn<?php if(1==$curpage){echo ' disable';}?>" onclick="gopage(this)">上一页</a>
	<a href="javascript:;" rel="next" class="pgbtn<?php if($maxpage==$curpage){echo ' disable';}?>" onclick="gopage(this)">下一页</a>
	<a href="javascript:;" rel="end" class="pgbtn<?php if($maxpage==$curpage){echo ' disable';}?>" onclick="gopage(this)">末页</a>
	<select name="pgsel" rel="select" class="pgbtn" onchange="gopage(this)">
	<?php for($i=1; $i<=$maxpage; $i++):?>
		<option value="<?=$i?>"<?php if($i==$curpage):?> selected="selected"<?php endif;?>>&nbsp;<?=$i?>/<?=$maxpage?></option>
	<?php endfor;?>
  </select>
</div>
<!-- END pager -->

<script type="text/javascript">
function gopage(obj) {
	if ($(obj).hasClass('disable')) {
		return;
	}
	var _parent = $(obj).parent();
	var curpage = myParseInt(_parent.attr('data-curpage'));
	var maxpage = myParseInt(_parent.attr('data-maxpage'));
	var qurl    = _parent.attr('data-qurl');
	var target  = _parent.attr('data-target');
	var p = 1, rel = $(obj).attr('rel');
	
	switch(rel) {
	default:
		case 'begin': p = 1;break;
		case 'end':   p = maxpage;break;
		case 'last':  p = curpage-1;p = p > 0 ? p : 1;break;
		case 'next':  p = curpage+1;p = p > maxpage ? maxpage : p;break;
		case 'select': p=$(obj).val();break;
	}
	
	if (maxpage<=1) {
		weui_alert('当前仅有一页');
		return;
	}
	else if (p==curpage) {
		if (p==1) {
			weui_alert('已经第一页');
		}
		else if (p==maxpage) {
			weui_alert('已经最后一页');
		}
		return;
	}
	location.href = qurl+"&p="+p;
	/*
	F.get(qurl+'&p='+p, function(ret){
		$(target).html(ret.body);
	});
	*/
}
</script>