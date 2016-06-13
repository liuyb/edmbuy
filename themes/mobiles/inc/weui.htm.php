<?php defined('IN_SIMPHP') or die('Access Denied');?>
<?php add_css($contextpath.'misc/js/ext/weui/weui.min.css',['scope'=>'global','ver'=>'0.4.2']);?>
<!-- weui style -->
<!--[HEAD_CSS]-->
<style>
.weui_mask, .weui_mask_transition, .weui_mask_transparent {z-index: 50000;}
.weui_dialog,.weui_toast {z-index: 50005;}
.weui_dialog_confirm .weui_dialog .weui_dialog_bd {text-align: center;}
.weui_btn_dialog.primary{color: #ff6d14;}
</style>
<!--[/HEAD_CSS]-->
<!--finished toast-->
<script type="text/html" id="tpl_weui_toast_finish">
<div id="weui_toast_finish" class="hide">
  <div class="weui_mask_transparent"></div>
  <div class="weui_toast">
    <i class="weui_icon_toast"></i>
    <p class="weui_toast_content">已完成</p>
  </div>
</div>
</script>
<!-- loading toast -->
<script type="text/html" id="tpl_weui_toast_loading">
<div id="weui_toast_loading" class="weui_loading_toast hide">
    <div class="weui_mask_transparent"></div>
    <div class="weui_toast">
        <div class="weui_loading">
            <div class="weui_loading_leaf weui_loading_leaf_0"></div>
            <div class="weui_loading_leaf weui_loading_leaf_1"></div>
            <div class="weui_loading_leaf weui_loading_leaf_2"></div>
            <div class="weui_loading_leaf weui_loading_leaf_3"></div>
            <div class="weui_loading_leaf weui_loading_leaf_4"></div>
            <div class="weui_loading_leaf weui_loading_leaf_5"></div>
            <div class="weui_loading_leaf weui_loading_leaf_6"></div>
            <div class="weui_loading_leaf weui_loading_leaf_7"></div>
            <div class="weui_loading_leaf weui_loading_leaf_8"></div>
            <div class="weui_loading_leaf weui_loading_leaf_9"></div>
            <div class="weui_loading_leaf weui_loading_leaf_10"></div>
            <div class="weui_loading_leaf weui_loading_leaf_11"></div>
        </div>
        <p class="weui_toast_content">数据加载中</p>
    </div>
</div>
</script>
<!-- confirm dialog -->
<script type="text/html" id="tpl_weui_dialog_confirm">
<div class="weui_dialog_confirm hide" id="weui_dialog_confirm">
    <div class="weui_mask"></div>
    <div class="weui_dialog">
        <div class="weui_dialog_hd"><strong class="weui_dialog_title">确认</strong></div>
        <div class="weui_dialog_bd"></div>
        <div class="weui_dialog_ft">
            <a href="javascript:;" class="weui_btn_dialog default">取消</a>
            <a href="javascript:;" class="weui_btn_dialog primary">确定</a>
        </div>
    </div>
</div>
</script>
<!-- alert dialog -->
<script type="text/html" id="tpl_weui_dialog_alert">
<div class="weui_dialog_alert hide" id="weui_dialog_alert">
    <div class="weui_mask"></div>
    <div class="weui_dialog">
        <div class="weui_dialog_hd"><strong class="weui_dialog_title">提示</strong></div>
        <div class="weui_dialog_bd"></div>
        <div class="weui_dialog_ft">
            <a href="javascript:;" class="weui_btn_dialog primary">好的</a>
        </div>
    </div>
</div>
</script>
<!-- common dialog -->
<script type="text/html" id="tpl_weui_dialog_common">
<div class="weui_dialog_confirm hide" id="weui_dialog_common">
    <div class="weui_mask"></div>
    <div class="weui_dialog">
        <div class="weui_dialog_hd"><strong class="weui_dialog_title">提示</strong></div>
        <div class="weui_dialog_bd"></div>
    </div>
</div>
</script>
<?php add_js($contextpath.'misc/js/ext/weui/weui.js',['pos'=>'current','ver'=>'0.1.1']);?>