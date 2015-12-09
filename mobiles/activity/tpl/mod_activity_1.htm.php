<?php defined('IN_SIMPHP') or die('Access Denied');?>

<script>gData.referURI='/activity';</script>
<div class="bbsizing info info-jj">
  <article><?=$info['content']?></article>
  <?php if (!empty($relation)):?>
  <div class="relation"><?=$relation?></div>
  <?php endif?>
</div>
<?php include T($tpl_footer);?>