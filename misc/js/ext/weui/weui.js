/*!
 * weui logic js
 */
// append html(供内部调用)
function weui_append_html(id) {
	var _id = $('#'+id);
	if (_id.size()==0) {
		$(document.body).append($('#tpl_'+id).html());
		_id = $('#'+id);
	}
	return _id;
}
// render toast(供内部调用)
function weui_render_toast(id, hide_type, tips) { //0:no hide, 1: auto hide, 2: hide immediately
	if (typeof(hide_type)=='undefined') hide_type = 1;
	if (typeof(tips)=='undefined') {
		if ('weui_toast_finish'==id) {
			tips = '已完成';
		}
		else {
			tips = '数据加载中';
		}
	}
	var $id = weui_append_html(id);
	$id.find('.weui_toast_content').text(tips);
	if (2==hide_type) {
		$id.hide();
	}
	else {
		$id.show();
		if (1==hide_type) {
			setTimeout(function(){$id.hide();}, 2000);
		}
	}
}
// render dialog(供内部调用)
function weui_render_dialog(dialog_type, content, title, ok_call, cancel_call) {
	var id  = 'confirm'==dialog_type ? 'weui_dialog_confirm' : 'weui_dialog_alert';
	if (typeof(title)=='undefined') title = '';
	var $id = weui_append_html(id);
	$id.find('.weui_dialog_title').text(title);
	$id.find('.weui_dialog_bd').html(content);

	//ok call
	var ok_call_fn,ok_call_args = {};
	if (typeof(ok_call)=='function') {
		ok_call_fn = ok_call;
	}
	else if (typeof(ok_call)=='object') {
		if (typeof(ok_call.fn)=='function') {
			ok_call_fn = ok_call.fn;
			if (typeof(ok_call.args)=='object') {
				ok_call_args = ok_call.args;
			}
		}
	}
	$id.show().on('click', '.weui_btn_dialog.primary', function(){
		if (typeof(ok_call_fn)=='function') {
			ok_call_fn(ok_call_args);
		}
		$id.off('click').hide();
	});

	//cancel call
	if ('confirm'==dialog_type) {
		var cancel_call_fn,cancel_call_args = {};
		if (typeof(cancel_call)=='function') {
			cancel_call_fn = cancel_call;
		}
		else if (typeof(cancel_call)=='object') {
			if (typeof(cancel_call.fn)=='function') {
				cancel_call_fn = cancel_call.fn;
				if (typeof(cancel_call.args)=='object') {
					cancel_call_args = cancel_call.args;
				}
			}
		}
		$id.on('click', '.weui_btn_dialog.default', function(){
			if (typeof(cancel_call_fn)=='function') {
				cancel_call_fn(cancel_call_args);
			}
			$id.off('click').hide();
	  });
	}
}
// show toast(供外部调用)
function weui_toast(toast_type,hide_type,tips) {
	if (toast_type!='finish' && toast_type!='loading') toast_type = 'loading';
	weui_render_toast('weui_toast_'+toast_type, hide_type, tips);
}
// show alert dialog(供外部调用)
function weui_alert(content, title, ok_call) {
	weui_render_dialog('alert', content, title, ok_call);
}
// show confirm dialog(供外部调用)
function weui_confirm(content, title, ok_call, cancel_call) {
	weui_render_dialog('confirm', content, title, ok_call, cancel_call);
}
