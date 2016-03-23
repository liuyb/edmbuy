!function(){
	var devWidth = window.screen.width;
	var isSmallDev = devWidth<=800;
	if(isSmallDev){
		var href = location.href; 
		if(!/[&?]m=([^&]+|$)/.exec(href)){
			location.href += (~href.indexOf('?')?'&':'?')+'m=1';
		}
	}
}();