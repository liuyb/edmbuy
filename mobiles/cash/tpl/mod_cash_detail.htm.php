<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script type="text/html" id="forTopnav">
<div class="header">提现明细<a href="<?=$backurl?>" class="back"></a></div>
</script>
<script type="text/javascript">show_topnav($('#forTopnav').html())</script>

<div class="cashlist_content">

  <?php foreach ($cashing_list AS $cash):?>
  
	<div class="cashlist_item_ydm">
		<div class="cashlist_tit_ydm">
			提现金额：<?=$cash['cashing_amount']?>元
			<?php if(1==$cash['state_type']):?>
			<span class="cashlist_status_ydm cashlist_ing_ydm">提现中</span>
			<?php elseif (2==$cash['state_type']):?>
			<span class="cashlist_status_ydm cashlist_no_ydm">提现失败</span>
			<?php elseif (3==$cash['state_type']):?>
			<span class="cashlist_status_ydm cashlist_yes_ydm">提现成功</span>
			<?php endif;?>
		</div>
		<div class="cashlist_main">
			<table cellspacing="0" cellpadding="0" class="ydm_c_table">
				<tr>
					<td class="cash_td1"><img src="/themes/mobiles/img/tix.png"></td>
					<td class="cash_td2"><img src="/themes/mobiles/img/jian.png"></td>
					<td class="cash_td1">
					<?php if (3==$cash['state_type']):?>
					<img src="/themes/mobiles/img/dzl.png">
					<?php else :?>
					<img src="/themes/mobiles/img/dah.png">
					<?php endif;?>
					</td>
				</tr>
				<tr>
					<td>
						<h1>提现申请</h1>
						<p style="width: 75px;"><?php echo nl2br(date("Y-m-d\nH:i:s",$cash['apply_time']))?></p>
					</td>
					<td>
			<?php if(1==$cash['state_type']):?>
			<span style="font-size:12px;color:#ff0000;">提现审核中</span>
			<?php elseif (2==$cash['state_type']):?>
			<span style="font-size:12px;color:#999;">提现审核失败</span>
			<?php elseif (3==$cash['state_type']):?>
			<span style="font-size:12px;color:#3add47;">提现审核通过</span>
			<?php endif;?>
					</td>
					<td>
						<h1>微信钱包到账</h1>
						<p style="width: 100px;">
			<?php if(1==$cash['state_type']):?>
			预计 <?php echo nl2br(date("Y-m-d\nH:i:s",$cash['apply_time']+86400*2))?>
			<?php elseif (3==$cash['state_type']):?>
			<?php echo nl2br(date("Y-m-d\nH:i:s",$cash['payment_time']))?>
			<?php endif;?>
						</p>
					</td>
				</tr>
			</table>
		</div>
		<?php if(2==$cash['state_type']):?>
		<div class="cashlist_fail_ydm">
			<div class="cashlist_fail_tit" style="color:#ff0000;font-size:12px;margin:5px 0;">
				提现失败详情
			</div>
			<div class="cashlist_fail_detail" style="font-size:14px;color:#333;">
				尊敬的用户，你的提现失败，原因是：<br/><br/><span style="color: #999"><?=$cash['remark']?></span><br/><br/>请排除错误后再试。
			</div>
		</div>
		<?php endif;?>
	</div>
  
  <?php endforeach;?>
  
</div>