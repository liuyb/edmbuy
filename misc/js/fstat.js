/*!
 * page view statistics 
 * 
 * !Notice: for getting better flash player version, including swfobject.js v2.2 before this script.
 * @author Gavin<laigw.vip@gmail.com>
 */
if (typeof(FST)=='undefined') var FST = new Object();
if (typeof(jQuery)!='function') throw new Error('jQuery is not defined');

(function(FST, $, w, undefined){
"use strict";

var UNDEF = "undefined",
	SHOCKWAVE_FLASH = "Shockwave Flash",
	SHOCKWAVE_FLASH_AX = "ShockwaveFlash.ShockwaveFlash",
	doc = w.document,
	scr = w.screen,
	nav = w.navigator;
	
//Get flash version
var _getFlashVersion = function() {
	var n=nav;
	var i=0, a='', f='';
	if (n.plugins && n.plugins.length) {
		for (i=0;i<n.plugins.length;i++) {
			if (n.plugins[i].name.indexOf(SHOCKWAVE_FLASH)!=-1) {
				a=n.plugins[i].description.split(' ');
				f=a[2]+'.'+a[3].replace('r','');
				break;
			}
		}
	} else if (w.ActiveXObject) {
		try {
			var a = new ActiveXObject(SHOCKWAVE_FLASH_AX);
			if (a) { // a will return null when ActiveX is disabled
				d = a.GetVariable("$version");
				if (d) {
					d = d.split(" ")[1].split(",");
					f = ''+parseInt(d[0], 10)+'.'+parseInt(d[1], 10)+'.'+parseInt(d[2], 10);
				}
			}
		}
		catch(e) {}
	}
	return f;
};
//Get browser width
var _browseWidth = function() {
	return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
};
//Get browser height
var _browseHeight = function() {
	return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;	
};
//jQuery extension
$.getJSONP = function(url, data, cb) {
  if (typeof (data)=='function') {
    cb = data;
    data = {};
  }
  var c = url.lastIndexOf('?') == -1 ? '?' : '&';
  url += c + 'jsoncb=?';
  $.getJSON(url, data, function(ret){
    cb(ret);
  });
};

//Set fixed properties
FST.autostart = typeof FST.autostart == UNDEF ? 1 : FST.autostart;
if (typeof w.CONTEXT_PATH == UNDEF) w.CONTEXT_PATH = '/';
FST.props = {
  browserName     : nav.appName,
  browserVersion  : nav.appVersion,
  browserCodeName : nav.appCodeName,
  browserLanguage : (nav.browserLanguage || nav.language).toLowerCase(),
  userAgent       : nav.userAgent,
  osPlatform      : nav.platform,
  cookieEnabled   : (nav.cookieEnabled || ("cookie" in doc && (doc.cookie.length > 0 || (doc.cookie = "test").indexOf.call(doc.cookie, "test") > -1))) ? 1 : 0,
  javaEnabled     : nav.javaEnabled() ? 1 : 0,
  screenWidth     : scr.width,
  screenHeight    : scr.height,
  screenColor     : scr.colorDepth,
  screenPixelRatio: w.devicePixelRatio || 0,//0:undefined,1,1.5,2,2.5,3...
  flashVer        : '',
  url             : w.CONTEXT_PATH + 'fstat.php'
};
if (typeof swfobject == 'object') {
  var _fver = swfobject.getFlashPlayerVersion();
  if(typeof _fver == 'object' && _fver) {
    FST.props.flashVer = ''+_fver.major+'.'+_fver.minor+'.'+_fver.release;
  }
}else{
  FST.props.flashVer = _getFlashVersion();
};

//Public functions
if (typeof FST.post_status == UNDEF) {
  FST.post_status = function(new_uid, new_status) {
    if (typeof(new_uid)==UNDEF) {
      new_uid = FST.uid;
    }
    if (typeof(new_status)==UNDEF) {
      new_status = FST.status;
    }
    $.getJSONP(FST.props.url,{act:'poststatus',vid:FFST.vid,uid:new_uid,status:new_status},function(vdata){
      FST.uid = vdata.uid;
      FST.status = vdata.status;
    });
  }
};
if (typeof FST.start == UNDEF) {
  FST.start = function() {
	//Support foreign variables and function:
	//FST.vid, FST.uid, FST.cflag1, FST.cflag2, FST.cflag3, FST.status, FST.post_status, FST.callback
	if (typeof FST.vid == UNDEF) FST.vid = 0;
	if (typeof FST.uid == UNDEF) FST.uid = 0;
	if (typeof FST.cflag1 == UNDEF) FST.cflag1 = '';
	if (typeof FST.cflag2 == UNDEF) FST.cflag2 = '';
	if (typeof FST.cflag3 == UNDEF) FST.cflag3 = '';
	if (typeof FST.status == UNDEF) FST.status = 'N';
	
	//Client info
	var _data = {
	  location        : w.location.href,
	  browserName     : FST.props.browserName,
	  browserVersion  : FST.props.browserVersion,
	  browserCodeName : FST.props.browserCodeName,
	  browserLanguage : FST.props.browserLanguage,
	  userAgent       : FST.props.userAgent,
	  osPlatform      : FST.props.osPlatform,
	  cookieEnabled   : FST.props.cookieEnabled,
	  javaEnabled     : FST.props.javaEnabled,
	  flashVersion    : FST.props.flashVer,
	  screenWidth     : FST.props.screenWidth,
	  screenHeight    : FST.props.screenHeight,
	  screenColor     : FST.props.screenColor,
	  screenPixelRatio: FST.props.screenPixelRatio,//0:undefined,1,1.5,2,2.5,3...
	  screenOrientation:_browseHeight() > _browseWidth() ? 1 : 2,//1:portrait,2:landscape
	  winOrientation  : w.orientation || 0,//0,90,-90,180...
	  referrer        : doc.referrer ? doc.referrer : '',
	  uid             : FST.uid,
	  cflag1          : FST.cflag1,
	  cflag2          : FST.cflag2,
	  cflag3          : FST.cflag3
	};
	
	if (typeof w.F=='object' && typeof F.location.hashreq!=UNDEF && F.location.hashreq) {
	  _data.location = F.location.href;
	  _data.referrer = F.getHashReferUrl();
	}

	//~ post data
	$.getJSONP(FST.props.url,_data,function(ret){
	  FST.vid = ret.vid;
	  if (typeof FST.callback == 'function') {
		  FST.callback(FST.vid, FST.uid, FST.cflag1, FST.cflag2, FST.cflag3);
	  };
	});
  }
};

if (FST.autostart) FST.start();

})(FST, jQuery, this);