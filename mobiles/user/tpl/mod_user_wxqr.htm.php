<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header">
	个人信息
<a href="javascript:history.back();" class="back"></a>
</div>
</script>
<script>show_topnav($('#forTopnav').html())</script>

<div style="border-top: 0;">
	<div class="wx_uploading" id="wx_uploading">
		<?php if($wxqr): ?>
			<img id="avatar1" src="<?=$wxqr ?>" style="height:200px;">
		<?php else : ?>
			<img id="avatar1" src="/themes/mobiles/img/tianj.png" style="height:200px;">	
		<?php endif;?>
		<input type="file" id="file" name="file" onchange="fileupload(event)" class="dia_file"> 
		<input type="hidden" id="picUrl" name="picUrl" value="">
	</div>
	
	<div style="text-align:center;margin:20px 0 ">
		<span style="font-size:14px;color:#aaa;">请上传个人二维码</span>
	</div>
</div>

<div class="wx_info">
	<p style="font-size: 18px;padding-bottom: 20px;color: #4a6fdb;">如何保存个人微信二维码？</p>
	<p style="font-size: 15px;color: #333;line-height: 25px;">
		打开“微信” → 点击“我” → 点击“头像” → 选择“我的二维码” → 点击右上角按钮“ ... ” → 保存图片到本地相册。
	</p>
</div>

<script>
function fileupload(e){
    var files = e.target.files;
    var file = files[0];
    var valid = false;
    if (file && file.type && file.type) {
    	var reg = /^image/i;
    	valid = reg.test(file.type);
    }
    if(!valid){
        alert('请选择正确的图片格式上传，如：JPG/JPEG/PNG/GIF ');
		return;
    }
    var fr = new FileReader();
    fr.onload = function(ev) {
    	var img = ev.target.result;
    	F.post('/user/wxqr/update', {img:img}, function(ret){
    		if(ret.flag=='SUC'){
    			$("#wx_uploading").find("img").attr("src",ret.result+'?r='+Math.random());//强制清缓存
    			window.location.href = '/user/setting';
    		}else{
    			alert(ret.errMsg);
    		}
    	});
    };
  	fr.readAsDataURL(file);
}
$(function(){
	$("#activePage > .scrollArea").css('background','#fff')
})
</script>
<?php endif;?>