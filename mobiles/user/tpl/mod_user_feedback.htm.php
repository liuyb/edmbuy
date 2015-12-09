<?php defined('IN_SIMPHP') or die('Access Denied');?>
<div class="uc-feedback">
<form name="frm_feedback" id="frm_feedback" action="<?php echo U('user/feedback')?>" method="post">
  <h2>我的想法和建议：</h2>
  <p><textarea name="content" id="frm_content" class="bbsizing"></textarea></p>
  <h2>联系邮箱(可选)：</h2>
  <p><input name="contact" id="frm_contact" type="text" class="inptxt" /></p>
  <p><input name="submit" type="submit" class="btn btn-orange" id="frm_submit" value="发送反馈" /><a class="ret" href="<?php echo U('user')?>">返回</a></p>
</form>
</div>
<?php include T($tpl_footer);?>	
<script>
$(function(){
	$('#frm_feedback').bind('submit', function(){

		var _btn = $('#frm_submit');
		_btn.val('发送中...').attr('disabled',true);
		
		var post_data = {content:'',contact:''};
		post_data.content = $('#frm_content').val().trim();
		post_data.contact = $('#frm_contact').val().trim();
		if(post_data.content==''){
			alert('请输入您的意见');
			$('#frm_content').focus();
			return false;
		}

		$.post('<?php echo U('user/feedback')?>', post_data, function(ret){
			_btn.val('发送完成').removeAttr('disabled');
			if(ret.flag=='SUC'){
				alert('您的意见我们已经收到，感谢您的反馈');
				window.location.href = ret.backurl;
			}else{
				_btn.val('发送反馈');
				alert(ret.msg);
			}
		}, 'json');

		return false;
	});
});
</script>