<?php defined('IN_SIMPHP') or die('Access Denied');?>

<?php if(''!==$errmsg):?>

<div class="error"><?=$errmsg?></div>

<?php else:?>

<script id="forTopnav" type="text/html">
<div class="header">
	发表评论
<a href="javascript:goBack();" class="back"></a>
</div>
</script>
<script id="forMnav" type="text/html">
<div class="comm_fb_p">
	发表评论
</div>
</script>
<script>
show_topnav($('#forTopnav').html());
show_mnav($('#forMnav').html());
</script>

<div class="comment_add">
	<div id="post_party_content_placeholder" class="placeholder">请写下对宝贝的感受吧，对他人帮助很大哦~</div>
	<div contenteditable="true" id="post_party_content" class="richtext"></div>
	<div style="float:right;">还可以输入 <span id="remainText">200</span> 个字</div>
	<div class="publish_image" style="margin-top:8px;">
		<div class="dj_index tshop_add">		
			<img src="/themes/mobiles/img/editor_plus.png">		
			<input type="file" name="file" class="dj_file" onchange="fileupload(event);">		
		</div>
		<div class="clear"></div>
	</div>
</div>

<div class="comment_p">
	<div class="p_rank" data-rank="1">
		<div><p class="p_hp_on" id="hp"></p></div>
		<div><p class="p_zp" id="zp"></p></div>
		<div><p class="p_cp" id="cp"></p></div>
		<div class="clear"></div>
	</div>
	<div class="fhsd_xj">
		<p class="fhsd_font">发货速度</p>
		<p>
			<span class="p_1"  data-id="1"></span>
			<span class="p_2"  data-id="2"></span>
			<span class="p_3"  data-id="3"></span>
			<span class="p_4"  data-id="4"></span>
			<span class="p_5"  data-id="5"></span>
		</p>
		<div class="clear"></div>
	</div>
	<div class="fwtd_xj">
		<p class="fwtd_font">服务态度</p>
		<p>
			<span class="n_1"  data-num="1"></span>
			<span class="n_2"  data-num="2"></span>
			<span class="n_3"  data-num="3"></span>
			<span class="n_4"  data-num="4"></span>
			<span class="n_5"  data-num="5"></span>
		</p>
		<div class="clear"></div>
	</div>
</div>

<script>
$(function(){
	//评价等级
	$(".fhsd_xj span").on("click",function(){
		$(".fhsd_xj span").removeClass("red_on");
		var id = $(this).data("id") ;
		$(".fhsd_xj").attr("data-ship",id);
		$(".fhsd_xj span").each(function(){
			var _p = ".p_" + id ;
			$(_p).addClass("red_on");
			++id;
			if(id>5){
				return false;
			}
		});
	});

	$(".fwtd_xj span").on("click",function(){
		$(".fwtd_xj span").removeClass("red_on");
		var num = $(this).data("num") ;
		$(".fwtd_xj").attr("data-service",num);
		$(".fwtd_xj span").each(function(){
			var _n = ".n_" + num;
			$(_n).addClass("red_on");
			++num;
			if(num>5){
				return false;
			}
		});
	});

	//选择评论
	$("#hp").on("click",function(){
		$(".p_rank").attr("data-rank",1);
		$("#hp").addClass("p_hp_on");
		$("#zp").removeClass("p_zp_on");
		$("#cp").removeClass("p_cp_on");
	});
	$("#zp").on("click",function(){
		$(".p_rank").attr("data-rank",2);
		$("#hp").removeClass("p_hp_on").addClass("p_hp");
		$("#zp").addClass("p_zp_on");
		$("#cp").removeClass("p_cp_on");
	});
	$("#cp").on("click",function(){
		$(".p_rank").attr("data-rank",3);
		$("#hp").removeClass("p_hp_on").addClass("p_hp");
		$("#zp").removeClass("p_zp_on");
		$("#cp").addClass("p_cp_on");
	});

	//点击提示语，聚焦
	$("#post_party_content_placeholder").on("click", function(){
		$("#post_party_content").focus();
	});

	$("#post_party_content").on("click", function(){
		$("#post_party_content").focus();
	});
	
	//聚焦
	$("#post_party_content").on("focus", function(){
		$("#post_party_content_placeholder").html("");
	});

	//失焦
	$("#post_party_content").on("blur", function(){
		if($(this).html() == ""){
			var tip = "请写下对宝贝的感受吧，对他人帮助很大哦~";
			$("#post_party_content_placeholder").html(tip);
		}
	});

	setInterval(function(){
		var b = $("#post_party_content").text().length;
		var remainText = $("#remainText");
		if(b != remainText.text().length){
			var num = 200 - b;
			remainText.text(num);
			if(num < 0){
				remainText.css('color','red');
			}else{
				remainText.css('color','#000');
			}
		}
	}, 500);
	
	$(".comm_fb_p").on("click",function(){
		if($(this).attr("data-lock") == 'Y'){
			return;
		}
		submitComment();
	});
});

function fileupload(e, obj){
	var picNum = parseInt($(".publish_img").length);
	if(picNum >= 5){
		boxalert('最多只能添加5张图片哦！');
		return;
	}
    var files = e.target.files;
    var file = files[0];
    var valid = false;
    if (file && file.type && file.type) {
    	var reg = /^image/i;
    	valid = reg.test(file.type);
    }
    if(!valid){
    	boxalert('请选择正确的图片格式上传，如：JPG/JPEG/PNG/GIF ');
		return;
    }
    var fr = new FileReader();
    fr.onload = function(ev) {
    	var img = ev.target.result;
    	F.postWithLoading('/item/comment/image', {img:img}, function(ret){
    		if(ret.flag=='SUC'){
    			//oripath.push(ret.result);
    			//thumbpath.push(ret.thumb);
    			preappendImg(ret.result, ret.thumb, obj);
    		}else{
    			boxalert(ret.errMsg);
    		}
    	});
    };
  	fr.readAsDataURL(file);
}

function preappendImg(img, thumb, obj){
	if(obj){
		var oldDIV = $(obj).closest("div");
		var html =   '<img src="'+thumb+'" data-img="'+img+'">	'
			+' <input type="file" name="file" class="dj_file" onchange="fileupload(event, this);">'
		oldDIV.html(html);
		return;
	}
	var html =   '	<div class="dj_index publish_img">'
		+'		<img src="'+thumb+'" data-img="'+img+'">	'
		+' <input type="file" name="file" class="dj_file" onchange="fileupload(event, this);">'
		+'	</div>';
		
	$(".tshop_add").before(html);
}

function submitComment(){
	var content = $("#post_party_content").text();
	if(!content){
		boxalert('你还没有评论哦！');
		return;
	}
	if(trim(content).length == 0){
		boxalert('你还没有评论哦！');
		return;
	}
	if(content.length > 200){
		boxalert('评论太长啦！');
		return;
	}
	var clevel = $(".p_rank").attr("data-rank");
	clevel = clevel ? clevel : 1;
	var shipping = $(".fhsd_xj").data("ship");
	var service = $(".fwtd_xj").data("service");
	if(shipping){
		shipping = 6 - parseInt(shipping);
	}
	shipping = shipping > 5 ? 5 : shipping;
	if(service){
		service = 6 - parseInt(service);
	}
	service = service > 5 ?　5 : service;
	var oripath = [];
	var thumbpath = [];
	getUploadImgs(oripath, thumbpath);
	var comment_img = "";
	var comment_thumb = "";
	if(oripath.length){
		comment_img = oripath.join(",");
	}
	if(thumbpath.length){
		comment_thumb = thumbpath.join(",");
	}
	var data = {
		goods_id : <?=$goods_id ?>,
	    order_id : <?=$order_id ?>,
	    content : content,
	    comment_img : comment_img,
	    comment_thumb : comment_thumb,
	    comment_level : clevel,
	    shipping_level : shipping,
	    service_level : service
	};
	$(".comm_fb_p").attr("data-lock", "Y").css("opacity",0.2);
	F.post('/item/comment', data, function(ret){
		console.log(ret);
		if(ret == 'SUCC'){
			boxalert('谢谢你的评论！');
    		window.location.href='/trade/order/record?status=finished';
		}else{
			boxalert('评论出现意外，请稍后重试！');
			$(".comm_fb_p").attr("data-lock", "N").css("opacity",1);
		}
	});
}

function getUploadImgs(imgs, thumbs){
	$(".publish_img img").each(function(){
		thumbs.push($(this).attr("src"));
		imgs.push($(this).attr("data-img"));
	});
}

</script>

<?php endif;?>