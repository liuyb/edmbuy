/*!
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message Digest Algorithm, as defined in RFC 1321.
 * Version 2.2 Copyright (C) Paul Johnston 1999 - 2009
 * See http://pajhome.org.uk/crypt/md5 for more info.
 */
var hexcase=0;var b64pad="";var chrsz=8;function hex_md5(s){return binl2hex(core_md5(str2binl(s),s.length*chrsz));}function b64_md5(s){return binl2b64(core_md5(str2binl(s),s.length*chrsz));}function str_md5(s){return binl2str(core_md5(str2binl(s),s.length*chrsz));}function hex_hmac_md5(key,data){return binl2hex(core_hmac_md5(key,data));}function b64_hmac_md5(key,data){return binl2b64(core_hmac_md5(key,data));}function str_hmac_md5(key,data){return binl2str(core_hmac_md5(key,data));}function md5_vm_test(){return hex_md5("abc")=="900150983cd24fb0d6963f7d28e17f72";}function core_md5(x,len){x[len>>5]|=0x80<<((len)%32);x[(((len+64)>>>9)<<4)+14]=len;var a=1732584193;var b=-271733879;var c=-1732584194;var d=271733878;for(var i=0;i<x.length;i+=16){var olda=a;var oldb=b;var oldc=c;var oldd=d;a=md5_ff(a,b,c,d,x[i+0],7,-680876936);d=md5_ff(d,a,b,c,x[i+1],12,-389564586);c=md5_ff(c,d,a,b,x[i+2],17,606105819);b=md5_ff(b,c,d,a,x[i+3],22,-1044525330);a=md5_ff(a,b,c,d,x[i+4],7,-176418897);d=md5_ff(d,a,b,c,x[i+5],12,1200080426);c=md5_ff(c,d,a,b,x[i+6],17,-1473231341);b=md5_ff(b,c,d,a,x[i+7],22,-45705983);a=md5_ff(a,b,c,d,x[i+8],7,1770035416);d=md5_ff(d,a,b,c,x[i+9],12,-1958414417);c=md5_ff(c,d,a,b,x[i+10],17,-42063);b=md5_ff(b,c,d,a,x[i+11],22,-1990404162);a=md5_ff(a,b,c,d,x[i+12],7,1804603682);d=md5_ff(d,a,b,c,x[i+13],12,-40341101);c=md5_ff(c,d,a,b,x[i+14],17,-1502002290);b=md5_ff(b,c,d,a,x[i+15],22,1236535329);a=md5_gg(a,b,c,d,x[i+1],5,-165796510);d=md5_gg(d,a,b,c,x[i+6],9,-1069501632);c=md5_gg(c,d,a,b,x[i+11],14,643717713);b=md5_gg(b,c,d,a,x[i+0],20,-373897302);a=md5_gg(a,b,c,d,x[i+5],5,-701558691);d=md5_gg(d,a,b,c,x[i+10],9,38016083);c=md5_gg(c,d,a,b,x[i+15],14,-660478335);b=md5_gg(b,c,d,a,x[i+4],20,-405537848);a=md5_gg(a,b,c,d,x[i+9],5,568446438);d=md5_gg(d,a,b,c,x[i+14],9,-1019803690);c=md5_gg(c,d,a,b,x[i+3],14,-187363961);b=md5_gg(b,c,d,a,x[i+8],20,1163531501);a=md5_gg(a,b,c,d,x[i+13],5,-1444681467);d=md5_gg(d,a,b,c,x[i+2],9,-51403784);c=md5_gg(c,d,a,b,x[i+7],14,1735328473);b=md5_gg(b,c,d,a,x[i+12],20,-1926607734);a=md5_hh(a,b,c,d,x[i+5],4,-378558);d=md5_hh(d,a,b,c,x[i+8],11,-2022574463);c=md5_hh(c,d,a,b,x[i+11],16,1839030562);b=md5_hh(b,c,d,a,x[i+14],23,-35309556);a=md5_hh(a,b,c,d,x[i+1],4,-1530992060);d=md5_hh(d,a,b,c,x[i+4],11,1272893353);c=md5_hh(c,d,a,b,x[i+7],16,-155497632);b=md5_hh(b,c,d,a,x[i+10],23,-1094730640);a=md5_hh(a,b,c,d,x[i+13],4,681279174);d=md5_hh(d,a,b,c,x[i+0],11,-358537222);c=md5_hh(c,d,a,b,x[i+3],16,-722521979);b=md5_hh(b,c,d,a,x[i+6],23,76029189);a=md5_hh(a,b,c,d,x[i+9],4,-640364487);d=md5_hh(d,a,b,c,x[i+12],11,-421815835);c=md5_hh(c,d,a,b,x[i+15],16,530742520);b=md5_hh(b,c,d,a,x[i+2],23,-995338651);a=md5_ii(a,b,c,d,x[i+0],6,-198630844);d=md5_ii(d,a,b,c,x[i+7],10,1126891415);c=md5_ii(c,d,a,b,x[i+14],15,-1416354905);b=md5_ii(b,c,d,a,x[i+5],21,-57434055);a=md5_ii(a,b,c,d,x[i+12],6,1700485571);d=md5_ii(d,a,b,c,x[i+3],10,-1894986606);c=md5_ii(c,d,a,b,x[i+10],15,-1051523);b=md5_ii(b,c,d,a,x[i+1],21,-2054922799);a=md5_ii(a,b,c,d,x[i+8],6,1873313359);d=md5_ii(d,a,b,c,x[i+15],10,-30611744);c=md5_ii(c,d,a,b,x[i+6],15,-1560198380);b=md5_ii(b,c,d,a,x[i+13],21,1309151649);a=md5_ii(a,b,c,d,x[i+4],6,-145523070);d=md5_ii(d,a,b,c,x[i+11],10,-1120210379);c=md5_ii(c,d,a,b,x[i+2],15,718787259);b=md5_ii(b,c,d,a,x[i+9],21,-343485551);a=safe_add(a,olda);b=safe_add(b,oldb);c=safe_add(c,oldc);d=safe_add(d,oldd);}return Array(a,b,c,d);}function md5_cmn(q,a,b,x,s,t){return safe_add(bit_rol(safe_add(safe_add(a,q),safe_add(x,t)),s),b);}function md5_ff(a,b,c,d,x,s,t){return md5_cmn((b&c)|((~b)&d),a,b,x,s,t);}function md5_gg(a,b,c,d,x,s,t){return md5_cmn((b&d)|(c&(~d)),a,b,x,s,t);}function md5_hh(a,b,c,d,x,s,t){return md5_cmn(b^c^d,a,b,x,s,t);}function md5_ii(a,b,c,d,x,s,t){return md5_cmn(c^(b|(~d)),a,b,x,s,t);}function core_hmac_md5(key,data){var bkey=str2binl(key);if(bkey.length>16)bkey=core_md5(bkey,key.length*chrsz);var ipad=Array(16),opad=Array(16);for(var i=0;i<16;i++){ipad[i]=bkey[i]^0x36363636;opad[i]=bkey[i]^0x5C5C5C5C;}var hash=core_md5(ipad.concat(str2binl(data)),512+data.length*chrsz);return core_md5(opad.concat(hash),512+128);}function safe_add(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF);var msw=(x>>16)+(y>>16)+(lsw>>16);return(msw<<16)|(lsw&0xFFFF);}function bit_rol(num,cnt){return(num<<cnt)|(num>>>(32-cnt));}function str2binl(str){var bin=Array();var mask=(1<<chrsz)-1;for(var i=0;i<str.length*chrsz;i+=chrsz)bin[i>>5]|=(str.charCodeAt(i/chrsz)&mask)<<(i%32);return bin;}function binl2str(bin){var str="";var mask=(1<<chrsz)-1;for(var i=0;i<bin.length*32;i+=chrsz)str+=String.fromCharCode((bin[i>>5]>>>(i%32))&mask);return str;}function binl2hex(binarray){var hex_tab=hexcase?"0123456789ABCDEF":"0123456789abcdef";var str="";for(var i=0;i<binarray.length*4;i++){str+=hex_tab.charAt((binarray[i>>2]>>((i%4)*8+4))&0xF)+hex_tab.charAt((binarray[i>>2]>>((i%4)*8))&0xF);}return str;}function binl2b64(binarray){var tab="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";var str="";for(var i=0;i<binarray.length*4;i+=3){var triplet=(((binarray[i>>2]>>8*(i%4))&0xFF)<<16)|(((binarray[i+1>>2]>>8*((i+1)%4))&0xFF)<<8)|((binarray[i+2>>2]>>8*((i+2)%4))&0xFF);for(var j=0;j<4;j++){if(i*8+j*6>binarray.length*32)str+=b64pad;else str+=tab.charAt((triplet>>6*(3-j))&0x3F);}}return str;};

/*!
 * fgnass.github.com/spin.js#v2.1.0
 */
!function(a,b){"object"==typeof exports?module.exports=b():"function"==typeof define&&define.amd?define(b):a.Spinner=b()}(this,function(){"use strict";function a(a,b){var c,d=document.createElement(a||"div");for(c in b)d[c]=b[c];return d}function b(a){for(var b=1,c=arguments.length;c>b;b++)a.appendChild(arguments[b]);return a}function c(a,b,c,d){var e=["opacity",b,~~(100*a),c,d].join("-"),f=.01+c/d*100,g=Math.max(1-(1-a)/b*(100-f),a),h=j.substring(0,j.indexOf("Animation")).toLowerCase(),i=h&&"-"+h+"-"||"";return m[e]||(k.insertRule("@"+i+"keyframes "+e+"{0%{opacity:"+g+"}"+f+"%{opacity:"+a+"}"+(f+.01)+"%{opacity:1}"+(f+b)%100+"%{opacity:"+a+"}100%{opacity:"+g+"}}",k.cssRules.length),m[e]=1),e}function d(a,b){var c,d,e=a.style;for(b=b.charAt(0).toUpperCase()+b.slice(1),d=0;d<l.length;d++)if(c=l[d]+b,void 0!==e[c])return c;return void 0!==e[b]?b:void 0}function e(a,b){for(var c in b)a.style[d(a,c)||c]=b[c];return a}function f(a){for(var b=1;b<arguments.length;b++){var c=arguments[b];for(var d in c)void 0===a[d]&&(a[d]=c[d])}return a}function g(a,b){return"string"==typeof a?a:a[b%a.length]}function h(a){this.opts=f(a||{},h.defaults,n)}function i(){function c(b,c){return a("<"+b+' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',c)}k.addRule(".spin-vml","behavior:url(#default#VML)"),h.prototype.lines=function(a,d){function f(){return e(c("group",{coordsize:k+" "+k,coordorigin:-j+" "+-j}),{width:k,height:k})}function h(a,h,i){b(m,b(e(f(),{rotation:360/d.lines*a+"deg",left:~~h}),b(e(c("roundrect",{arcsize:d.corners}),{width:j,height:d.scale*d.width,left:d.scale*d.radius,top:-d.scale*d.width>>1,filter:i}),c("fill",{color:g(d.color,a),opacity:d.opacity}),c("stroke",{opacity:0}))))}var i,j=d.scale*(d.length+d.width),k=2*d.scale*j,l=-(d.width+d.length)*d.scale*2+"px",m=e(f(),{position:"absolute",top:l,left:l});if(d.shadow)for(i=1;i<=d.lines;i++)h(i,-2,"progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");for(i=1;i<=d.lines;i++)h(i);return b(a,m)},h.prototype.opacity=function(a,b,c,d){var e=a.firstChild;d=d.shadow&&d.lines||0,e&&b+d<e.childNodes.length&&(e=e.childNodes[b+d],e=e&&e.firstChild,e=e&&e.firstChild,e&&(e.opacity=c))}}var j,k,l=["webkit","Moz","ms","O"],m={},n={lines:12,length:7,width:5,radius:10,scale:1,rotate:0,corners:1,color:"#000",direction:1,speed:1,trail:100,opacity:.25,fps:20,zIndex:2e9,className:"spinner",top:"50%",left:"50%",position:"absolute"};if(h.defaults={},f(h.prototype,{spin:function(b){this.stop();var c=this,d=c.opts,f=c.el=e(a(0,{className:d.className}),{position:d.position,width:0,zIndex:d.zIndex});if(e(f,{left:d.left,top:d.top}),b&&b.insertBefore(f,b.firstChild||null),f.setAttribute("role","progressbar"),c.lines(f,c.opts),!j){var g,h=0,i=(d.lines-1)*(1-d.direction)/2,k=d.fps,l=k/d.speed,m=(1-d.opacity)/(l*d.trail/100),n=l/d.lines;!function o(){h++;for(var a=0;a<d.lines;a++)g=Math.max(1-(h+(d.lines-a)*n)%l*m,d.opacity),c.opacity(f,a*d.direction+i,g,d);c.timeout=c.el&&setTimeout(o,~~(1e3/k))}()}return c},stop:function(){var a=this.el;return a&&(clearTimeout(this.timeout),a.parentNode&&a.parentNode.removeChild(a),this.el=void 0),this},lines:function(d,f){function h(b,c){return e(a(),{position:"absolute",width:f.scale*(f.length+f.width)+"px",height:f.scale*f.width+"px",background:b,boxShadow:c,transformOrigin:"left",transform:"rotate("+~~(360/f.lines*k+f.rotate)+"deg) translate("+f.scale*f.radius+"px,0)",borderRadius:(f.corners*f.scale*f.width>>1)+"px"})}for(var i,k=0,l=(f.lines-1)*(1-f.direction)/2;k<f.lines;k++)i=e(a(),{position:"absolute",top:1+~(f.scale*f.width/2)+"px",transform:f.hwaccel?"translate3d(0,0,0)":"",opacity:f.opacity,animation:j&&c(f.opacity,f.trail,l+k*f.direction,f.lines)+" "+1/f.speed+"s linear infinite"}),f.shadow&&b(i,e(h("#000","0 0 4px #000"),{top:"2px"})),b(d,b(i,h(g(f.color,k),"0 0 1px rgba(0,0,0,.1)")));return d},opacity:function(a,b,c){b<a.childNodes.length&&(a.childNodes[b].style.opacity=c)}}),"undefined"!=typeof document){k=function(){var c=a("style",{type:"text/css"});return b(document.getElementsByTagName("head")[0],c),c.sheet||c.styleSheet}();var o=e(a("group"),{behavior:"url(#default#VML)"});!d(o,"transform")&&o.adj?i():j=d(o,"animation")}return h});

/*!
 * FastClick: https://github.com/ftlabs/fastclick#v1.0.3
 * zip by: dean.edwards.name/packer/,Pack+Shrink+Base62
 */
(function(){function e(a,b){function c(a,b){return function(){return a.apply(b,arguments)}}var d;b=b||{};this.trackingClick=!1;this.trackingClickStart=0;this.targetElement=null;this.lastTouchIdentifier=this.touchStartY=this.touchStartX=0;this.touchBoundary=b.touchBoundary||10;this.layer=a;this.tapDelay=b.tapDelay||200;this.tapTimeout=b.tapTimeout||700;if(!e.notNeeded(a)){for(var f="onMouse onClick onTouchStart onTouchMove onTouchEnd onTouchCancel".split(" "),h=0,k=f.length;h<k;h++)this[f[h]]=c(this[f[h]],
this);g&&(a.addEventListener("mouseover",this.onMouse,!0),a.addEventListener("mousedown",this.onMouse,!0),a.addEventListener("mouseup",this.onMouse,!0));a.addEventListener("click",this.onClick,!0);a.addEventListener("touchstart",this.onTouchStart,!1);a.addEventListener("touchmove",this.onTouchMove,!1);a.addEventListener("touchend",this.onTouchEnd,!1);a.addEventListener("touchcancel",this.onTouchCancel,!1);Event.prototype.stopImmediatePropagation||(a.removeEventListener=function(b,c,d){var e=Node.prototype.removeEventListener;
"click"===b?e.call(a,b,c.hijacked||c,d):e.call(a,b,c,d)},a.addEventListener=function(b,c,d){var e=Node.prototype.addEventListener;"click"===b?e.call(a,b,c.hijacked||(c.hijacked=function(a){a.propagationStopped||c(a)}),d):e.call(a,b,c,d)});"function"===typeof a.onclick&&(d=a.onclick,a.addEventListener("click",function(a){d(a)},!1),a.onclick=null)}}var k=0<=navigator.userAgent.indexOf("Windows Phone"),g=0<navigator.userAgent.indexOf("Android")&&!k,f=/iP(ad|hone|od)/.test(navigator.userAgent)&&!k,l=
f&&/OS 4_\d(_\d)?/.test(navigator.userAgent),m=f&&/OS [6-7]_\d/.test(navigator.userAgent),n=0<navigator.userAgent.indexOf("BB10");e.prototype.needsClick=function(a){switch(a.nodeName.toLowerCase()){case "button":case "select":case "textarea":if(a.disabled)return!0;break;case "input":if(f&&"file"===a.type||a.disabled)return!0;break;case "label":case "iframe":case "video":return!0}return/\bneedsclick\b/.test(a.className)};e.prototype.needsFocus=function(a){switch(a.nodeName.toLowerCase()){case "textarea":return!0;
case "select":return!g;case "input":switch(a.type){case "button":case "checkbox":case "file":case "image":case "radio":case "submit":return!1}return!a.disabled&&!a.readOnly;default:return/\bneedsfocus\b/.test(a.className)}};e.prototype.sendClick=function(a,b){var c,d;document.activeElement&&document.activeElement!==a&&document.activeElement.blur();d=b.changedTouches[0];c=document.createEvent("MouseEvents");c.initMouseEvent(this.determineEventType(a),!0,!0,window,1,d.screenX,d.screenY,d.clientX,d.clientY,
!1,!1,!1,!1,0,null);c.forwardedTouchEvent=!0;a.dispatchEvent(c)};e.prototype.determineEventType=function(a){return g&&"select"===a.tagName.toLowerCase()?"mousedown":"click"};e.prototype.focus=function(a){var b;f&&a.setSelectionRange&&0!==a.type.indexOf("date")&&"time"!==a.type&&"month"!==a.type?(b=a.value.length,a.setSelectionRange(b,b)):a.focus()};e.prototype.updateScrollParent=function(a){var b,c;b=a.fastClickScrollParent;if(!b||!b.contains(a)){c=a;do{if(c.scrollHeight>c.offsetHeight){b=c;a.fastClickScrollParent=
c;break}c=c.parentElement}while(c)}b&&(b.fastClickLastScrollTop=b.scrollTop)};e.prototype.getTargetElementFromEventTarget=function(a){return a.nodeType===Node.TEXT_NODE?a.parentNode:a};e.prototype.onTouchStart=function(a){var b,c,d;if(1<a.targetTouches.length)return!0;b=this.getTargetElementFromEventTarget(a.target);c=a.targetTouches[0];if(f){d=window.getSelection();if(d.rangeCount&&!d.isCollapsed)return!0;if(!l){if(c.identifier&&c.identifier===this.lastTouchIdentifier)return a.preventDefault(),!1;
this.lastTouchIdentifier=c.identifier;this.updateScrollParent(b)}}this.trackingClick=!0;this.trackingClickStart=a.timeStamp;this.targetElement=b;this.touchStartX=c.pageX;this.touchStartY=c.pageY;a.timeStamp-this.lastClickTime<this.tapDelay&&a.preventDefault();return!0};e.prototype.touchHasMoved=function(a){a=a.changedTouches[0];var b=this.touchBoundary;return Math.abs(a.pageX-this.touchStartX)>b||Math.abs(a.pageY-this.touchStartY)>b?!0:!1};e.prototype.onTouchMove=function(a){if(!this.trackingClick)return!0;
if(this.targetElement!==this.getTargetElementFromEventTarget(a.target)||this.touchHasMoved(a))this.trackingClick=!1,this.targetElement=null;return!0};e.prototype.findControl=function(a){return void 0!==a.control?a.control:a.htmlFor?document.getElementById(a.htmlFor):a.querySelector("button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea")};e.prototype.onTouchEnd=function(a){var b,c,d=this.targetElement;if(!this.trackingClick)return!0;if(a.timeStamp-this.lastClickTime<
this.tapDelay)return this.cancelNextClick=!0;if(a.timeStamp-this.trackingClickStart>this.tapTimeout)return!0;this.cancelNextClick=!1;this.lastClickTime=a.timeStamp;b=this.trackingClickStart;this.trackingClick=!1;this.trackingClickStart=0;m&&(c=a.changedTouches[0],d=document.elementFromPoint(c.pageX-window.pageXOffset,c.pageY-window.pageYOffset)||d,d.fastClickScrollParent=this.targetElement.fastClickScrollParent);c=d.tagName.toLowerCase();if("label"===c){if(b=this.findControl(d)){this.focus(d);if(g)return!1;
d=b}}else if(this.needsFocus(d)){if(100<a.timeStamp-b||f&&window.top!==window&&"input"===c)return this.targetElement=null,!1;this.focus(d);this.sendClick(d,a);f&&"select"===c||(this.targetElement=null,a.preventDefault());return!1}if(f&&!l&&(b=d.fastClickScrollParent)&&b.fastClickLastScrollTop!==b.scrollTop)return!0;this.needsClick(d)||(a.preventDefault(),this.sendClick(d,a));return!1};e.prototype.onTouchCancel=function(){this.trackingClick=!1;this.targetElement=null};e.prototype.onMouse=function(a){return this.targetElement&&
!a.forwardedTouchEvent&&a.cancelable?!this.needsClick(this.targetElement)||this.cancelNextClick?(a.stopImmediatePropagation?a.stopImmediatePropagation():a.propagationStopped=!0,a.stopPropagation(),a.preventDefault(),!1):!0:!0};e.prototype.onClick=function(a){if(this.trackingClick)return this.targetElement=null,this.trackingClick=!1,!0;if("submit"===a.target.type&&0===a.detail)return!0;a=this.onMouse(a);a||(this.targetElement=null);return a};e.prototype.destroy=function(){var a=this.layer;g&&(a.removeEventListener("mouseover",
this.onMouse,!0),a.removeEventListener("mousedown",this.onMouse,!0),a.removeEventListener("mouseup",this.onMouse,!0));a.removeEventListener("click",this.onClick,!0);a.removeEventListener("touchstart",this.onTouchStart,!1);a.removeEventListener("touchmove",this.onTouchMove,!1);a.removeEventListener("touchend",this.onTouchEnd,!1);a.removeEventListener("touchcancel",this.onTouchCancel,!1)};e.notNeeded=function(a){var b,c;if("undefined"===typeof window.ontouchstart)return!0;if(c=+(/Chrome\/([0-9]+)/.exec(navigator.userAgent)||
[,0])[1])if(g){if((b=document.querySelector("meta[name=viewport]"))&&(-1!==b.content.indexOf("user-scalable=no")||31<c&&document.documentElement.scrollWidth<=window.outerWidth))return!0}else return!0;return n&&(b=navigator.userAgent.match(/Version\/([0-9]*)\.([0-9]*)/),10<=b[1]&&3<=b[2]&&(b=document.querySelector("meta[name=viewport]"))&&(-1!==b.content.indexOf("user-scalable=no")||document.documentElement.scrollWidth<=window.outerWidth))||"none"===a.style.msTouchAction||"manipulation"===a.style.touchAction||
27<=+(/Firefox\/([0-9]+)/.exec(navigator.userAgent)||[,0])[1]&&(b=document.querySelector("meta[name=viewport]"))&&(-1!==b.content.indexOf("user-scalable=no")||document.documentElement.scrollWidth<=window.outerWidth)?!0:"none"===a.style.touchAction||"manipulation"===a.style.touchAction?!0:!1};e.attach=function(a,b){return new e(a,b)};"function"===typeof define&&"object"===typeof define.amd&&define.amd?define(function(){return e}):"undefined"!==typeof module&&module.exports?(module.exports=e.attach,
module.exports.FastClick=e):window.FastClick=e})();

/*!
 * Part of jQuery Migrate - v1.2.1 - 2013-05-08
 * https://github.com/jquery/jquery-migrate
 * zip by: dean.edwards.name/packer/,Pack+Shrink
 */
(function( jQuery, window, undefined ) {
	jQuery.handleError = function( s, xhr, status, e ) {
        // If a local callback was specified, fire it
        if ( s.error )
            s.error( xhr, status, e );
        // If we have some XML response text (e.g. from an AJAX call) then log it in the console
        else if(xhr.responseText)
            console.log(xhr.responseText);
    };
	jQuery.uaMatch = function( ua ) {
		ua = ua.toLowerCase();
	
		var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
			/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
			/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
			/(msie) ([\w.]+)/.exec( ua ) ||
			ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
			[];
	
		return {
			browser: match[ 1 ] || "",
			version: match[ 2 ] || "0"
		};
	};
	// Don't clobber any existing jQuery.browser in case it's different
	if ( !jQuery.browser ) {
		matched = jQuery.uaMatch( navigator.userAgent );
		var browser = {};
	
		if ( matched.browser ) {
			browser[ matched.browser ] = true;
			browser.version = matched.version;
		}
	
		// Chrome is Webkit, but Webkit is also Safari.
		if ( browser.chrome ) {
			browser.webkit = true;
		} else if ( browser.webkit ) {
			browser.safari = true;
		}
	
		jQuery.browser = browser;
	}
})( jQuery, this );

/*!
 * Javascript Cookie v1.5.1
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 * 
 * zip by: dean.edwards.name/packer/,Pack+Shrink
 */
(function (factory) {
	var jQuery;
	if (typeof define === 'function' && define.amd) {
		// AMD (Register as an anonymous module)
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// Node/CommonJS
		try {
			jQuery = require('jquery');
		} catch(e) {}
		module.exports = factory(jQuery);
	} else {
		// Browser globals
		var _OldCookies = window.Cookies;
		var api = window.Cookies = factory(window.jQuery);
		api.noConflict = function() {
			window.Cookies = _OldCookies;
			return api;
		};
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return api.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return api.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(api.json ? JSON.stringify(value) : String(value));
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
			return api.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = api.raw ? s : parseCookieValue(s);
		return isFunction(converter) ? converter(value) : value;
	}

	function extend() {
		var key, options;
		var i = 0;
		var result = {};
		for (; i < arguments.length; i++) {
			options = arguments[ i ];
			for (key in options) {
				result[key] = options[key];
			}
		}
		return result;
	}

	function isFunction(obj) {
		return Object.prototype.toString.call(obj) === '[object Function]';
	}

	var api = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !isFunction(value)) {
			options = extend(api.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
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

		var result = key ? undefined : {},
			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling "get()".
			cookies = document.cookie ? document.cookie.split('; ') : [],
			i = 0,
			l = cookies.length;

		for (; i < l; i++) {
			var parts = cookies[i].split('='),
				name = decode(parts.shift()),
				cookie = parts.join('=');

			if (key === name) {
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

	api.get = api.set = api;
	api.defaults = {};

	api.remove = function (key, options) {
		// Must not alter options, thus extending a fresh object...
		api(key, '', extend(options, { expires: -1 }));
		return !api(key);
	};

	if ( $ ) {
		$.cookie = api;
		$.removeCookie = api.remove;
	}

	return api;
}));

/*!
 * part of jquery mobile
 * See http://jquerymobile.com
 * zip by: dean.edwards.name/packer/,Pack+Shrink
 */
;(function(a,b,c){if(typeof define==="function"&&define.amd){define(["jquery"],function($){c($,a,b);return $.mobile})}else{c(a.jQuery,a,b)}}(this,document,function(k,l,m,n){(function($,b,c){var d="webkitAnimationEnd oAnimationEnd oanimationend animationend msAnimationEnd";var e="webkitTransitionEnd oTransitionEnd otransitionend transitionend msTransitionEnd";$.fn.animationComplete=function(a){if($.support.cssTransitions){return $(this).one(d,a)}else{setTimeout(a,0);return $(this)}};$.fn.transitionComplete=function(a){if($.support.cssTransitions){return $(this).one(e,a)}else{setTimeout(a,0);return $(this)}}})(k,this);(function($){$.mobile={}}(k));(function($,a,b){$.mobile=$.extend($.mobile,{window:$(a),document:$(m),getScreenHeight:function(){return a.innerHeight||$.mobile.window.height()}},$.mobile)})(k,this);(function($,d){l.matchMedia=l.matchMedia||(function(a,b){var c,docElem=a.documentElement,refNode=docElem.firstElementChild||docElem.firstChild,fakeBody=a.createElement("body"),div=a.createElement("div");div.id="mq-test-1";div.style.cssText="position:absolute;top:-100em";fakeBody.style.background="none";fakeBody.appendChild(div);return function(q){div.innerHTML="&shy;<style media=\""+q+"\"> #mq-test-1 { width: 42px; }</style>";docElem.insertBefore(fakeBody,refNode);c=div.offsetWidth===42;docElem.removeChild(fakeBody);return{matches:c,media:q}}}(m));$.mobile.media=function(q){return l.matchMedia(q).matches}})(k);(function($,a){var b={touch:"ontouchend"in m};$.mobile.support=$.mobile.support||{};$.extend($.support,b);$.extend($.mobile.support,b)}(k));(function($,a){$.extend($.support,{orientation:"orientation"in l&&"onorientationchange"in l})}(k));(function($,g){function propExists(a){var b=a.charAt(0).toUpperCase()+a.substr(1),props=(a+" "+vendors.join(b+" ")+b).split(" ");for(var v in props){if(fbCSS[props[v]]!==g){return true}}}var h=$("<body>").prependTo("html"),fbCSS=h[0].style,vendors=["Webkit","Moz","O"],webos="palmGetResource"in l,opera=l.opera,operamini=l.operamini&&({}).toString.call(l.operamini)==="[object OperaMini]",bb=l.blackberry&&!propExists("-webkit-transform");function validStyle(c,d,e){var f=m.createElement('div'),uc=function(a){return a.charAt(0).toUpperCase()+a.substr(1)},vend_pref=function(a){if(a===""){return""}else{return"-"+a.charAt(0).toLowerCase()+a.substr(1)+"-"}},check_style=function(a){var b=vend_pref(a)+c+": "+d+";",uc_vend=uc(a),propStyle=uc_vend+(uc_vend===""?c:uc(c));f.setAttribute("style",b);if(!!f.style[propStyle]){ret=true}},check_vends=e?e:vendors,ret;for(var i=0;i<check_vends.length;i++){check_style(check_vends[i])}return!!ret}function transform3dTest(){var a="transform-3d",ret=$.mobile.media("(-"+vendors.join("-"+a+"),(-")+"-"+a+"),("+a+")");if(ret){return!!ret}var b=m.createElement("div"),transforms={'MozTransform':'-moz-transform','transform':'transform'};h.append(b);for(var t in transforms){if(b.style[t]!==g){b.style[t]='translate3d( 100px, 1px, 1px )';ret=l.getComputedStyle(b).getPropertyValue(transforms[t])}}return(!!ret&&ret!=="none")}function baseTagTest(){var a=location.protocol+"//"+location.host+location.pathname+"ui-dir/",base=$("head base"),fauxEle=null,href="",link,rebase;if(!base.length){base=fauxEle=$("<base>",{"href":a}).appendTo("head")}else{href=base.attr("href")}link=$("<a href='testurl' />").prependTo(h);rebase=link[0].href;base[0].href=href||location.pathname;if(fauxEle){fauxEle.remove()}return rebase.indexOf(a)===0}function cssPointerEventsTest(){var a=m.createElement('x'),documentElement=m.documentElement,getComputedStyle=l.getComputedStyle,supports;if(!('pointerEvents'in a.style)){return false}a.style.pointerEvents='auto';a.style.pointerEvents='x';documentElement.appendChild(a);supports=getComputedStyle&&getComputedStyle(a,'').pointerEvents==='auto';documentElement.removeChild(a);return!!supports}function boundingRect(){var a=m.createElement("div");return typeof a.getBoundingClientRect!=="undefined"}$.extend($.mobile,{browser:{}});$.mobile.browser.oldIE=(function(){var v=3,div=m.createElement("div"),a=div.all||[];do{div.innerHTML="<!--[if gt IE "+(++v)+"]><br><![endif]-->"}while(a[0]);return v>4?v:!v})();function fixedPosition(){var w=l,ua=navigator.userAgent,platform=navigator.platform,wkmatch=ua.match(/AppleWebKit\/([0-9]+)/),wkversion=!!wkmatch&&wkmatch[1],ffmatch=ua.match(/Fennec\/([0-9]+)/),ffversion=!!ffmatch&&ffmatch[1],operammobilematch=ua.match(/Opera Mobi\/([0-9]+)/),omversion=!!operammobilematch&&operammobilematch[1];if(((platform.indexOf("iPhone")>-1||platform.indexOf("iPad")>-1||platform.indexOf("iPod")>-1)&&wkversion&&wkversion<534)||(w.operamini&&({}).toString.call(w.operamini)==="[object OperaMini]")||(operammobilematch&&omversion<7458)||(ua.indexOf("Android")>-1&&wkversion&&wkversion<533)||(ffversion&&ffversion<6)||("palmGetResource"in l&&wkversion&&wkversion<534)||(ua.indexOf("MeeGo")>-1&&ua.indexOf("NokiaBrowser/8.5.0")>-1)){return false}return true}$.extend($.support,{cssTransitions:"WebKitTransitionEvent"in l||validStyle('transition','height 100ms linear',["Webkit","Moz",""])&&!$.mobile.browser.oldIE&&!opera,pushState:"pushState"in history&&"replaceState"in history&&!(l.navigator.userAgent.indexOf("Firefox")>=0&&l.top!==l)&&(l.navigator.userAgent.search(/CriOS/)===-1),mediaquery:$.mobile.media("only all"),cssPseudoElement:!!propExists("content"),touchOverflow:!!propExists("overflowScrolling"),cssTransform3d:transform3dTest(),boxShadow:!!propExists("boxShadow")&&!bb,fixedPosition:fixedPosition(),scrollTop:("pageXOffset"in l||"scrollTop"in m.documentElement||"scrollTop"in h[0])&&!webos&&!operamini,dynamicBaseTag:baseTagTest(),cssPointerEvents:cssPointerEventsTest(),boundingRect:boundingRect()});h.remove();var j=(function(){var a=l.navigator.userAgent;return a.indexOf("Nokia")>-1&&(a.indexOf("Symbian/3")>-1||a.indexOf("Series60/5")>-1)&&a.indexOf("AppleWebKit")>-1&&a.match(/(BrowserNG|NokiaBrowser)\/7\.[0-3]/)})();$.mobile.gradeA=function(){return($.support.mediaquery||$.mobile.browser.oldIE&&$.mobile.browser.oldIE>=7)&&($.support.boundingRect||$.fn.jquery.match(/1\.[0-7+]\.[0-9+]?/)!==null)};$.mobile.ajaxBlacklist=l.blackberry&&!l.WebKitPoint||operamini||j;if(j){$(function(){$("head link[rel='stylesheet']").attr("rel","alternate stylesheet").attr("rel","stylesheet")})}})(k)}));

/*!
 * jQuery hashchange event - v1.3
 * http://benalman.com/projects/jquery-hashchange-plugin/
 */
;(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}$.browser.msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);

/*!
 * iScroll v5.1.3(probe version)
 * http://iscrolljs.com/ 
 */
;(function(o,p,q){var s=o.requestAnimationFrame||o.webkitRequestAnimationFrame||o.mozRequestAnimationFrame||o.oRequestAnimationFrame||o.msRequestAnimationFrame||function(a){o.setTimeout(a,1000/60)};var t=(function(){var h={};var j=p.createElement('div').style;var m=(function(){var a=['t','webkitT','MozT','msT','OT'],transform,i=0,l=a.length;for(;i<l;i++){transform=a[i]+'ransform';if(transform in j)return a[i].substr(0,a[i].length-1)}return false})();function _prefixStyle(a){if(m===false)return false;if(m==='')return a;return m+a.charAt(0).toUpperCase()+a.substr(1)}h.getTime=Date.now||function getTime(){return new Date().getTime()};h.extend=function(a,b){for(var i in b){a[i]=b[i]}};h.addEvent=function(a,b,c,d){a.addEventListener(b,c,!!d)};h.removeEvent=function(a,b,c,d){a.removeEventListener(b,c,!!d)};h.prefixPointerEvent=function(a){return o.MSPointerEvent?'MSPointer'+a.charAt(9).toUpperCase()+a.substr(10):a};h.momentum=function(a,b,c,d,e,f){var g=a-b,speed=q.abs(g)/c,destination,duration;f=f===undefined?0.0006:f;destination=a+(speed*speed)/(2*f)*(g<0?-1:1);duration=speed/f;if(destination<d){destination=e?d-(e/2.5*(speed/8)):d;g=q.abs(destination-a);duration=g/speed}else if(destination>0){destination=e?e/2.5*(speed/8):0;g=q.abs(a)+destination;duration=g/speed}return{destination:q.round(destination),duration:duration}};var n=_prefixStyle('transform');h.extend(h,{hasTransform:n!==false,hasPerspective:_prefixStyle('perspective')in j,hasTouch:'ontouchstart'in o,hasPointer:o.PointerEvent||o.MSPointerEvent,hasTransition:_prefixStyle('transition')in j});h.isBadAndroid=/Android /.test(o.navigator.appVersion)&&!(/Chrome\/\d/.test(o.navigator.appVersion));h.extend(h.style={},{transform:n,transitionTimingFunction:_prefixStyle('transitionTimingFunction'),transitionDuration:_prefixStyle('transitionDuration'),transitionDelay:_prefixStyle('transitionDelay'),transformOrigin:_prefixStyle('transformOrigin')});h.hasClass=function(e,c){var a=new RegExp("(^|\\s)"+c+"(\\s|$)");return a.test(e.className)};h.addClass=function(e,c){if(h.hasClass(e,c)){return}var a=e.className.split(' ');a.push(c);e.className=a.join(' ')};h.removeClass=function(e,c){if(!h.hasClass(e,c)){return}var a=new RegExp("(^|\\s)"+c+"(\\s|$)",'g');e.className=e.className.replace(a,' ')};h.offset=function(a){var b=-a.offsetLeft,top=-a.offsetTop;while(a=a.offsetParent){b-=a.offsetLeft;top-=a.offsetTop}return{left:b,top:top}};h.preventDefaultException=function(a,b){for(var i in b){if(b[i].test(a[i])){return true}}return false};h.extend(h.eventType={},{touchstart:1,touchmove:1,touchend:1,mousedown:2,mousemove:2,mouseup:2,pointerdown:3,pointermove:3,pointerup:3,MSPointerDown:3,MSPointerMove:3,MSPointerUp:3});h.extend(h.ease={},{quadratic:{style:'cubic-bezier(0.25, 0.46, 0.45, 0.94)',fn:function(k){return k*(2-k)}},circular:{style:'cubic-bezier(0.1, 0.57, 0.1, 1)',fn:function(k){return q.sqrt(1-(--k*k))}},back:{style:'cubic-bezier(0.175, 0.885, 0.32, 1.275)',fn:function(k){var b=4;return(k=k-1)*k*((b+1)*k+b)+1}},bounce:{style:'',fn:function(k){if((k/=1)<(1/2.75)){return 7.5625*k*k}else if(k<(2/2.75)){return 7.5625*(k-=(1.5/2.75))*k+0.75}else if(k<(2.5/2.75)){return 7.5625*(k-=(2.25/2.75))*k+0.9375}else{return 7.5625*(k-=(2.625/2.75))*k+0.984375}}},elastic:{style:'',fn:function(k){var f=0.22,e=0.4;if(k===0){return 0}if(k==1){return 1}return(e*q.pow(2,-10*k)*q.sin((k-f/4)*(2*q.PI)/f)+1)}}});h.tap=function(e,a){var b=p.createEvent('Event');b.initEvent(a,true,true);b.pageX=e.pageX;b.pageY=e.pageY;e.target.dispatchEvent(b)};h.click=function(e){var a=e.target,ev;if(!(/(SELECT|INPUT|TEXTAREA)/i).test(a.tagName)){ev=p.createEvent('MouseEvents');ev.initMouseEvent('click',true,true,e.view,1,a.screenX,a.screenY,a.clientX,a.clientY,e.ctrlKey,e.altKey,e.shiftKey,e.metaKey,0,null);ev._constructed=true;a.dispatchEvent(ev)}};return h})();function IScroll(a,b){this.wrapper=typeof a=='string'?p.querySelector(a):a;this.scroller=this.wrapper.children[0];this.scrollerStyle=this.scroller.style;this.options={resizeScrollbars:true,mouseWheelSpeed:20,snapThreshold:0.334,startX:0,startY:0,scrollY:true,directionLockThreshold:5,momentum:true,bounce:true,bounceTime:600,bounceEasing:'',preventDefault:true,preventDefaultException:{tagName:/^(INPUT|TEXTAREA|BUTTON|SELECT)$/},HWCompositing:true,useTransition:true,useTransform:true};for(var i in b){this.options[i]=b[i]}this.translateZ=this.options.HWCompositing&&t.hasPerspective?' translateZ(0)':'';this.options.useTransition=t.hasTransition&&this.options.useTransition;this.options.useTransform=t.hasTransform&&this.options.useTransform;this.options.eventPassthrough=this.options.eventPassthrough===true?'vertical':this.options.eventPassthrough;this.options.preventDefault=!this.options.eventPassthrough&&this.options.preventDefault;this.options.scrollY=this.options.eventPassthrough=='vertical'?false:this.options.scrollY;this.options.scrollX=this.options.eventPassthrough=='horizontal'?false:this.options.scrollX;this.options.freeScroll=this.options.freeScroll&&!this.options.eventPassthrough;this.options.directionLockThreshold=this.options.eventPassthrough?0:this.options.directionLockThreshold;this.options.bounceEasing=typeof this.options.bounceEasing=='string'?t.ease[this.options.bounceEasing]||t.ease.circular:this.options.bounceEasing;this.options.resizePolling=this.options.resizePolling===undefined?60:this.options.resizePolling;if(this.options.tap===true){this.options.tap='tap'}if(this.options.shrinkScrollbars=='scale'){this.options.useTransition=false}this.options.invertWheelDirection=this.options.invertWheelDirection?-1:1;if(this.options.probeType==3){this.options.useTransition=false}this.x=0;this.y=0;this.directionX=0;this.directionY=0;this._events={};this._init();this.refresh();this.scrollTo(this.options.startX,this.options.startY);this.enable()}IScroll.prototype={version:'5.1.3',_init:function(){this._initEvents();if(this.options.scrollbars||this.options.indicators){this._initIndicators()}if(this.options.mouseWheel){this._initWheel()}if(this.options.snap){this._initSnap()}if(this.options.keyBindings){this._initKeys()}},destroy:function(){this._initEvents(true);this._execEvent('destroy')},_transitionEnd:function(e){if(e.target!=this.scroller||!this.isInTransition){return}this._transitionTime();if(!this.resetPosition(this.options.bounceTime)){this.isInTransition=false;this._execEvent('scrollEnd')}},_start:function(e){if(t.eventType[e.type]!=1){if(e.button!==0){return}}if(!this.enabled||(this.initiated&&t.eventType[e.type]!==this.initiated)){return}if(this.options.preventDefault&&!t.isBadAndroid&&!t.preventDefaultException(e.target,this.options.preventDefaultException)){e.preventDefault()}var a=e.touches?e.touches[0]:e,pos;this.initiated=t.eventType[e.type];this.moved=false;this.distX=0;this.distY=0;this.directionX=0;this.directionY=0;this.directionLocked=0;this._transitionTime();this.startTime=t.getTime();if(this.options.useTransition&&this.isInTransition){this.isInTransition=false;pos=this.getComputedPosition();this._translate(q.round(pos.x),q.round(pos.y));this._execEvent('scrollEnd')}else if(!this.options.useTransition&&this.isAnimating){this.isAnimating=false;this._execEvent('scrollEnd')}this.startX=this.x;this.startY=this.y;this.absStartX=this.x;this.absStartY=this.y;this.pointX=a.pageX;this.pointY=a.pageY;this._execEvent('beforeScrollStart')},_move:function(e){if(!this.enabled||t.eventType[e.type]!==this.initiated){return}if(this.options.preventDefault){e.preventDefault()}var a=e.touches?e.touches[0]:e,deltaX=a.pageX-this.pointX,deltaY=a.pageY-this.pointY,timestamp=t.getTime(),newX,newY,absDistX,absDistY;this.pointX=a.pageX;this.pointY=a.pageY;this.distX+=deltaX;this.distY+=deltaY;absDistX=q.abs(this.distX);absDistY=q.abs(this.distY);if(timestamp-this.endTime>300&&(absDistX<10&&absDistY<10)){return}if(!this.directionLocked&&!this.options.freeScroll){if(absDistX>absDistY+this.options.directionLockThreshold){this.directionLocked='h'}else if(absDistY>=absDistX+this.options.directionLockThreshold){this.directionLocked='v'}else{this.directionLocked='n'}}if(this.directionLocked=='h'){if(this.options.eventPassthrough=='vertical'){e.preventDefault()}else if(this.options.eventPassthrough=='horizontal'){this.initiated=false;return}deltaY=0}else if(this.directionLocked=='v'){if(this.options.eventPassthrough=='horizontal'){e.preventDefault()}else if(this.options.eventPassthrough=='vertical'){this.initiated=false;return}deltaX=0}deltaX=this.hasHorizontalScroll?deltaX:0;deltaY=this.hasVerticalScroll?deltaY:0;newX=this.x+deltaX;newY=this.y+deltaY;if(newX>0||newX<this.maxScrollX){newX=this.options.bounce?this.x+deltaX/3:newX>0?0:this.maxScrollX}if(newY>0||newY<this.maxScrollY){newY=this.options.bounce?this.y+deltaY/3:newY>0?0:this.maxScrollY}this.directionX=deltaX>0?-1:deltaX<0?1:0;this.directionY=deltaY>0?-1:deltaY<0?1:0;if(!this.moved){this._execEvent('scrollStart')}this.moved=true;this._translate(newX,newY);if(timestamp-this.startTime>300){this.startTime=timestamp;this.startX=this.x;this.startY=this.y;if(this.options.probeType==1){this._execEvent('scroll')}}if(this.options.probeType>1){this._execEvent('scroll')}},_end:function(e){if(!this.enabled||t.eventType[e.type]!==this.initiated){return}if(this.options.preventDefault&&!t.preventDefaultException(e.target,this.options.preventDefaultException)){e.preventDefault()}var a=e.changedTouches?e.changedTouches[0]:e,momentumX,momentumY,duration=t.getTime()-this.startTime,newX=q.round(this.x),newY=q.round(this.y),distanceX=q.abs(newX-this.startX),distanceY=q.abs(newY-this.startY),time=0,easing='';this.isInTransition=0;this.initiated=0;this.endTime=t.getTime();if(this.resetPosition(this.options.bounceTime)){return}this.scrollTo(newX,newY);if(!this.moved){if(this.options.tap){t.tap(e,this.options.tap)}if(this.options.click){t.click(e)}this._execEvent('scrollCancel');return}if(this._events.flick&&duration<200&&distanceX<100&&distanceY<100){this._execEvent('flick');return}if(this.options.momentum&&duration<300){momentumX=this.hasHorizontalScroll?t.momentum(this.x,this.startX,duration,this.maxScrollX,this.options.bounce?this.wrapperWidth:0,this.options.deceleration):{destination:newX,duration:0};momentumY=this.hasVerticalScroll?t.momentum(this.y,this.startY,duration,this.maxScrollY,this.options.bounce?this.wrapperHeight:0,this.options.deceleration):{destination:newY,duration:0};newX=momentumX.destination;newY=momentumY.destination;time=q.max(momentumX.duration,momentumY.duration);this.isInTransition=1}if(this.options.snap){var b=this._nearestSnap(newX,newY);this.currentPage=b;time=this.options.snapSpeed||q.max(q.max(q.min(q.abs(newX-b.x),1000),q.min(q.abs(newY-b.y),1000)),300);newX=b.x;newY=b.y;this.directionX=0;this.directionY=0;easing=this.options.bounceEasing}if(newX!=this.x||newY!=this.y){if(newX>0||newX<this.maxScrollX||newY>0||newY<this.maxScrollY){easing=t.ease.quadratic}this.scrollTo(newX,newY,time,easing);return}this._execEvent('scrollEnd')},_resize:function(){var a=this;clearTimeout(this.resizeTimeout);this.resizeTimeout=setTimeout(function(){a.refresh()},this.options.resizePolling)},resetPosition:function(a){var x=this.x,y=this.y;a=a||0;if(!this.hasHorizontalScroll||this.x>0){x=0}else if(this.x<this.maxScrollX){x=this.maxScrollX}if(!this.hasVerticalScroll||this.y>0){y=0}else if(this.y<this.maxScrollY){y=this.maxScrollY}if(x==this.x&&y==this.y){return false}this.scrollTo(x,y,a,this.options.bounceEasing);return true},disable:function(){this.enabled=false},enable:function(){this.enabled=true},refresh:function(){var a=this.wrapper.offsetHeight;this.wrapperWidth=this.wrapper.clientWidth;this.wrapperHeight=this.wrapper.clientHeight;this.scrollerWidth=this.scroller.offsetWidth;this.scrollerHeight=this.scroller.offsetHeight;this.maxScrollX=this.wrapperWidth-this.scrollerWidth;this.maxScrollY=this.wrapperHeight-this.scrollerHeight;this.hasHorizontalScroll=this.options.scrollX&&this.maxScrollX<0;this.hasVerticalScroll=this.options.scrollY&&this.maxScrollY<0;if(!this.hasHorizontalScroll){this.maxScrollX=0;this.scrollerWidth=this.wrapperWidth}if(!this.hasVerticalScroll){this.maxScrollY=0;this.scrollerHeight=this.wrapperHeight}this.endTime=0;this.directionX=0;this.directionY=0;this.wrapperOffset=t.offset(this.wrapper);this._execEvent('refresh');this.resetPosition()},on:function(a,b){if(!this._events[a]){this._events[a]=[]}this._events[a].push(b)},off:function(a,b){if(!this._events[a]){return}var c=this._events[a].indexOf(b);if(c>-1){this._events[a].splice(c,1)}},_execEvent:function(a){if(!this._events[a]){return}var i=0,l=this._events[a].length;if(!l){return}for(;i<l;i++){this._events[a][i].apply(this,[].slice.call(arguments,1))}},scrollBy:function(x,y,a,b){x=this.x+x;y=this.y+y;a=a||0;this.scrollTo(x,y,a,b)},scrollTo:function(x,y,a,b){b=b||t.ease.circular;this.isInTransition=this.options.useTransition&&a>0;if(!a||(this.options.useTransition&&b.style)){this._transitionTimingFunction(b.style);this._transitionTime(a);this._translate(x,y)}else{this._animate(x,y,a,b.fn)}},scrollToElement:function(a,b,c,d,e){a=a.nodeType?a:this.scroller.querySelector(a);if(!a){return}var f=t.offset(a);f.left-=this.wrapperOffset.left;f.top-=this.wrapperOffset.top;if(c===true){c=q.round(a.offsetWidth/2-this.wrapper.offsetWidth/2)}if(d===true){d=q.round(a.offsetHeight/2-this.wrapper.offsetHeight/2)}f.left-=c||0;f.top-=d||0;f.left=f.left>0?0:f.left<this.maxScrollX?this.maxScrollX:f.left;f.top=f.top>0?0:f.top<this.maxScrollY?this.maxScrollY:f.top;b=b===undefined||b===null||b==='auto'?q.max(q.abs(this.x-f.left),q.abs(this.y-f.top)):b;this.scrollTo(f.left,f.top,b,e)},_transitionTime:function(a){a=a||0;this.scrollerStyle[t.style.transitionDuration]=a+'ms';if(!a&&t.isBadAndroid){this.scrollerStyle[t.style.transitionDuration]='0.001s'}if(this.indicators){for(var i=this.indicators.length;i--;){this.indicators[i].transitionTime(a)}}},_transitionTimingFunction:function(a){this.scrollerStyle[t.style.transitionTimingFunction]=a;if(this.indicators){for(var i=this.indicators.length;i--;){this.indicators[i].transitionTimingFunction(a)}}},_translate:function(x,y){if(this.options.useTransform){this.scrollerStyle[t.style.transform]='translate('+x+'px,'+y+'px)'+this.translateZ}else{x=q.round(x);y=q.round(y);this.scrollerStyle.left=x+'px';this.scrollerStyle.top=y+'px'}this.x=x;this.y=y;if(this.indicators){for(var i=this.indicators.length;i--;){this.indicators[i].updatePosition()}}},_initEvents:function(a){var b=a?t.removeEvent:t.addEvent,target=this.options.bindToWrapper?this.wrapper:o;b(o,'orientationchange',this);b(o,'resize',this);if(this.options.click){b(this.wrapper,'click',this,true)}if(!this.options.disableMouse){b(this.wrapper,'mousedown',this);b(target,'mousemove',this);b(target,'mousecancel',this);b(target,'mouseup',this)}if(t.hasPointer&&!this.options.disablePointer){b(this.wrapper,t.prefixPointerEvent('pointerdown'),this);b(target,t.prefixPointerEvent('pointermove'),this);b(target,t.prefixPointerEvent('pointercancel'),this);b(target,t.prefixPointerEvent('pointerup'),this)}if(t.hasTouch&&!this.options.disableTouch){b(this.wrapper,'touchstart',this);b(target,'touchmove',this);b(target,'touchcancel',this);b(target,'touchend',this)}b(this.scroller,'transitionend',this);b(this.scroller,'webkitTransitionEnd',this);b(this.scroller,'oTransitionEnd',this);b(this.scroller,'MSTransitionEnd',this)},getComputedPosition:function(){var a=o.getComputedStyle(this.scroller,null),x,y;if(this.options.useTransform){a=a[t.style.transform].split(')')[0].split(', ');x=+(a[12]||a[4]);y=+(a[13]||a[5])}else{x=+a.left.replace(/[^-\d.]/g,'');y=+a.top.replace(/[^-\d.]/g,'')}return{x:x,y:y}},_initIndicators:function(){var b=this.options.interactiveScrollbars,customStyle=typeof this.options.scrollbars!='string',indicators=[],indicator;var c=this;this.indicators=[];if(this.options.scrollbars){if(this.options.scrollY){indicator={el:createDefaultScrollbar('v',b,this.options.scrollbars),interactive:b,defaultScrollbars:true,customStyle:customStyle,resize:this.options.resizeScrollbars,shrink:this.options.shrinkScrollbars,fade:this.options.fadeScrollbars,listenX:false};this.wrapper.appendChild(indicator.el);indicators.push(indicator)}if(this.options.scrollX){indicator={el:createDefaultScrollbar('h',b,this.options.scrollbars),interactive:b,defaultScrollbars:true,customStyle:customStyle,resize:this.options.resizeScrollbars,shrink:this.options.shrinkScrollbars,fade:this.options.fadeScrollbars,listenY:false};this.wrapper.appendChild(indicator.el);indicators.push(indicator)}}if(this.options.indicators){indicators=indicators.concat(this.options.indicators)}for(var i=indicators.length;i--;){this.indicators.push(new Indicator(this,indicators[i]))}function _indicatorsMap(a){for(var i=c.indicators.length;i--;){a.call(c.indicators[i])}}if(this.options.fadeScrollbars){this.on('scrollEnd',function(){_indicatorsMap(function(){this.fade()})});this.on('scrollCancel',function(){_indicatorsMap(function(){this.fade()})});this.on('scrollStart',function(){_indicatorsMap(function(){this.fade(1)})});this.on('beforeScrollStart',function(){_indicatorsMap(function(){this.fade(1,true)})})}this.on('refresh',function(){_indicatorsMap(function(){this.refresh()})});this.on('destroy',function(){_indicatorsMap(function(){this.destroy()});delete this.indicators})},_initWheel:function(){t.addEvent(this.wrapper,'wheel',this);t.addEvent(this.wrapper,'mousewheel',this);t.addEvent(this.wrapper,'DOMMouseScroll',this);this.on('destroy',function(){t.removeEvent(this.wrapper,'wheel',this);t.removeEvent(this.wrapper,'mousewheel',this);t.removeEvent(this.wrapper,'DOMMouseScroll',this)})},_wheel:function(e){if(!this.enabled){return}e.preventDefault();e.stopPropagation();var a,wheelDeltaY,newX,newY,that=this;if(this.wheelTimeout===undefined){that._execEvent('scrollStart')}clearTimeout(this.wheelTimeout);this.wheelTimeout=setTimeout(function(){that._execEvent('scrollEnd');that.wheelTimeout=undefined},400);if('deltaX'in e){if(e.deltaMode===1){a=-e.deltaX*this.options.mouseWheelSpeed;wheelDeltaY=-e.deltaY*this.options.mouseWheelSpeed}else{a=-e.deltaX;wheelDeltaY=-e.deltaY}}else if('wheelDeltaX'in e){a=e.wheelDeltaX/120*this.options.mouseWheelSpeed;wheelDeltaY=e.wheelDeltaY/120*this.options.mouseWheelSpeed}else if('wheelDelta'in e){a=wheelDeltaY=e.wheelDelta/120*this.options.mouseWheelSpeed}else if('detail'in e){a=wheelDeltaY=-e.detail/3*this.options.mouseWheelSpeed}else{return}a*=this.options.invertWheelDirection;wheelDeltaY*=this.options.invertWheelDirection;if(!this.hasVerticalScroll){a=wheelDeltaY;wheelDeltaY=0}if(this.options.snap){newX=this.currentPage.pageX;newY=this.currentPage.pageY;if(a>0){newX--}else if(a<0){newX++}if(wheelDeltaY>0){newY--}else if(wheelDeltaY<0){newY++}this.goToPage(newX,newY);return}newX=this.x+q.round(this.hasHorizontalScroll?a:0);newY=this.y+q.round(this.hasVerticalScroll?wheelDeltaY:0);if(newX>0){newX=0}else if(newX<this.maxScrollX){newX=this.maxScrollX}if(newY>0){newY=0}else if(newY<this.maxScrollY){newY=this.maxScrollY}this.scrollTo(newX,newY,0);if(this.options.probeType>1){this._execEvent('scroll')}},_initSnap:function(){this.currentPage={};if(typeof this.options.snap=='string'){this.options.snap=this.scroller.querySelectorAll(this.options.snap)}this.on('refresh',function(){var i=0,l,m=0,n,cx,cy,x=0,y,stepX=this.options.snapStepX||this.wrapperWidth,stepY=this.options.snapStepY||this.wrapperHeight,el;this.pages=[];if(!this.wrapperWidth||!this.wrapperHeight||!this.scrollerWidth||!this.scrollerHeight){return}if(this.options.snap===true){cx=q.round(stepX/2);cy=q.round(stepY/2);while(x>-this.scrollerWidth){this.pages[i]=[];l=0;y=0;while(y>-this.scrollerHeight){this.pages[i][l]={x:q.max(x,this.maxScrollX),y:q.max(y,this.maxScrollY),width:stepX,height:stepY,cx:x-cx,cy:y-cy};y-=stepY;l++}x-=stepX;i++}}else{el=this.options.snap;l=el.length;n=-1;for(;i<l;i++){if(i===0||el[i].offsetLeft<=el[i-1].offsetLeft){m=0;n++}if(!this.pages[m]){this.pages[m]=[]}x=q.max(-el[i].offsetLeft,this.maxScrollX);y=q.max(-el[i].offsetTop,this.maxScrollY);cx=x-q.round(el[i].offsetWidth/2);cy=y-q.round(el[i].offsetHeight/2);this.pages[m][n]={x:x,y:y,width:el[i].offsetWidth,height:el[i].offsetHeight,cx:cx,cy:cy};if(x>this.maxScrollX){m++}}}this.goToPage(this.currentPage.pageX||0,this.currentPage.pageY||0,0);if(this.options.snapThreshold%1===0){this.snapThresholdX=this.options.snapThreshold;this.snapThresholdY=this.options.snapThreshold}else{this.snapThresholdX=q.round(this.pages[this.currentPage.pageX][this.currentPage.pageY].width*this.options.snapThreshold);this.snapThresholdY=q.round(this.pages[this.currentPage.pageX][this.currentPage.pageY].height*this.options.snapThreshold)}});this.on('flick',function(){var a=this.options.snapSpeed||q.max(q.max(q.min(q.abs(this.x-this.startX),1000),q.min(q.abs(this.y-this.startY),1000)),300);this.goToPage(this.currentPage.pageX+this.directionX,this.currentPage.pageY+this.directionY,a)})},_nearestSnap:function(x,y){if(!this.pages.length){return{x:0,y:0,pageX:0,pageY:0}}var i=0,l=this.pages.length,m=0;if(q.abs(x-this.absStartX)<this.snapThresholdX&&q.abs(y-this.absStartY)<this.snapThresholdY){return this.currentPage}if(x>0){x=0}else if(x<this.maxScrollX){x=this.maxScrollX}if(y>0){y=0}else if(y<this.maxScrollY){y=this.maxScrollY}for(;i<l;i++){if(x>=this.pages[i][0].cx){x=this.pages[i][0].x;break}}l=this.pages[i].length;for(;m<l;m++){if(y>=this.pages[0][m].cy){y=this.pages[0][m].y;break}}if(i==this.currentPage.pageX){i+=this.directionX;if(i<0){i=0}else if(i>=this.pages.length){i=this.pages.length-1}x=this.pages[i][0].x}if(m==this.currentPage.pageY){m+=this.directionY;if(m<0){m=0}else if(m>=this.pages[0].length){m=this.pages[0].length-1}y=this.pages[0][m].y}return{x:x,y:y,pageX:i,pageY:m}},goToPage:function(x,y,a,b){b=b||this.options.bounceEasing;if(x>=this.pages.length){x=this.pages.length-1}else if(x<0){x=0}if(y>=this.pages[x].length){y=this.pages[x].length-1}else if(y<0){y=0}var c=this.pages[x][y].x,posY=this.pages[x][y].y;a=a===undefined?this.options.snapSpeed||q.max(q.max(q.min(q.abs(c-this.x),1000),q.min(q.abs(posY-this.y),1000)),300):a;this.currentPage={x:c,y:posY,pageX:x,pageY:y};this.scrollTo(c,posY,a,b)},next:function(a,b){var x=this.currentPage.pageX,y=this.currentPage.pageY;x++;if(x>=this.pages.length&&this.hasVerticalScroll){x=0;y++}this.goToPage(x,y,a,b)},prev:function(a,b){var x=this.currentPage.pageX,y=this.currentPage.pageY;x--;if(x<0&&this.hasVerticalScroll){x=0;y--}this.goToPage(x,y,a,b)},_initKeys:function(e){var a={pageUp:33,pageDown:34,end:35,home:36,left:37,up:38,right:39,down:40};var i;if(typeof this.options.keyBindings=='object'){for(i in this.options.keyBindings){if(typeof this.options.keyBindings[i]=='string'){this.options.keyBindings[i]=this.options.keyBindings[i].toUpperCase().charCodeAt(0)}}}else{this.options.keyBindings={}}for(i in a){this.options.keyBindings[i]=this.options.keyBindings[i]||a[i]}t.addEvent(o,'keydown',this);this.on('destroy',function(){t.removeEvent(o,'keydown',this)})},_key:function(e){if(!this.enabled){return}var a=this.options.snap,newX=a?this.currentPage.pageX:this.x,newY=a?this.currentPage.pageY:this.y,now=t.getTime(),prevTime=this.keyTime||0,acceleration=0.250,pos;if(this.options.useTransition&&this.isInTransition){pos=this.getComputedPosition();this._translate(q.round(pos.x),q.round(pos.y));this.isInTransition=false}this.keyAcceleration=now-prevTime<200?q.min(this.keyAcceleration+acceleration,50):0;switch(e.keyCode){case this.options.keyBindings.pageUp:if(this.hasHorizontalScroll&&!this.hasVerticalScroll){newX+=a?1:this.wrapperWidth}else{newY+=a?1:this.wrapperHeight}break;case this.options.keyBindings.pageDown:if(this.hasHorizontalScroll&&!this.hasVerticalScroll){newX-=a?1:this.wrapperWidth}else{newY-=a?1:this.wrapperHeight}break;case this.options.keyBindings.end:newX=a?this.pages.length-1:this.maxScrollX;newY=a?this.pages[0].length-1:this.maxScrollY;break;case this.options.keyBindings.home:newX=0;newY=0;break;case this.options.keyBindings.left:newX+=a?-1:5+this.keyAcceleration>>0;break;case this.options.keyBindings.up:newY+=a?1:5+this.keyAcceleration>>0;break;case this.options.keyBindings.right:newX-=a?-1:5+this.keyAcceleration>>0;break;case this.options.keyBindings.down:newY-=a?1:5+this.keyAcceleration>>0;break;default:return}if(a){this.goToPage(newX,newY);return}if(newX>0){newX=0;this.keyAcceleration=0}else if(newX<this.maxScrollX){newX=this.maxScrollX;this.keyAcceleration=0}if(newY>0){newY=0;this.keyAcceleration=0}else if(newY<this.maxScrollY){newY=this.maxScrollY;this.keyAcceleration=0}this.scrollTo(newX,newY,0);this.keyTime=now},_animate:function(b,c,d,e){var f=this,startX=this.x,startY=this.y,startTime=t.getTime(),destTime=startTime+d;function step(){var a=t.getTime(),newX,newY,easing;if(a>=destTime){f.isAnimating=false;f._translate(b,c);if(!f.resetPosition(f.options.bounceTime)){f._execEvent('scrollEnd')}return}a=(a-startTime)/d;easing=e(a);newX=(b-startX)*easing+startX;newY=(c-startY)*easing+startY;f._translate(newX,newY);if(f.isAnimating){s(step)}if(f.options.probeType==3){f._execEvent('scroll')}}this.isAnimating=true;step()},handleEvent:function(e){switch(e.type){case'touchstart':case'pointerdown':case'MSPointerDown':case'mousedown':this._start(e);break;case'touchmove':case'pointermove':case'MSPointerMove':case'mousemove':this._move(e);break;case'touchend':case'pointerup':case'MSPointerUp':case'mouseup':case'touchcancel':case'pointercancel':case'MSPointerCancel':case'mousecancel':this._end(e);break;case'orientationchange':case'resize':this._resize();break;case'transitionend':case'webkitTransitionEnd':case'oTransitionEnd':case'MSTransitionEnd':this._transitionEnd(e);break;case'wheel':case'DOMMouseScroll':case'mousewheel':this._wheel(e);break;case'keydown':this._key(e);break;case'click':if(!e._constructed){e.preventDefault();e.stopPropagation()}break}}};function createDefaultScrollbar(a,b,c){var d=p.createElement('div'),indicator=p.createElement('div');if(c===true){d.style.cssText='position:absolute;z-index:9999';indicator.style.cssText='-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;position:absolute;background:rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.9);border-radius:3px'}indicator.className='iScrollIndicator';if(a=='h'){if(c===true){d.style.cssText+=';height:5px;left:2px;right:2px;bottom:0';indicator.style.height='100%'}d.className='iScrollHorizontalScrollbar'}else{if(c===true){d.style.cssText+=';width:5px;bottom:2px;top:2px;right:1px';indicator.style.width='100%'}d.className='iScrollVerticalScrollbar'}d.style.cssText+=';overflow:hidden';if(!b){d.style.pointerEvents='none'}d.appendChild(indicator);return d}function Indicator(a,b){this.wrapper=typeof b.el=='string'?p.querySelector(b.el):b.el;this.wrapperStyle=this.wrapper.style;this.indicator=this.wrapper.children[0];this.indicatorStyle=this.indicator.style;this.scroller=a;this.options={listenX:true,listenY:true,interactive:false,resize:true,defaultScrollbars:false,shrink:false,fade:false,speedRatioX:0,speedRatioY:0};for(var i in b){this.options[i]=b[i]}this.sizeRatioX=1;this.sizeRatioY=1;this.maxPosX=0;this.maxPosY=0;if(this.options.interactive){if(!this.options.disableTouch){t.addEvent(this.indicator,'touchstart',this);t.addEvent(o,'touchend',this)}if(!this.options.disablePointer){t.addEvent(this.indicator,t.prefixPointerEvent('pointerdown'),this);t.addEvent(o,t.prefixPointerEvent('pointerup'),this)}if(!this.options.disableMouse){t.addEvent(this.indicator,'mousedown',this);t.addEvent(o,'mouseup',this)}}if(this.options.fade){this.wrapperStyle[t.style.transform]=this.scroller.translateZ;this.wrapperStyle[t.style.transitionDuration]=t.isBadAndroid?'0.001s':'0ms';this.wrapperStyle.opacity='0'}}Indicator.prototype={handleEvent:function(e){switch(e.type){case'touchstart':case'pointerdown':case'MSPointerDown':case'mousedown':this._start(e);break;case'touchmove':case'pointermove':case'MSPointerMove':case'mousemove':this._move(e);break;case'touchend':case'pointerup':case'MSPointerUp':case'mouseup':case'touchcancel':case'pointercancel':case'MSPointerCancel':case'mousecancel':this._end(e);break}},destroy:function(){if(this.options.interactive){t.removeEvent(this.indicator,'touchstart',this);t.removeEvent(this.indicator,t.prefixPointerEvent('pointerdown'),this);t.removeEvent(this.indicator,'mousedown',this);t.removeEvent(o,'touchmove',this);t.removeEvent(o,t.prefixPointerEvent('pointermove'),this);t.removeEvent(o,'mousemove',this);t.removeEvent(o,'touchend',this);t.removeEvent(o,t.prefixPointerEvent('pointerup'),this);t.removeEvent(o,'mouseup',this)}if(this.options.defaultScrollbars){this.wrapper.parentNode.removeChild(this.wrapper)}},_start:function(e){var a=e.touches?e.touches[0]:e;e.preventDefault();e.stopPropagation();this.transitionTime();this.initiated=true;this.moved=false;this.lastPointX=a.pageX;this.lastPointY=a.pageY;this.startTime=t.getTime();if(!this.options.disableTouch){t.addEvent(o,'touchmove',this)}if(!this.options.disablePointer){t.addEvent(o,t.prefixPointerEvent('pointermove'),this)}if(!this.options.disableMouse){t.addEvent(o,'mousemove',this)}this.scroller._execEvent('beforeScrollStart')},_move:function(e){var a=e.touches?e.touches[0]:e,deltaX,deltaY,newX,newY,timestamp=t.getTime();if(!this.moved){this.scroller._execEvent('scrollStart')}this.moved=true;deltaX=a.pageX-this.lastPointX;this.lastPointX=a.pageX;deltaY=a.pageY-this.lastPointY;this.lastPointY=a.pageY;newX=this.x+deltaX;newY=this.y+deltaY;this._pos(newX,newY);if(this.scroller.options.probeType==1&&timestamp-this.startTime>300){this.startTime=timestamp;this.scroller._execEvent('scroll')}else if(this.scroller.options.probeType>1){this.scroller._execEvent('scroll')}e.preventDefault();e.stopPropagation()},_end:function(e){if(!this.initiated){return}this.initiated=false;e.preventDefault();e.stopPropagation();t.removeEvent(o,'touchmove',this);t.removeEvent(o,t.prefixPointerEvent('pointermove'),this);t.removeEvent(o,'mousemove',this);if(this.scroller.options.snap){var a=this.scroller._nearestSnap(this.scroller.x,this.scroller.y);var b=this.options.snapSpeed||q.max(q.max(q.min(q.abs(this.scroller.x-a.x),1000),q.min(q.abs(this.scroller.y-a.y),1000)),300);if(this.scroller.x!=a.x||this.scroller.y!=a.y){this.scroller.directionX=0;this.scroller.directionY=0;this.scroller.currentPage=a;this.scroller.scrollTo(a.x,a.y,b,this.scroller.options.bounceEasing)}}if(this.moved){this.scroller._execEvent('scrollEnd')}},transitionTime:function(a){a=a||0;this.indicatorStyle[t.style.transitionDuration]=a+'ms';if(!a&&t.isBadAndroid){this.indicatorStyle[t.style.transitionDuration]='0.001s'}},transitionTimingFunction:function(a){this.indicatorStyle[t.style.transitionTimingFunction]=a},refresh:function(){this.transitionTime();if(this.options.listenX&&!this.options.listenY){this.indicatorStyle.display=this.scroller.hasHorizontalScroll?'block':'none'}else if(this.options.listenY&&!this.options.listenX){this.indicatorStyle.display=this.scroller.hasVerticalScroll?'block':'none'}else{this.indicatorStyle.display=this.scroller.hasHorizontalScroll||this.scroller.hasVerticalScroll?'block':'none'}if(this.scroller.hasHorizontalScroll&&this.scroller.hasVerticalScroll){t.addClass(this.wrapper,'iScrollBothScrollbars');t.removeClass(this.wrapper,'iScrollLoneScrollbar');if(this.options.defaultScrollbars&&this.options.customStyle){if(this.options.listenX){this.wrapper.style.right='8px'}else{this.wrapper.style.bottom='8px'}}}else{t.removeClass(this.wrapper,'iScrollBothScrollbars');t.addClass(this.wrapper,'iScrollLoneScrollbar');if(this.options.defaultScrollbars&&this.options.customStyle){if(this.options.listenX){this.wrapper.style.right='2px'}else{this.wrapper.style.bottom='2px'}}}var r=this.wrapper.offsetHeight;if(this.options.listenX){this.wrapperWidth=this.wrapper.clientWidth;if(this.options.resize){this.indicatorWidth=q.max(q.round(this.wrapperWidth*this.wrapperWidth/(this.scroller.scrollerWidth||this.wrapperWidth||1)),8);this.indicatorStyle.width=this.indicatorWidth+'px'}else{this.indicatorWidth=this.indicator.clientWidth}this.maxPosX=this.wrapperWidth-this.indicatorWidth;if(this.options.shrink=='clip'){this.minBoundaryX=-this.indicatorWidth+8;this.maxBoundaryX=this.wrapperWidth-8}else{this.minBoundaryX=0;this.maxBoundaryX=this.maxPosX}this.sizeRatioX=this.options.speedRatioX||(this.scroller.maxScrollX&&(this.maxPosX/this.scroller.maxScrollX))}if(this.options.listenY){this.wrapperHeight=this.wrapper.clientHeight;if(this.options.resize){this.indicatorHeight=q.max(q.round(this.wrapperHeight*this.wrapperHeight/(this.scroller.scrollerHeight||this.wrapperHeight||1)),8);this.indicatorStyle.height=this.indicatorHeight+'px'}else{this.indicatorHeight=this.indicator.clientHeight}this.maxPosY=this.wrapperHeight-this.indicatorHeight;if(this.options.shrink=='clip'){this.minBoundaryY=-this.indicatorHeight+8;this.maxBoundaryY=this.wrapperHeight-8}else{this.minBoundaryY=0;this.maxBoundaryY=this.maxPosY}this.maxPosY=this.wrapperHeight-this.indicatorHeight;this.sizeRatioY=this.options.speedRatioY||(this.scroller.maxScrollY&&(this.maxPosY/this.scroller.maxScrollY))}this.updatePosition()},updatePosition:function(){var x=this.options.listenX&&q.round(this.sizeRatioX*this.scroller.x)||0,y=this.options.listenY&&q.round(this.sizeRatioY*this.scroller.y)||0;if(!this.options.ignoreBoundaries){if(x<this.minBoundaryX){if(this.options.shrink=='scale'){this.width=q.max(this.indicatorWidth+x,8);this.indicatorStyle.width=this.width+'px'}x=this.minBoundaryX}else if(x>this.maxBoundaryX){if(this.options.shrink=='scale'){this.width=q.max(this.indicatorWidth-(x-this.maxPosX),8);this.indicatorStyle.width=this.width+'px';x=this.maxPosX+this.indicatorWidth-this.width}else{x=this.maxBoundaryX}}else if(this.options.shrink=='scale'&&this.width!=this.indicatorWidth){this.width=this.indicatorWidth;this.indicatorStyle.width=this.width+'px'}if(y<this.minBoundaryY){if(this.options.shrink=='scale'){this.height=q.max(this.indicatorHeight+y*3,8);this.indicatorStyle.height=this.height+'px'}y=this.minBoundaryY}else if(y>this.maxBoundaryY){if(this.options.shrink=='scale'){this.height=q.max(this.indicatorHeight-(y-this.maxPosY)*3,8);this.indicatorStyle.height=this.height+'px';y=this.maxPosY+this.indicatorHeight-this.height}else{y=this.maxBoundaryY}}else if(this.options.shrink=='scale'&&this.height!=this.indicatorHeight){this.height=this.indicatorHeight;this.indicatorStyle.height=this.height+'px'}}this.x=x;this.y=y;if(this.scroller.options.useTransform){this.indicatorStyle[t.style.transform]='translate('+x+'px,'+y+'px)'+this.scroller.translateZ}else{this.indicatorStyle.left=x+'px';this.indicatorStyle.top=y+'px'}},_pos:function(x,y){if(x<0){x=0}else if(x>this.maxPosX){x=this.maxPosX}if(y<0){y=0}else if(y>this.maxPosY){y=this.maxPosY}x=this.options.listenX?q.round(x/this.sizeRatioX):this.scroller.x;y=this.options.listenY?q.round(y/this.sizeRatioY):this.scroller.y;this.scroller.scrollTo(x,y)},fade:function(b,c){if(c&&!this.visible){return}clearTimeout(this.fadeTimeout);this.fadeTimeout=null;var d=b?250:500,delay=b?0:300;b=b?'1':'0';this.wrapperStyle[t.style.transitionDuration]=d+'ms';this.fadeTimeout=setTimeout((function(a){this.wrapperStyle.opacity=a;this.visible=+a}).bind(this,b),delay)}};IScroll.utils=t;if(typeof module!='undefined'&&module.exports){module.exports=IScroll}else{o.IScroll=IScroll}})(window,document,Math);

/*!
 * jQuery flexText: Auto-height textareas
 * --------------------------------------
 * Requires: jQuery 1.7+
 * Usage example: $('textarea').flexText()
 * Info: https://github.com/alexdunphy/flexText
 */
;(function(b){function a(c){this.$textarea=b(c);this._init()}a.prototype={_init:function(){var c=this;this.$textarea.wrap('<div class="flex-text-wrap" />').before("<pre><span /><br /><br /></pre>");this.$span=this.$textarea.prev().find("span");this.$textarea.on("input propertychange keyup change",function(){c._mirror()});b.valHooks.textarea={get:function(d){return d.value.replace(/\r?\n/g,"\r\n")}};this._mirror()},_mirror:function(){this.$span.text(this.$textarea.val())}};b.fn.flexText=function(){return this.each(function(){if(!b.data(this,"flexText")){b.data(this,"flexText",new a(this))}})}})(jQuery);

/*!
 * TouchSlider v1.3.1
 * http://www.qiqiboy.com
 */
;eval(function(B,D,A,G,E,F){function C(A){return A<62?String.fromCharCode(A+=A<26?65:A<52?71:-4):A<63?'_':A<64?'$':C(A>>6)+C(A&63)}while(A>0)E[C(G--)]=D[--A];return B.replace(/[\w\$]+/g,function(A){return E[A]==F[A]?A:E[A]})}('(1(J,K){"use strict";a F=("createTouch"W 3)||("ontouchstart"W J),H=3.createElement("div").n,D=(1(){a U={OTransform:["-C0-","otransitionend"],WebkitTransform:["-webkit-","webkitTransitionEnd"],MozTransform:["-moz-","BG"],msTransform:["-BX-","MSTransitionEnd"],transform:["","BG"]},T;d(T W U)V(T W H)s U[T];s l})(),I=[["Bh","k","BJ"],["height","top","bottom"]],L=D&&D[Q],B=1(U){s(U+"").CE(/^-BX-/,"BX-").CE(/-([CB-C2]|[Q-C3])/ig,1(T,U){s(U+"").toUpperCase()})},C=1(U){a T=B(L+U);s(U W H)&&U||(T W H)&&T},G=1(T,U){d(a A W U)V(v T[A]=="6")T[A]=U[A];s T},E=1(U){a A=U.children||U.childNodes,T=[],B=Q;d(;B<A.t;B++)V(A[B].BO===R)T.push(A[B]);s T},M=1(U,T){a B=Q,A=U.t;d(;B<A;B++)V(T.Bs(U[B],B,U[B])===l)BT},U=1(U){U=T.Y.BA(U);U.$()},A=D[R]||"",T=1(U,A){V(!(f instanceof T))s e T(U,A);V(v U!="CH"&&!U.BO){A=U;U=A.Cv}V(!U.BO)U=3.getElementById(U);f.Z=G(A||{},f.Cw);f.y=U;V(f.y){f.8=f.y.CV||3.CK;f.Bw()}};T.Y=T.prototype={Cn:"R.B6.R",Cw:{Cv:"slider",r:Q,BV:j,BD:600,0:5000,5:"k",CR:"center",Cu:j,Bj:l,Cf:j,B$:e CC,CO:e CC},c:1(U,D){V(v D=="CH"){a T=3.Ca&&3.Ca.CX&&CX(U,Bp)||U.currentStyle||U.n||{};s T[B(D)]}h{a A,C;d(A W D){V(A=="B1")C=("Cb"W H)?"Cb":"styleFloat";h C=B(A);U.n[C]=D[A]}}},9:1(T,A,B,U){V(T.Bd){T.Bd(A,B,U);s j}h V(T.Bn){T.Bn("Ce"+A,B);s j}s l},Cy:1(T,A,B,U){V(T.Bd){T.removeEventListener(A,B,U);s j}h V(T.Bn){T.detachEvent("Ce"+A,B);s j}s l},BA:1(B){a T={},C="changedTouches BS Bt w view which B5 B4 fromElement offsetX offsetY o q toElement".split(" ");B=B||J.event;M(C,1(){T[f]=B[f]});T.w=B.w||B.srcElement||3;V(T.w.BO===B6)T.w=T.w.CV;T.$=1(){B.$&&B.$();T.CM=B.CM=l};T.Bu=1(){B.Bu&&B.Bu();T.B8=B.B8=j};V(F&&T.BS.t){T.o=T.BS.Cq(Q).o;T.q=T.BS.Cq(Q).q}h V(v B.o=="6"){a A=3.documentElement,U=3.CK;T.o=B.B5+(A&&A.CI||U&&U.CI||Q)-(A&&A.CG||U&&U.CG||Q);T.q=B.B4+(A&&A.Cx||U&&U.Cx||Q)-(A&&A.B2||U&&U.B2||Q)}T.CA=B;s T},i:1(T,U){s 1(){s T.apply(U,arguments)}},Bw:1(){a C=F||!f.Z.Cf,T=C?"touchstart":"mousedown",B=C?"touchmove":"mousemove",U=C?"touchend":"mouseup";f.u=E(f.y);f.t=f.u.t;f.Z.0=BR(f.Z.0);f.Z.BD=BR(f.Z.BD);f.Z.r=BR(f.Z.r);f.Z.BV=!!f.Z.BV;f.Z.0=g.BP(f.Z.0,f.Z.BD);f.CY=!!F;f.css3transition=!!D;f.m=f.Z.r<Q||f.Z.r>=f.t?Q:f.Z.r;V(f.t<R)s l;f.Ch=3.createComment("\\P Powered by CZ C1"+f.Cn+",\\P author: Bc,\\P email: imqiqiboy@gmail.Be,\\P blog: Cg://www.Bc.Be,\\P Cj: Cg://Cj.Be/Bc\\P");f.8.BY(f.Ch,f.y);Cm(f.Z.5){BB"CD":BB"down":f.5=f.Z.5;f.2=R;BT;BB"BJ":f.5="BJ";Ct:f.5=f.5||"k";f.2=Q;BT}f.9(f.y,T,f.i(f.CS,f),l);f.9(3,B,f.i(f.Cp,f),l);f.9(3,U,f.i(f.Bb,f),l);f.9(3,"touchcancel",f.i(f.Bb,f),l);f.9(f.y,A,f.i(f.BG,f),l);f.9(J,"BH",f.i(1(){_(f.CU);f.CU=BQ(f.i(f.BH,f),Co)},f),l);V(f.Z.Bj){f.9(f.y,"mousewheel",f.i(f.Bv,f),l);f.9(f.y,"DOMMouseScroll",f.i(f.Bv,f),l)}f.z=f.Z.BV;f.BH()},x:1(C,T,D){a A=Q,E=T,U=B("-"+C);d(;E<D;E++)A+=f["Br"+U](f.u[E]);s A},BZ:1(D,A){a T=B("-"+D),U=f.x(D,A,A+R),C=f.x(D,Q,A)+f["Br"+T](f.y)/S-f["Bg"+T](f.y)/S;Cm(f.Z.CR){BB"k":s-C;BB"BJ":s f[D]-U-C;Ct:s(f[D]-U)/S-C}},BH:1(){_(f.BW);a A=f,D,C=I[f.2][Q],U=B("-"+C),T=f.c(f.8,"Bo");f.c(f.8,{CT:"By",B0:"By",listStyle:"Cr",Bo:T=="static"?"Cs":T});f[C]=f["Bg"+U](f.8);D={B1:f.2?"Cr":"k",display:"block"};M(f.u,1(){V(A.Z.Cu)D[C]=A[C]-A["Bm"+U](f)-A["Bf"+U](f)-A["BE"+U](f)+"X";A.c(f,D)});f.Bz=f.x(C,Q,f.t);D={Bo:"Cs",CT:"By"};D[L+"Bx-Cz"]="B9";D[C]=f.Bz+"X";D[I[f.2][R]]=f.t?f.BZ(C,f.m)+"X":Q;f.c(f.y,D);f.c(f.8,{B0:"visible"});f.z&&f.Ba();s f},BN:1(U,A){a B=I[f.2][R],H=I[f.2][Q],F=C("Bx"),M=BC(f.c(f.y,B))||Q,O,BM={},D,J=f.x(H,U,U+R);U=g.Bq(g.BP(Q,U),f.t-R);A=v A=="6"?f.Z.BD:BR(A);O=f.BZ(H,U);D=O-M,A=g.b(D)<J?g.B_(g.b(D)/J*A):A;V(F){BM[F]=B+" ease "+A+"BX";BM[B]=O+"X";f.c(f.y,BM)}h{a N=f,K=Q,L=A/CF,T=1(T,A,B,U){s-B*((T=T/U-R)*T*T*T-R)+A},G=1(){V(K<L){K++;N.y.n[B]=g.B_(T(K,M,D,L))+"X";N.BW=BQ(G,CF)}h{N.y.n[B]=O+"X";N.BG({CP:B})}};_(f.BW);G()}a E=f.u[f.m];f.m=U;f.Z.B$.Bs(f,U,E);s f},Ba:1(){_(f.p);f.z=j;f.p=BQ(f.i(1(){f.5=="k"||f.5=="CD"?f.BL():f.BK()},f),f.Z.0);s f},CN:1(){_(f.p);f.z=l;s f},stop:1(){f.CN();s f.BN(Q)},BK:1(A,T){_(f.p);a U=f.m;A=v A=="6"?A=R:A%f.t;U-=A;V(T===l)U=g.BP(U,Q);h U=U<Q?f.t+U:U;s f.BN(U)},BL:1(A,T){_(f.p);a U=f.m;V(v A=="6")A=R;U+=A;V(T===l)U=g.Bq(U,f.t-R);h U%=f.t;s f.BN(U)},CS:1(A){A=f.BA(A);a T=A.w.nodeName.B7();V(!f.CY&&(T=="CB"||T=="img"))A.$();f.Cy(f.y,"Cl",U);f.4=[A.o,A.q];f.y.n[B(L+"Bx-Cz")]="B9";f.Bl=+e Cc;f.Bi=BC(f.c(f.y,I[f.2][R]))||Q},Cp:1(A){V(!f.4||A.Bt&&A.Bt!==R)s;A=f.BA(A);f.BI=[A.o,A.q];a U,T=I[f.2][R],C=I[f.2][Q],B=f.BI[f.2]-f.4[f.2];V(f.7||v f.7=="6"&&g.b(B)>=g.b(f.BI[R-f.2]-f.4[R-f.2])){A.$();B=B/((!f.m&&B>Q||f.m==f.t-R&&B<Q)?(g.b(B)/f[C]+R):R);f.y.n[T]=f.Bi+B+"X";V(J.CJ!=Bp){U=CJ();V(U.Cd)U.Cd();h V(U.Ci)U.Ci()}V(B&&v f.7=="6"){f.7=j;_(f.p);_(f.BW)}}h f.7=l},Bb:1(E){V(f.4){V(f.7){a K=I[f.2][Q],C=I[f.2][R],J=f.BI[f.2]-f.4[f.2],H=g.b(J),A=H/J,T,G,B,D=f.m,F=Q;f.9(f.y,"Cl",U);V(H>20){G=BC(f.c(f.y,I[f.2][R]));do{V(D>=Q&&D<f.t){B=f.BZ(K,D);T=f.x(K,D,D+R)}h{D+=A;BT}}while(g.b(B-G)>T/S&&(D-=A))F=g.b(D-f.m);V(!F&&+e Cc-f.Bl<250)F=R}J>Q?f.BK(F,l):f.BL(F,l);f.z&&f.Ba()}BF f.Bi;BF f.BI;BF f.4;BF f.7;BF f.Bl}},Bv:1(C){V(f.Z.Bj){C=f.BA(C);a D=f,B=C.CA,T=Q,A=Q,U;V("Bk"W B){T=B.Bk;A=B.wheelDeltaY}h V("B3"W B)A=B.B3;h V("CL"W B)A=-B.CL;h s;V(!f.2&&g.b(T)>g.b(A))U=T;h V(A&&(!B.Bk||f.2&&g.b(T)<g.b(A)))U=A;V(U){C.$();_(f.Ck);f.Ck=BQ(1(){U>Q?D.BK(R,l):D.BL(R,l)},Co)}}},BG:1(U){V(U.CP==I[f.2][R]){f.Z.CO.Bs(f,f.m,f.u[f.m]);f.z&&f.Ba()}},BU:1(){V(f.5==Bp)f.Bw();h{f.u=E(f.y);f.t=f.u.t;f.m=g.BP(g.Bq(f.t-R,f.m),Q);f.BH()}},CQ:1(U){f.y.appendChild(U);f.BU()},prepend:1(U){f.t?f.BY(U,Q):f.CQ(U)},BY:1(U,T){f.y.BY(U,f.u[T]);V(f.m>=T)f.m++;f.BU()},remove:1(U){f.y.removeChild(f.u[U]);V(f.m>=U)f.m--;f.BU()}};M(["Width","Height"],1(B,A){a U=A.B7();M(["Bm","Bf","BE"],1(C,U){T.Y[U+A]=1(T){s(BC(f.c(T,U+"-"+I[B][R]+(U=="BE"?"-Bh":"")))||Q)+(BC(f.c(T,U+"-"+I[B][S]+(U=="BE"?"-Bh":"")))||Q)}});T.Y["Bg"+A]=1(U){s U["CW"+A]-f["Bf"+A](U)-f["BE"+A](U)};T.Y["Br"+A]=1(U){s U["CW"+A]+f["Bm"+A](U)}});J.CZ=T})(window)','n|0|1|2|_|$|if|in|px|fn|cfg|var|abs|css|for|new|this|Math|else|bind|true|left|false|index|style|pageX|timer|pageY|begin|return|length|slides|typeof|target|getSum|element|playing|timeout|function|vertical|document|startPos|direction|undefined|scrolling|container|addListener|clearTimeout|preventDefault|eventHook|case|parseFloat|speed|border|delete|transitionend|resize|stopPos|right|prev|next|P|slide|nodeType|max|setTimeout|parseInt|touches|break|refresh|auto|aniTimer|ms|insertBefore|getPos|play|_end|qiqiboy|addEventListener|com|padding|get|width|_pos|mouseWheel|wheelDeltaX|startTime|margin|attachEvent|position|null|min|getOuter|call|scale|stopPropagation|mouseScroll|setup|transition|hidden|total|visibility|float|clientTop|wheelDelta|clientY|clientX|3|toLowerCase|cancelBubble|0ms|ceil|before|origEvent|a|Function|up|replace|10|clientLeft|string|scrollLeft|getSelection|body|detail|returnValue|pause|after|propertyName|append|align|_start|overflow|resizeTimer|parentNode|offset|getComputedStyle|touching|TouchSlider|defaultView|cssFloat|Date|empty|on|mouseDrag|http|comment|removeAllRanges|weibo|mouseTimer|click|switch|version|100|_move|item|none|relative|default|fixWidth|id|_default|scrollTop|removeListener|duration|o|v|z|9'.split('|'),169,183,{},{}))

/*!
 * flib mobile version, matching jquery mobile library
 *
 * @author Gavin<laigw.vip@gmail.com>
 * 
 * zip by: dean.edwards.name/packer/,Pack+Shrink
 */
;(function( $, w, UNDEF ) {
	
	// Trim whitespace characters
	w.String.prototype.trim = function() {
		return this.replace( /(^\s+)|(\s+$)/g, '' );
	};
	w.String.prototype.ltrim = function() {
		return this.replace( /(^\s+)/g, '' );
	};
	w.String.prototype.rtrim = function() {
		return this.replace( /(\s+$)/g, '' );
	};
	w.String.prototype.isUrl = function() {
		return /(http(s?):\/\/)([\w-]+\.)+[\w:]{2,}(\/[\x00-\xff^ ]*)?/i.test(this);
	};
	w.String.prototype.isMobile = function() {
		return /^0?1[3458]\d{9}$/.test(this);
	};
	w.String.prototype.isEmail = function() {
		return /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/.test(this);
	};
	
	// q like
	w.String.prototype.qlike = function(what) {
		var match = false;
		if (this==what) {
			match = true;
		}else if (this.match(F.genQRegex(what))) {
			match = true;
		}
		return match;
	};
	
	// Define FUI class
	var FUI = function(options) {
		this.user  = {};
		this.data  = {};
		this.box   = {};
		this.queue = {};
		this.location = {};
		this.cache = new Array();
		this.cacheCtrl = new Array();
		this.hashData  = new Array();
		this.qsSep     = ',';
		this.hashTag   = '/';
		this.hashReqId = '_hr';
		this.hashUrlGenCallback = null;
		this.hashHist = 'hashhist';
		this.hashHistSize = 10;	// history queue size default to 10
		this.defaultHash = '#'+this.hashTag;
		this.cleanUrl = true;
		this.timer = null;
		this.maxage= 60; // default maxage
		this.container = '#ifr-body';
		this.lang = {
			loading : '数据加载中...',
			posting : '数据保存中...',
			saved   : '数据已保存'
		};
		
		this.toploadingid  = 'toploading';	// top loading box id
		this.toploadingobj = null;			// top loading box dom jQuery object 
		this.topmainboxid  = 'mainMsgBox';	// top main msg box id
		this.topmainboxobj = null;			// top main msg box dom jQuery object
		
		//~ event handling
		this.event = {
			flag: {downpull: false},
			_events: {},
			on: function (type, fn) {
				if ( !this._events[type] ) {
					this._events[type] = [];
				}
				this._events[type].push(fn);
			},
			off: function (type, fn) {
				if ( !this._events[type] ) {
					return;
				}
				
				var index = this._events[type].indexOf(fn);
				
				if ( index > -1 ) {
					this._events[type].splice(index, 1);
				}
			},
			execEvent: function (type, call_obj) {
				if ( !this._events[type] ) {
					return;
				}
				
				var i = 0,
					l = this._events[type].length;

				if ( !l ) {
					return;
				}

				call_obj = call_obj ? call_obj : this;
				for ( ; i < l; i++ ) {
					this._events[type][i].apply(call_obj, [].slice.call(arguments, 2));
				}
			},
			reset: function() {
				this._events = {};
				this.flag.downpull = false;
			}
		};
		
		//~ initialize queue
		this.createQueue(this.hashHist, this.hashHistSize);
	};
	FUI.prototype = {
			
		// Cache dealing
		getCache: function(key) {
			var d = this.cache[key];
			var t = this.time();
			if (d==UNDEF || t >= d.expires) {
				d = '';
			}
			return d;
		},
		setCache: function(key, value) {
			this.cache[key] = value;
		},
		clearCache: function(key) {
			this.cacheDirty(key);
		},
		clearCacheAll: function() {
			this.cache = new Array();
			this.cacheCtrl = new Array();
		},
		cacheDirty: function(url) {
			var key = this.genCacheKey(url);
			var cd  = this.getCache(key);
			if (cd) {
				cd.expires = this.time()-3600;
				this.setCache(key, cd);
			}
		},
		genCacheKey: function($str) {
			return hex_md5($str);
		},
		
		// Util functions
		datetime: function() {
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
			return ''+Y+'-'+m+'-'+d+' '+H+':'+i+':'+s;
		},
		time: function() {
			var now = new Date();
			return parseInt(now.getTime()/1000);
		},
		setTimer: function(timer) {
			this.timer = timer;
		},
		getTimer: function (timer) {
			return this.timer || null;
		},
		
		// Queue dealing
		createQueue: function(name, size) {
			if (size==UNDEF || size<0 || name==UNDEF || name=='') {
				return false;
			}
			this.queue[name] = {container:[],size:size};
			return this.queue[name];
		},
		inQueue: function(name,value) {
			if (name==UNDEF || name=='') {
				return false;
			}
			if (this.queue[name].container.length>=this.queue[name].size) {
				this.queue[name].container.shift();
			}
			return this.queue[name].container.push(value);
		},
		outQueue: function(name) {
			if (name==UNDEF || name=='') {
				return false;
			}
			var ret = null;
			if (this.queue[name].container.length>0) {
				ret = this.queue[name].container.shift();
			}
			return ret;
		},
		indexQueue: function(name,index) {
			if (name==UNDEF || name=='') {
				return false;
			}
			if (index==UNDEF) {
				index = 0;
			}
			var ret = null;
			if (index>=0) {
				if (this.queue[name].container.length>index) {
					ret = this.queue[name].container[index];
				}
			}
			else {
				var len = this.queue[name].container.length;
				if (Math.abs(index)<=len) {
					index = len+index;
					ret = this.indexQueue(name, index);
				}
			}
			return ret;
		},
		clearQueue: function(name) {
			if (name==UNDEF || name=='') {
				return false;
			}
			this.queue[name].container.length = 0;
			return true;
		},
		getQueueLen: function(name) {
			if (name==UNDEF || name=='') {
				return false;
			}
			return this.queue[name].container.length;
		},
		getQueueHead: function(name) {
			if (name==UNDEF || name=='') {
				return false;
			}
			var ret = null;
			if (this.queue[name].container.length>0) {
				ret = this.queue[name].container[0];
			}
			return ret;
		},
		getQueueEnd: function(name) {
			if (name==UNDEF || name=='') {
				return false;
			}
			var ret = null;
			var len = this.queue[name].container.length;
			if (len>0) {
				ret = this.queue[name].container[len-1];
			}
			return ret;
		},
		
		// The following two method are used to show or hide top loading tips
		ajaxStart: function(type, callback) {
			if (type==UNDEF || (type!='load'&&type!='post')) {
				type = 'load';
			}
			var showtips = this.lang.loading;
			if (type=='post') {
				showtips = this.lang.posting;
				if (!this.topmainboxobj) this.topmainboxobj = $('#'+this.topmainboxid);
				if (typeof callback == 'function') {
					callback(this.topmainboxobj);
				}
				else {
					if (typeof callback == 'string') { //indicating transfer msg, not function
						showtips = callback;
					}
					this.topmainboxobj.find('span').text(showtips).attr('class','msg msg-loading');
					this.topmainboxobj.show();
				}
			}
			else {
				if (!this.toploadingobj) this.toploadingobj = $('#'+this.toploadingid);
				if (typeof callback == 'function') {
					callback(this.toploadingobj);
				}
				else {
					if (typeof callback == 'string') { //indicating transfer msg, not function
						showtips = callback;
					}
					this.toploadingobj.find('span').text(showtips);
					this.toploadingobj.show();
				}
			}
		},
		ajaxComplete: function(type, callback) {
			if (type==UNDEF || (type!='load'&&type!='post')) {
				type = 'load';
			}
			if (type=='post') {
				if (!this.topmainboxobj) this.topmainboxobj = $('#'+this.topmainboxid);
				if (typeof callback == 'function') {
					callback(this.topmainboxobj);
				}
				else {
					var _msg = this.lang.saved;
					if (typeof callback == 'string') { //indicating transfer msg, not function
						_msg = callback;
					}
					this.topmainboxobj.find('span').attr('class','msg').text(_msg);
					var _oThis = this;
					setTimeout(function(){_oThis.topmainboxobj.fadeOut();},3000);
				}
			}
			else {
				if (!this.toploadingobj) this.toploadingobj = $('#'+this.toploadingid);
				if (typeof callback == 'function') {
					callback(this.toploadingobj);
				}
				else {
					this.toploadingobj.hide();
				}
			}
		},
		
		// Wrapper of jquery ajax method
		ajax: function(options) {
			$.ajax(options);
		},
		load: function(url, data, callback) {
			$.load(url, data, callback);
		},
		get: function(url, data, callback) {
			$.get(url, data, callback);
		},
		getScript: function(url, callback) {
			$.getScript(url, callback);
		},
		getJSON: function(url, data, callback, ajax_start_cb, ajax_complete_cb) {
			if (typeof data == 'function') {
				ajax_complete_cb = ajax_start_cb;
				ajax_start_cb = callback;
				callback = data;
				data = {};
			} else {
				data.maxage = data.maxage != UNDEF ? parseInt(data.maxage) : this.maxage;
			}
			var key = this.genCacheKey(url);
			var cd  = this.getCache(key);
			if (cd && data.maxage>0) {
				callback(cd);
			} else {
				var oThis = this;
				if (this.cacheCtrl[key] == UNDEF) {
					this.cacheCtrl[key] = {ajaxing: 0};
				}
				if (this.cacheCtrl[key].ajaxing) {
					return;
				}
				this.cacheCtrl[key].ajaxing = 1;
				this.ajaxStart('load',ajax_start_cb);
				$.getJSON(url, data, function(d){
					d.expires = F.time() + parseInt(d.maxage);
					oThis.setCache(key, d);
					oThis.cacheCtrl[key].ajaxing = 0;
					oThis.ajaxComplete('load',ajax_complete_cb);
					callback(d);
				});
			}
		},
		post: function(url, data, callback, ajax_start_cb, ajax_complete_cb) {
			if (typeof data == 'function') {
				ajax_complete_cb = ajax_start_cb;
				ajax_start_cb = callback;
				callback = data;
				data = {};
			}
			var oThis = this;
			this.ajaxStart('post',ajax_start_cb);
			$.ajax({
				url: url,
				type:'post',
				data: data,
				dataType: 'json',
				success: function(data){
					oThis.ajaxComplete('post',ajax_complete_cb);
					callback(data);
				},
				error: function(xhr, status, error) {
					oThis.ajaxComplete('post',ajax_complete_cb);
				}
			});
		},
		
		// Hash request core
		hashReq: function(hash, data, callback, options) {
			if (typeof data == 'function') {
				options = callback;
				callback = data;
				data = {};
			}
			data.maxage = data.maxage != UNDEF ? parseInt(data.maxage) : this.maxage;
			data[this.hashReqId] = 1;
			
			if (typeof options == 'undefined') {
				options = {};
			}
			options = $.extend({
				changeHash: true,
				forceRefresh: false,
				renderPrepend: '',
				renderAppend: ''
			},options);
			
			var gourl = '';
			if (hash) {
				var showhash = hash;
				var pos = hash.indexOf('#');
				if (pos>=0) {
					showhash = hash.substring(pos);
				}
				else {
					showhash = '#'+this.hashTag+hash;
				}
				this.inQueue(this.hashHist, {hash:showhash,data:data}); //new hash enter history queue
				gourl = this.parseHashUrl(showhash);
				if (options.changeHash) this.setHash(showhash);
			}
			else {
				gourl = w.location.href.replace(/#.*$/g, ''); //right trim the '#xxx' part
			}
			if (options.forceRefresh) {
				this.clearCache(gourl);
			}
			var _realurl = this.genRealUrl(gourl, data);
			this.getJSON(gourl,data,function(d){
				d.body = options.renderPrepend + '<script type="text/javascript">F.location.hashreq=1;F.location.href=\''+_realurl+'\';</script>' + d.body + options.renderAppend; //prepend F.location.href
				callback(d);
			});
			return false;
		},
		// Hash load page
		hashLoad: function(hash, data, default_cb, options) {
			if (typeof data == 'function') {
				options = default_cb;
				default_cb = data;
				data = {};
			}
			if (!hash) hash = w.location.hash;
			if (!hash) hash = this.defaultHash;
			
			if (this.isReqHash(hash)) {
				this.hashReq(hash, data, function(ret){
					var $c = typeof(options.container)=='undefined' ? $(F.container) : (typeof options.container == 'object' ? options.container : $(options.container));
					$c.html(ret.body);
					if (typeof default_cb == 'function') {
						default_cb(ret);
					}
				}, options);
			}
			return false;
		},
		// Hash Reload Page
		hashReload: function(){
			this.clearCacheAll();
			$(window).hashchange();
			return false;
		},
		// Go to hash reference uri
		hashRefer: function(default_hash, force_refresh) {
			if (!default_hash) {
				var _hashHist = this.indexQueue(this.hashHist,-2); //the last second one of the queue
				if (_hashHist) default_hash = _hashHist.hash;
			}
			if (force_refresh && default_hash) {
				this.clearCache(this.parseHashUrl(default_hash));
			}
			this.setHash(default_hash);
			$(window).hashchange();
			return false;
		},
		// Go to hash reference uri
		hashRedirect: function(gohash, force_refresh) {
			if (!gohash) gohash = this.defaultHash;
			var pos = gohash.indexOf('#');
			if (pos < 0) gohash = '#'+gohash;
			if (force_refresh && gohash) {
				this.clearCache(this.parseHashUrl(gohash));
			}
			this.setHash(gohash);
			$(window).hashchange();
			return false;
		},
		// alias of hashRedirect
		hashGo: function(gohash, force_refresh) {
			return this.hashRedirect(gohash, force_refresh);
		},
		// Generate q RegExp
		genQRegex: function(q) {
			var regex = q.replace( /%d/g, '(\\d+)' );
			regex = regex.replace( /%s/g, '([0-9a-zA-Z_-]+)' );
			return new RegExp('^'+regex+'$');		
		},
		// Generate real request url
		genRealUrl: function(url, data) {
			var real_url = url;
			if (data) {
				var _ppart = '';
				for(var _dk in data) {
					_ppart += '&' + _dk + '=' + data[_dk];
				}
				if (''!=_ppart) {
					_ppart = _ppart.substring(1);
					real_url += (url.lastIndexOf('?')<0 ? '?' : '&') + _ppart;
				}
			}
			return real_url;
		},
		// get hash referrer uri
		getHashReferUrl: function(default_hash, force_refresh) {
			var _hashHist = this.indexQueue(this.hashHist,-2); //the last second one of the queue
			if (_hashHist) {
				return this.genRealUrl(this.parseHashUrl(_hashHist.hash), _hashHist.data);
			}
			return '';
		},
		// Check whether is valid request hash
		isReqHash: function(hash) {
			return hash.match(new RegExp('^#'+this.hashTag+'.*'));
		},
		// Parse hash url
		parseHashUrl: function(hash) {
			var hashurl = '';
			var pos = hash.indexOf('#');
			if (pos>=0) {
				hash = hash.substring(pos+2); //begin with like '#~xxx'
			}
			if (this.qsSep != '?') {
				hash = hash.replace(this.qsSep,'?'); //support separated by ','
			}
			var harr    = hash.split('?');
			var hashurl = '/'+harr[0];
			if (typeof this.hashUrlGenCallback == 'function') {
				hashurl = this.hashUrlGenCallback(harr[0]);
			}
			if (harr.length>1 && harr[1].trim()!='') {
				var tarr;
				harr  = harr[1].split('&');
				for(var i=0,j=0; i<harr.length; ++i) {
					tarr = harr[i].split('=');
					if (tarr[0]=='maxage') continue;
					hashurl += (j==0&&this.cleanUrl?'?':'&') + harr[i];
					++j;
				}
			}
			return hashurl;
		},
		// Get location hash
		getHash: function() {
			var hash = w.location.hash;
			return hash ? hash : '';
		},
		// Set location hash
		setHash: function(h) {
			w.location.hash = h;
		},
		
		// Util functions
		locatePoint: function(id, position){
			var aCtrl = null;
			if (typeof id != 'string') {
				aCtrl = id;
			}else{
				aCtrl = document.getElementById(id);
			}
			if (aCtrl.setSelectionRange) {
				setTimeout(function(){aCtrl.setSelectionRange(position, position);aCtrl.focus();}, 0);
			}else if (aCtrl.createTextRange) {
				var textArea=aCtrl;
				var tempText=textArea.createTextRange();
				tempText.collapse(true); 
				//tempText.moveStart('character',-1+position);
				tempText.moveStart('character',position);
				tempText.select();
				setTimeout(function(){textArea.focus();}, 0);
			}
		},
		placeCaretAtEnd: function(el) {
			el.focus();
			if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
				var range = document.createRange();
				range.selectNodeContents(el);
				range.collapse(false);
				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
			} else if (typeof document.body.createTextRange != "undefined") {
				var textRange = document.body.createTextRange();
				textRange.moveToElementText(el);
				textRange.collapse(false);
				textRange.select();
			}
		},
		getBrowserWidth: function() {
			return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		},
		getBrowserHeight: function() {
			return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
		},
		
		// Console Log
		log: function($msg) {
			w.console.log($msg);
		}
	};
	
	w.F = w.FUI = new FUI();
	w._gPop	     = null; // global pop reference
	w._gPopHtml	 = null; // global html pop reference
	w._gWaitSucc = 1;	 // 1 second while success
	w._gWaitWarn = 2;	 // 2 second while warning
	w._gWaitFail = 3;	 // 3 seconds while fail
	
	/*
	 * 传递函数给whenReady()
	 * 当文档解析完毕且为操作准备就绪时，函数作为document的方法调用
	 */
	var whenReady = (function() {               //这个函数返回whenReady()函数
	    var funcs = [];             //当获得事件时，要运行的函数
	    var ready = false;          //当触发事件处理程序时,切换为true
	    
	    //当文档就绪时,调用事件处理程序
	    function handler(e) {
	        if(ready) return;       //确保事件处理程序只完整运行一次
	        
	        //如果发生onreadystatechange事件，但其状态不是complete的话,那么文档尚未准备好
	        if(e.type === 'onreadystatechange' && document.readyState !== 'complete') {
	            return;
	        }
	        
	        //运行所有注册函数
	        //注意每次都要计算funcs.length
	        //以防这些函数的调用可能会导致注册更多的函数
	        for(var i=0; i<funcs.length; i++) {
	            funcs[i].call(document);
	        }
	        //事件处理函数完整执行,切换ready状态, 并移除所有函数
	        ready = true;
	        funcs = null;
	    }
	    //为接收到的任何事件注册处理程序
	    if(document.addEventListener) {
	        document.addEventListener('DOMContentLoaded', handler, false);
	        document.addEventListener('readystatechange', handler, false);            //IE9+
	        window.addEventListener('load', handler, false);
	    }else if(document.attachEvent) {
	        document.attachEvent('onreadystatechange', handler);
	        window.attachEvent('onload', handler);
	    }
	    //返回whenReady()函数
	    return function whenReady(fn) {
	        if(ready) { fn.call(document); }
	        else { funcs.push(fn); }
	    }
	})();
	F.onDocReady = whenReady;
	
})(jQuery, window);