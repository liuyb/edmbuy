<html lang="zh-CN">
<head>
<meta charset="utf-8">
<style type="text/css">
.tb_cls {
	width: 90%;margin:20px;font-size:12px;border-collapse: collapse;
}
.tb_cls tr{
	line-height:30px;
}
.tb_cls td{
	border:1px solid #e6e6e6;
	padding-left:5px;
}
</style>
</head>
<body>
<div style="text-align: center;">
<?php 
$root = substr(getcwd(), 0, -9);
require ($root.'/core/init.php');

$request  = new Request();
$response = new Response();

SimPHP::I()->boot();

define('ONE_DAY_TIME', 86400);
define('DAY_SEP', ' ');
define('DAY_BEGIN', DAY_SEP.'00:00:00');
define('DAY_END',   DAY_SEP.'23:59:59');

require 'OverviewBuilder.php';
$overview = new OverviewBuilder();
$tableRow = $overview->genTableRow();
?>

<table class="tb_cls" cellspacing="0" cellpadding="0">
<?php foreach ($tableRow as $label => $bigrow):?>
<tr style="line-height:40px;background-color:#fff6f2;">
<td colspan="7" style="text-align: center;font-weight:bold;"><?php echo $label?></td>
</tr>
<?php 
$i = 0;
foreach ($bigrow as $row):
$i++;
?>
<tr>
<td style="width: 200px;"><?php echo $i?>、<?php echo $row['label']?></td>
<td style="width:100px">截止当前的：</td>
<td><?php echo $row['total']?></td>
<td style="width:60px">一天内：</td>
<td><?php echo $row['day']?></td>
<td style="width:60px">一周内：</td>
<td><?php echo $row['week']?></td>
<td style="width:60px">一月内：</td>
<td><?php echo $row['month']?></td>
</tr>
<?php 
endforeach;
endforeach;?>
</table>
</div>
</body>
</html>
