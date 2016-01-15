<?php ?>

<?php if($totalnum > $pagesize):?>
<!-- BEGIN pager -->
<div class="paging" data-qurl="/partner/list/<?=$level ?>" data-curpage="<?=$curpage?>" data-maxpage="<?=$maxpage?>">
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
<?php endif;?>
  
<script type="text/javascript">
function gopage(obj) {
	if ($(obj).hasClass('disable')) {
		return;
	}
	var _parent = $(obj).parent();
	var curpage = myParseInt(_parent.attr('data-curpage'));
	var maxpage = myParseInt(_parent.attr('data-maxpage'));
	var qurl    = _parent.attr('data-qurl');
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
		myAlert('当前仅有一页');
		return;
	}
	else if (p==curpage) {
		if (p==1) {
			myAlert('已经第一页');
		}
		else if (p==maxpage) {
			myAlert('已经最后一页');
		}
		return;
	}
	window.location = qurl+"?curpage="+p;
}
</script>
