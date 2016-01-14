<!-- 遮罩层 -->
<div class="mask"></div>

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
		<button type="button" class="order_comm feiend_yes">马上提醒</button>
		<button type="button" class="order_comm friend_no" style="color:#999;font-weight:normal;">暂不提醒</button>
	</div>
</div>

<!-- 加好友弹出框3 -->
<div class="add_friend2 phone_friend">
	<div style="font-size:18px;color:#111;text-align:center;line-height:64px;"></div>
	<div class="w_wx_colse"><img src="/themes/mobiles/img/gub.png"></div>
</div>

<script>
	function AddFriend(wxqr, phone){
		this.wxqr = wxqr;
		this.phone = phone;
		this.friendDom = null;
		this.initEvent();
	}
	
	AddFriend.prototype.initEvent = function(){
		var _THIS = this;
		$(".close_f,.mask,.friend_no,.w_wx_colse").on("click",_THIS._onCloseEvent.bind(_THIS));
	};
	
	AddFriend.prototype.showFriend = function(){
		var _wxqr = this.wxqr;
		var _phone = this.phone;
		if(_wxqr && _wxqr.length){
			this.friendDom = $(".wxqr_friend");
				$(".wxqr_friend").find(".add_f_img").find("img").attr("src",_wxqr);
			if(this.phone){
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
		this.friendDom.fadeIn(300);
		$(".mask").fadeIn(300);
	};
	
	AddFriend.prototype._onCloseEvent = function(){
		if(this.friendDom){
			this.friendDom.fadeOut(300);
			$(".mask").fadeOut(300);
		}
	};
	
</script>