<!-- 遮罩层 -->
<div class="mask"></div>

<div class="add_friend agency_tip" style="width:80%;height:auto;left:37%;top:30%;margin:0;"> 
	<div class="add_f_img" style="margin: 0px;"><img src="/themes/mobiles/img/dingtu.png" style="width:100%;height:auto;"></div>
	<div class="agency_tip_info">
	<p>1、在内测期间，推广锁定人脉争取时间优势；</p>
	<p>2、推广收入前20名，可参加上线后的组团营销大赛，收益多多，宣传多多；</p>
	<p>3、推广人脉前50名，系统给您更多的展示机会。</p>
	</div>
	<div class="w_wx_colse close_f"><img src="/themes/mobiles/img/guanbi.png"></div>
</div>

<!-- 加好友弹出框1 -->
<div class="add_friend wxqr_friend"> 
	<div class="add_f_img"><img src="/themes/mobiles/img/ydm.png"></div>
	<div class="add_f_tit">长按加好友，请注明来自：益多米</div>
	<div class="add_f_phone">电话：<a href="#" style="color:#111"></a></div>
	<div class="w_wx_colse close_f"><img src="/themes/mobiles/img/gub.png"></div>
</div>

<!-- 加好友弹出框2 -->
<div class="add_friend1 empty_friend">
	<div class="friend_ts">温馨提示</div>
	<div class="friend_nr">
		推荐人未完善个人信息<br/>
		提醒TA完善信息
	</div>
	<div class="goods_btn">
		<button type="button" class="order_comm friend_yes">马上提醒</button>
		<button type="button" class="order_comm friend_no" style="color:#999;font-weight:normal;">暂不提醒</button>
	</div>
</div>

<!-- 加好友弹出框3 -->
<div class="add_friend2 phone_friend">
	<div style="font-size:18px;color:#111;text-align:center;line-height:64px;"></div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
</div>

<script>
	var addFriend;

	function getAddFriendInstance(){
		if(!addFriend){
			addFriend = new AddFriend();
		}
		return addFriend;
	}

	function AddFriend(){
		this.friendDom = null;
		this.friendDialogId = "friendDialogId";
		//this.initEvent();
	}

	AddFriend.prototype.initEvent = function(){
		//var _THIS = this;
		//$(".close_f,.mask,.friend_no,.w_wx_colse").on("click",_THIS._onCloseEvent.bind(_THIS));
	};

	AddFriend.prototype.showAgencyTips = function(){
		var _THIS = this;
		if($("#"+this.friendDialogId)){
			this.removeFriendDialog();
		}
		this.friendDom = $(".agency_tip");
		this.friendDom = $(this.friendDom.prop('outerHTML'));
		this.friendDom.attr("id", this.friendDialogId);
		append_to_body(this.friendDom.prop('outerHTML'));
		centerDOM($("#"+this.friendDialogId));
		$("#"+this.friendDialogId).fadeIn(300);
		$(".mask").fadeIn(300);
		$("#"+this.friendDialogId).on("click",".w_wx_colse",_THIS._onCloseEvent.bind(_THIS));
		$(".mask").on('click',_THIS._onCloseEvent.bind(_THIS));

		
	}
	
	AddFriend.prototype.showFriend = function(uid, wxqr, phone){
		var _THIS = this;
		if($("#"+this.friendDialogId)){
			this.removeFriendDialog();
		}
		var _wxqr = wxqr;
		var _phone = phone;
		if(_wxqr && _wxqr.length){
			this.friendDom = $(".wxqr_friend");
				//$(".wxqr_friend").find(".add_f_img").find("img").attr("src",_wxqr);
			if(_phone){
				$(".wxqr_friend").find(".add_f_phone").find("a").text(_phone).attr("href",'tel:'+_phone);
			}else{
				$(".wxqr_friend").find(".add_f_phone").hide();
			}
		}else if(_phone){
			this.friendDom = $(".phone_friend");
			$(".phone_friend").find("div").first().text("电话："+_phone);
		}else{
			this.friendDom = $(".empty_friend");
		}
		this.friendDom = $(this.friendDom.prop('outerHTML'));
		this.friendDom.attr("id", this.friendDialogId);
		append_to_body(this.friendDom.prop('outerHTML'));
		if (uid) {
			$("#"+this.friendDialogId+" .friend_yes").attr('data-uid',uid);
		}
		$("#"+this.friendDialogId).fadeIn(300);
		$(".mask").fadeIn(300);
		$("#"+this.friendDialogId).on("click",".friend_yes,.friend_no,.w_wx_colse",_THIS._onCloseEvent.bind(_THIS));
		$(".mask").on('click',_THIS._onCloseEvent.bind(_THIS));
	};

	AddFriend.prototype._onCloseEvent = function(e){
		_target = $(e.target);
		if (_target.hasClass('friend_yes')) {
			var uid = _target.attr('data-uid');
			var _THIS = this;
			F.post("<?php echo U('user/notify_profile')?>",{"user_id": uid},function(){
				$("#"+_THIS.friendDialogId).hide();
				$(".mask").hide();
				_THIS.removeFriendDialog();
				weui_alert('已成功通知!');
			});
		}
		else {
			$("#"+this.friendDialogId).fadeOut(300);
			$(".mask").fadeOut(300);
			this.removeFriendDialog();
		}
	};

	AddFriend.prototype.removeFriendDialog = function(){
		$("#"+this.friendDialogId).remove();
		$("#"+this.friendDialogId).off("click");
	};

	function centerDOM(obj){
		var screenWidth = $(window).width(), screenHeight = $(window).height();  //当前浏览器窗口的 宽高
	 	var scrolltop = $(document).scrollTop();//获取当前窗口距离页面顶部高度
	 	var objLeft = (screenWidth - obj.width())/2 ;
	 	var objTop = (screenHeight - obj.height())/2 + scrolltop;
	 	obj.css({left: objLeft + 'px', top: objTop + 'px'});
	}
</script>
