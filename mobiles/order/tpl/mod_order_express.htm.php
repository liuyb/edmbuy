<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<style>
<!--
.track{
	margin-top:20px;
}
.track_l{
	margin:0 20px;
	float:left;
}
.track_r{
	width:auto;
  	margin-right: 12px;	
}
.line{
	margin-top:10px;
	border-right:1px solid #d0d0d0;
}
.track_r p{
	font-size:13px;
}
.track_title{
	margin:10px;
}
.track_title p{
	font-size:13px;
	line-height:30px;
}
.tit_b_info{
	border-bottom:1px solid #e6e6e6;
	padding-left:10px;
	line-height:40px;
	font-size:15px;
}
#Mbody{
	background : #ffffff !important;
}
-->
</style>
<script id="forTopnav" type="text/html">
<div class="header">
	物流详情
<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script> 
<?php 
function wk($date1) {
    $datearr = explode("-",$date1);     //将传来的时间使用“-”分割成数组
    $year = $datearr[0];       //获取年份
    $month = sprintf('%02d',$datearr[1]);  //获取月份
    $day = sprintf('%02d',$datearr[2]);      //获取日期
    $hour = $minute = $second = 0;   //默认时分秒均为0
    $dayofweek = mktime($hour,$minute,$second,$month,$day,$year);    //将时间转换成时间戳
    $shuchu = date("w",$dayofweek);      //获取星期值
    $weekarray=array("周日","周一","周二","周三","周四","周五","周六");
    return $weekarray[$shuchu];
}
?>
<div class="track_title">
	<p>订单编号：<?=$order['invoice_no'] ?></p>
	<p>物流公司：<?=$order['shipping_name'] ?></p>
</div>

<div class="tit_b_info">包裹详情</div>

<div class="track">
	<div class="track_l">
		<div class="line"></div>
	</div>
	
	<div class="track_r">
		<?php 
		  if($express):
    		  $json = json_decode($express);
    		  $list = $json->result ? $json->result->list :null;
    		  if($list && count($list) > 0):
    		  $i = 0;
    		  $count = count($list);
    		  $new_exp_arr = array();
		?>
		<?php foreach ($list as $item):?>
		<div style="margin-bottom:30px;" class='track'>
			<p style="margin-bottom:12px;"><?php echo $item->status ?></p>
			<p><?php echo $item->time ?></p>
		</div>
		<?php endforeach;?>
		<?php else: ?>
			暂时还没有物流记录！
			<?php endif;?>
		<?php else : echo "<div style='text-align:center;'>暂时还没有物流记录！</div>"; endif;?>		
	</div>
	
</div>
<script>
$(function(){
	var contentH = $(".track_r").height();
	$(".line").height(contentH);
	$(".track_l").height(contentH + 100);

	var i = 0;
	$(".track_r div.track").each(function(){
		var _top = $(this).offset().top - 44;//header height
		var _setTop = _top + 2;
		var img = "";
		if(i == 0){
			img = "/themes/mobiles/img/gly.png";
		}else{
			img = "/themes/mobiles/img/hsy.png";
		}
		$(".track_l").append('<div style="margin-left: -8px;position: absolute;top:'+_setTop+'px"><img src="'+img+'" style="width:18px;"></div>')
		i++;
	})
})
</script>
<?php endif;?>