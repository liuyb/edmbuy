<?php add_css('tgd.css',['scope'=>'module','mod'=>'activity','pos'=>'current']);?>
<div id="tgd">
	<span id="tgd_share">
			<img src="<?=$context?>/img/tgd_1.jpg" alt="">
	</span>
	<div id="w90">		
		<ul id="tgd_ul" class="clearfix bbsizing">
			<li for="lg"><span >给老公</span></li>
			<li for="lm"><span class="active_giveto">给老妈</span></li>
			<li for="dd"><span>给搭档</span></li>
			<li for="gm"><span>给闺蜜</span></li>
			<li for="ss"><span>给上司</span></li>
			<li for="er"><span>给儿子</span></li>
		</ul>
		<div class="giveto_wish" id="lg" style="display:none">
			给老公：真高兴能和你一起生活，二次投胎眼光还是准的。
		</div>
		<div class="giveto_wish" id="lm" style="">
			给老妈： 妈妈：“各自努力，好好生活！”这话小时候您常跟我说，现在终于懂了，我也非常认同。把自己的事情做好，我在非常努力了，希望我们都做得更好！
		</div>
		<div class="giveto_wish" id="dd" style="display:none">
			给搭档： 我们既是分享秘密的朋友也是并肩做战的同仁。没有比你更懂我的朋友，也没有比你更能让我崩溃的同事。但还是觉得非你不行。
		</div>
		<div class="giveto_wish" id="gm" style="display:none">
			给闺蜜： 想想你我已然交往十年以上，这份感情在身边越来越少了。未来也许我们偶尔才会联系，但是，你已经在我的生命里！
		</div>
		<div class="giveto_wish" id="ss" style="display:none">
			前上司：现在回想那段工作经历，受益良多。对我又有了全新的意义。祝好！也祝您新遇到的下级、上司一切都好！
		</div>
		<div class="giveto_wish" id="er" style="display:none">
			给儿子：苟日新、日日新、又日新，感谢你带来的新体验。
		</div>
		<span id="energy">
			<img src="<?=$context?>/img/tgd_3.jpg" alt="">
			<p>向TA写出你长久以来珍藏在心底的感恩心语吧！</p>
		</span>
		<div id="tgd_wish">
			<span id="wish_logo"><img src="<?=$context?>/img/tgd_4.png" alt=""></span>
			<!--<input type="text">-->
			<textarea id="nodetxt" class="bbsizing" placeholder="请输入分享内容"></textarea>
		</div>
		<input type="button" id="tgd_share_btn" value="点击分享" onclick="return toSendNode(this,true);" />
		<div id="activity_rule">
			<h3 id="rule_title">活动规则</h3>
			<p>即日起至2014年11月29日零点，只要您：</p>
			<p>1、 微信关注福小秘（fxmapp）。 </p>
			<p>2、在朋友圈分享，同时@出您为谁抢红包并完成对其感恩心语的撰写，您@的TA就有机会获得福小秘派发的200现金红包；</p>
			<p>感恩人与受恩人必须同时微信关注福小秘；</p>
			<p>感恩人不可兑奖，只限受恩人；</p>
			<p><i class="xyd"></i>开奖时间：2014年11月29日零点，福小秘将在官网与微信圈同步直接公布中奖名单。核实后直接注入中奖者手机账号。</p>
			<p><i class="xyd"></i>恶意点赞者将视为无效，取消中奖资格。</p>
		</div>
	</div>
</div>
<?php include T($tpl_footer);?>
<script type="text/javascript">
	var nid = '0';
	var baseurl = location.href.match(/(http:\/\/.+?\/+)/)[1];
	$(document).ready(function(){
		$('#tgd_share_btn').attr('callback', 'toSend');

		//
		$('#tgd_ul li').bind('click',function(){
			var rel = $(this).attr('for');
			$('.giveto_wish').hide();
			$('#'+rel).show();
			$('#tgd_ul li span').removeClass('active_giveto');
			$('span',this).addClass('active_giveto');
		});
	});
	//保存编辑的内容
	function toSend(){
		var _this = this;
		var res = {};
		var post_data = {nid:nid,content:''};
		post_data.content = $('#nodetxt').val();
		if(post_data.content==''){
			alert('请输入分享内容');
			$('#nodetxt').focus();
			return false;
		}
		if(typeof _this.running == 'undefined'|| _this.running==0){
			_this.running=1;
		}else{
			return false;
		}
		F.loadingStart();
		$.ajax({url:'/node/save', type:'POST',data:post_data, async:true, dataType:'json', success: function(data){
			F.loadingStop();
			_this.running=0;
		    if(data.flag=='SUC'){
				var nuid = data.data.nuid;
				res.content = post_data.content;
				res.callback = shareSuc2;
				shareSuc2.nuid = nuid;
				res.url = baseurl+'node/show/word/'+nuid;
				//res.url = baseurl+'activity/subject/1';
				//"<?php echo L('appname')?> - 分享红包·感恩致谢"
				//送TA红包，感恩致谢,福小秘感恩祝福语,每个200元哦！快抢吧
				setWxShareData({title:'福小秘送红包，感恩致谢！每个200元哦！快抢吧！',desc:post_data.content, callback:res.callback, 'link':res.url});
				showWxOptionMenu(true);
				toSendNode.target.show();
			}else{
				alert(data.msg);
			}
		},error:function(){
			F.loadingStop();
			_this.running=0;
		}});
		return res;
	}
	function shareSuc2(){
		var _self = shareSuc2;
		$.post('/node/updateShare', {nuid:_self.nuid,nid:nid}, function (data){
			
		}, 'json');
	}
</script>
