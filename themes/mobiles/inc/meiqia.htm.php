<script>
var isMeiQiaSet = false;
(function(m, ei, q, i, a, j, s) {
    m[a] = m[a] || function() {
        (m[a].a = m[a].a || []).push(arguments)
    };
    j = ei.createElement(q),
    s = ei.getElementsByTagName(q)[0];
    j.async = true;
    j.src = i;
    s.parentNode.insertBefore(j, s)
})(window, document, 'script', '//static.meiqia.com/dist/meiqia.js', '_MEIQIA');
_MEIQIA('entId', '<?=$ent_id ?>');
_MEIQIA('withoutBtn', true);
// 在这里开启手动模式
//_MEIQIA('manualInit', true);
_MEIQIA('allSet', handleAllSet);


/**
 * [美洽网站插件初始化成功了]
 */
function handleAllSet(online) {
    // 传递顾客信息
    _MEIQIA._SENDMETADATA({
    	name: gUser ? gUser['nickname'] : '', 
	    tel : gUser ? gUser['mobile'] : '',
	    gender:gUser ? (gUser['sex'] ? (gUser['sex'] == 1 ? '男' : '女') : '未知') : '未知',
	    comment: gUser ? '多米号:'+gUser['uid'] : ''
    });
    isMeiQiaSet = true;
    $(EventBus).trigger('meiqiaSet', online);
}

function showMEIQIA(){
	if(!isMeiQiaSet){
		return;
	}
	_MEIQIA._SHOWPANEL();
}
</script>