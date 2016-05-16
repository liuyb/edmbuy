<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>
<style>
<!--
.package-status .status-box {
    position: relative
}
.package-status .status-list {
    margin-top: 0
}

.package-status .status-info {
    background: 0 0
}

.package-status .status-list .latest {
    border: none;
	margin-bottom:20px;
}

.package-status .status-list li {
    margin-bottom: -2px
}

.package-status {
    padding: 18px 0 0 0
}

.package-status .status-list {
    margin: 0;
    padding: 0;
    margin-top: -9px;
    padding-left: 10px;
    list-style: none;
    font-size: 12px
}

.package-status .status-list li {
    height: 50px;
    border-left: 1px solid #d9d9d9
}

.package-status .status-list li:before {
    content: '';
    border: 3px solid #f3f3f3;
    background-color: #d9d9d9;
    display: inline-block;
    width: 5px;
    height: 5px;
    border-radius: 5px;
    margin-left: -6px;
    margin-right: 10px;
	margin-top:10px;
}

.package-status .status-list .date {
    font-weight: 700;
    margin-right: 8px;
    font-family: Arial
}

.package-status .status-list .time {
    margin-right: 18px;
    margin-left: 5px
}

.package-status .status-list .week {
    font-weight: 700
}

.package-status .status-list .latest:before {
    background-color: #fe4300;
    border-color: #f8e9e4
}

.package-status .status-list em {
    color: #ff4207
}

.package-status .status-list .hidden {
    display: inline
}

.package-status .status-box {
    overflow: hidden
}

.package-status .status-action {
    text-align: right;
    color: #ff4200;
    padding-right: 10px
}

.package-status .status-action .action-handler {
    cursor: pointer
}

.package-status .status-info {
    margin-top: -10px;
    margin-left: 22px;
    margin-right: 10px;
    background-color: #f3f3f3;
    overflow: hidden;
    line-height: 28px;
    color: #959595;
    text-align: right
}

.package-status .status-info .info-inner {
    margin-top: 20px;
    margin-left: 18px;
    border-top: 1px solid #e8e8e8;
    height: 28px
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
<div style="background-color: #fff;padding:20px;">
	<div style="font-weight:bold;">
		运单号码：<?=$express['invoice_no'] ?>
	</div>
	<div style="font-weight:bold;">
		物流公司：<?=$express['shipping_name'] ?>
	</div>
	<div style="text-align: center;font-weight:bold;margin-top:10px;margin-bottom:10px;">包裹详情</div>
	<div class="package-status">
		<?php 
		  if($express['express_trace']):
    		  $json = json_decode($express['express_trace']);
    		  $list = $json->result ? $json->result->list :null;
    		  if($list && count($list) > 0):
    		  $i = 0;
    		  $count = count($list);
    		  $new_exp_arr = array();
		?>
		<div class="status-box" id="status_list">
    		<ul id="J_listtext2" class="status-list">
    			<?php foreach ($list as $item):?>
    				<?php 
    				    $time = explode(" ", $item->time);
    				    $date = $time[0];
    				    $new_exp_arr[date("Y-m-d H:i:s",strtotime($item->time))] = array("date" => $date,"time"=>$time[1], "week" => wk($date), "status" => $item->status);
    				  endforeach;
    				  ksort($new_exp_arr);
    				  foreach ($new_exp_arr as $item => $val):
    				    $i++;
    				    $licls = "";
    				    if($i == $count){
    				        $licls = "latest";
    				    }
    				?>
    				<li class="<?=$licls ?>"><span class="date"><?=$val['date'] ?></span><span class="week"><?=$val['week'] ?></span>
        			<span class="time"><?=$val['time'] ?></span><span class="text"><?=$val['status'] ?></span></li>
    			<?php endforeach;?>
    		</ul>
		</div>
			<?php else: ?>
			暂时还没有物流记录！
			<?php endif;?>
		<?php else : echo "暂时还没有物流记录！"; endif;?>		
	</div>
</div>
<script>

</script>
<?php endif;?>