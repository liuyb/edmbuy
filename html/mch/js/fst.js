/*!
 * page view statistics 
 * 
 * !Notice: for getting better flash player version, including swfobject.js v2.2 before this script.
 * 
 * Support foreign global variables setting:
 * window.FST_host = 'st.8558.com';
 * window.FST_cookiedomain = '.tg08.com';
 * 
 * @author Gavin<laigw.vip@gmail.com>
 */
if (typeof(FST)=='undefined') var FST = new Object();
if (typeof(FST_host)=='undefined')         var FST_host         = ''; //host domain, can change
if (typeof(FST_cookiedomain)=='undefined') var FST_cookiedomain = ''; //cookie domain, can change

(function(fst, w, undefined){
"use strict";

var UNDEF = "undefined",
	SHOCKWAVE_FLASH = "Shockwave Flash",
	SHOCKWAVE_FLASH_AX = "ShockwaveFlash.ShockwaveFlash",
	doc = w.document,
	scr = w.screen,
	nav = w.navigator;

if (typeof(fst.retention_start)==UNDEF) fst.retention_start = (new Date()).getTime();
if (typeof(fst.retention_end)==UNDEF) fst.retention_end = fst.retention_start;
if (typeof(fst.lasttime)==UNDEF) fst.lasttime = fst.retention_start;
if (typeof(fst.host)==UNDEF) fst.host = FST_host;
if (typeof(fst.cookiedomain)==UNDEF) fst.cookiedomain = FST_cookiedomain;
	
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
var _browserWidth = function() {
	return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
};
//Get browser height
var _browserHeight = function() {
	return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;	
};
//Generate UUID
var _uuid = (function() { var a = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz".split(""); return function(b, f) { var h = a, e = [], d = Math.random; f = f || h.length; if (b) { for (var c = 0; c < b; c++) { e[c] = h[0 | d() * f]; } } else { var g; e[8] = e[13] = e[18] = e[23] = "-"; e[14] = "4"; for (var c = 0; c < 36; c++) { if (!e[c]) { g = 0 | d() * 16; e[c] = h[(c == 19) ? (g & 3) | 8 : g & 15]; } } } return e.join("").toLowerCase(); }; })();
//Get query string
var _getQueryString = function(name) {
  var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
  var r = w.location.search.substr(1).match(reg);
  if(r!=null) return  unescape(r[2]);
  return null;
};
//Simulating jQuery some method
var $ = {
	extend: function(){
		for(var i=1; i<arguments.length; i++)
		    for(var key in arguments[i])
		        if(arguments[i].hasOwnProperty(key))
		            arguments[0][key] = arguments[i][key];
		return arguments[0];
	},
	isFunction: function(obj){
		return typeof(obj)==='function'?true:false;
	},
	jsonParamName: 'jsoncb', //JSONP callback parameter name
	getJSONP: function(url, params, callback){
		if (typeof (params)=='function') {
			callback = params;
		    params = {};
		}
	    var paramsUrl = "",jsonp = this.genCallbackName();
	    params[this.jsonParamName] = jsonp;
	    for(var key in params){
	        paramsUrl+=key+"="+encodeURIComponent(params[key])+"&";
	    }
	    url+= (url.indexOf("?")===-1?"?":"&")+paramsUrl+'_='+(new Date()).getTime();
	    window[jsonp] = function(data) {
	        try {
	            delete window[jsonp];
	        } catch(e) {}
	        window[jsonp] = undefined;
	        if (head) {
	            head.removeChild(script);
	        }
	        callback(data);
	    };
	    var head = document.getElementsByTagName('head')[0];
	    var script = document.createElement('script');
	    script.charset = "UTF-8";
	    script.type = "text/javascript";
	    script.src = url;
	    head.appendChild(script);
	    return true;
	},
	imgRequest: function(url, params) {
		var paramsUrl="";
		for(var key in params){
			paramsUrl+=key+"="+encodeURIComponent(params[key])+"&";
		}
		url+= (url.indexOf("?")===-1?"?":"&")+paramsUrl+'_='+(new Date()).getTime();
		
		var img = new Image();  
	    img.src = url;
	    setTimeout(function(){},1000);
	    return true;
	},
	genCallbackName: function() {
		var now = new Date();
		var Y = now.getFullYear();
		var m = now.getMonth()+1;
		var d = now.getDate();
		var H = now.getHours();
		var i = now.getMinutes();
		var s = now.getSeconds();
		if (m<10) m = '0'+m;
		if (d<10) d = '0'+d;
		if (H<10) H = '0'+H;
		if (i<10) i = '0'+i;
		if (s<10) s = '0'+s;
		return 'jsoncb_'+Y+m+d+H+i+s+parseInt(Math.random()*10000);
	},
	getQueryString: function(url) {
	    var result = {},
	        queryString = (url && url.indexOf("?")!=-1 && url.split("?")[1]) || location.search.substring(1),
	        re = /([^&=]+)=([^&]*)/g,
	        m;
	    while (m = re.exec(queryString)) {
	        result[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
	    }
	    return result;
	}
};
//Cookie dealing from jQuery cookie plugin
(function($){

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (value !== undefined && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}
			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};
	
})($);

//Set or Get Cookie
var ckname = 'FSTCID';
if(!$.cookie(ckname)) {
	$.cookie(ckname,_uuid().replace(/\-/g,''),{expires:365*10,path:'/',domain:fst.cookiedomain});
}
fst.uvid = $.cookie(ckname);
//Set fixed properties
fst.autostart = typeof fst.autostart == UNDEF ? 1 : fst.autostart;
fst.props = {
  browserName     : nav.appName,
  browserVersion  : nav.appVersion,
  browserCodeName : nav.appCodeName,
  browserLanguage : (nav.browserLanguage || nav.language).toLowerCase(),
  userAgent       : nav.userAgent,
  osPlatform      : nav.platform,
  cookieEnabled   : (nav.cookieEnabled || ("cookie" in doc && (doc.cookie.length > 0 || (doc.cookie = "test").indexOf.call(doc.cookie, "test") > -1))) ? 1 : 0,
  javaEnabled     : nav.javaEnabled() ? 1 : 0,
  screenWxH       : scr.width + 'x' + scr.height,
  screenColor     : scr.colorDepth,
  screenPixelRatio: w.devicePixelRatio || 0,//0:undefined,1,1.5,2,2.5,3...
  flashVer        : ''
};
if (typeof swfobject == 'object') {
  var _fver = swfobject.getFlashPlayerVersion();
  if(typeof _fver == 'object' && _fver) {
    fst.props.flashVer = ''+_fver.major+'.'+_fver.minor+'.'+_fver.release;
  }
}else{
  fst.props.flashVer = _getFlashVersion();
};

//Public functions
if (typeof fst.url == UNDEF) {
  fst.url = function(suffix) {
    if (typeof(suffix)==UNDEF) suffix = '.js';
    return ((typeof(this.host)==UNDEF || this.host=='') ? '' : ('http://'+this.host)) + '/fst' + suffix;
  };
};
if (typeof fst.post_status == UNDEF) {
  fst.post_status = function(new_uid, new_status) {
	var oThis = this;
    if (typeof(new_uid)==UNDEF) {
      new_uid = oThis.uid;
    }
    if (typeof(new_status)==UNDEF) {
      new_status = oThis.status;
    }
    $.getJSONP(oThis.url(),{act:'poststatus',vid:fst.vid,uid:new_uid,status:new_status},function(vdata){
    	oThis.uid = vdata.uid;
    	oThis.status = vdata.status;
    });
  };
};
if (typeof fst.retention_stat == UNDEF) {
	fst.retention_stat = function() {
		var oThis = this;
		oThis.retention_end = (new Date()).getTime();
		var params = {act:'retention_stat',vid:oThis.vid,rt:(oThis.retention_end-oThis.retention_start)};
		$.imgRequest(oThis.url('.gif'),params);
	};
};
if (typeof fst.onload_stat == UNDEF) {
	fst.onload_stat = function() {
		var oThis = this;
		var onload_t = (new Date()).getTime() - oThis.retention_start;
		var params = {act:'onload_stat',vid:oThis.vid,t:onload_t};
		$.imgRequest(oThis.url('.gif'),params);
	};
};
w.onbeforeunload = function() {
	if (fst.autostart) {
		fst.retention_stat();
	}
};
w.onload = function() {
	if (fst.autostart) {
		fst.onload_stat();
	}
};

if (typeof fst.start == UNDEF) {
  fst.start = function() {
	var oThis = this;
	
	//Support foreign variables and function:
	//FST.vid, FST.uid, FST.cflag1, FST.cflag2, FST.cflag3, FST.status, FST.post_status, FST.callback
	if (typeof oThis.vid == UNDEF) oThis.vid = 0;
	if (typeof oThis.uid == UNDEF) oThis.uid = 0;
	if (typeof oThis.cflag1 == UNDEF) oThis.cflag1 = '';
	if (typeof oThis.cflag2 == UNDEF) oThis.cflag2 = '';
	if (typeof oThis.cflag3 == UNDEF) oThis.cflag3 = '';
	if (typeof oThis.status == UNDEF) oThis.status = 'N';
	oThis.retention_start = (new Date()).getTime();
	if (!oThis.autostart) {
		//Every new request, stat the previous request retention time
		if (oThis.vid>0) {
			oThis.retention_stat(oThis.lasttime);
		}
		oThis.lasttime = oThis.retention_start;
	}
	
	//Client info
	var _spm = _getQueryString('spm');
	_spm = _spm ? _spm : '';
	var _data = {
	  lo : w.location.href,
	  bn : oThis.props.browserName,
	  bv : oThis.props.browserVersion,
	  bc : oThis.props.browserCodeName,
	  bl : oThis.props.browserLanguage,
	  ua : oThis.props.userAgent,
	  op : oThis.props.osPlatform,
	  ck : oThis.props.cookieEnabled,
	  jv : oThis.props.javaEnabled,
	  fl : oThis.props.flashVer,
	  sw : oThis.props.screenWxH,
	  sc : oThis.props.screenColor,
	  sp : oThis.props.screenPixelRatio,//0:undefined,1,1.5,2,2.5,3...
	  so : _browserHeight() > _browserWidth() ? 1 : 2,//1:portrait,2:landscape
	  wo : w.orientation || 0,//0,90,-90,180...
	  rf : doc.referrer ? doc.referrer : '',
	  uv : !!oThis.uvid ? oThis.uvid : '',
	  ud : oThis.uid,
	  pm : _spm,
	  c1 : oThis.cflag1,
	  c2 : oThis.cflag2,
	  c3 : oThis.cflag3
	};
	
	if (typeof w.F=='object' && typeof w.F.location!=UNDEF && typeof w.F.location.hashreq!=UNDEF && w.F.location.hashreq) {
	  _data.lo = w.F.location.href;
	  _data.rf = w.F.getHashReferUrl();
	}

	//~ post data
	$.getJSONP(oThis.url(),_data,function(ret){
	  oThis.vid = ret.vid;
	  oThis.retention_start = (new Date()).getTime();
	  if (typeof oThis.callback == 'function') {
		  oThis.callback(oThis.vid, oThis.uid, oThis.cflag1, oThis.cflag2, oThis.cflag3);
	  };
	});
  };
};

if (fst.autostart) fst.start();

})(FST, this);