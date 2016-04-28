/*!
 * Moble pic cropper
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
(function( w, $, UNDEF ) {
"use strict";

/*!
 * 屏幕点类
 */
var MPoint=function(_x, _y){this.x=_x||0;this.y=_y||0;};

/*!
 * jQuery Double Tap Plugin.
 * @dependency MPoint class
 */
// Determine if we on touchable device
$.fn.doubletap = function(onDoubleTapCallback, onTapCallback, delay){
	var eventName, action;
	delay = delay ? delay : 500;
	eventName = ("createTouch" in document) ? 'touchend' : 'click';
	
	$(this).get(0).addEventListener(eventName,function(event){
		var now = new Date().getTime();
		var lastTouch = $(this).data('lastTouch') || now + 1 /** the first time this will make delta a negative number */;
		var delta = now - lastTouch;
		clearTimeout(action);
		
		//check point distance
		var currPoint = new MPoint();
		currPoint.x   = (event.type === 'touchend') ? event.changedTouches[0].clientX : event.clientX;
		currPoint.y   = (event.type === 'touchend') ? event.changedTouches[0].clientY : event.clientY;
		var lastPoint = $(this).data('lastPoint') || currPoint;
		var ptDistance= Math.sqrt( Math.pow(lastPoint.x-currPoint.x, 2) + Math.pow(lastPoint.y-currPoint.y, 2) );
		
		if(delta<500 && delta>0 && ptDistance<20){/**point distance can not be too big*/
			if(onDoubleTapCallback != null && typeof onDoubleTapCallback == 'function'){
				onDoubleTapCallback(event);
			}
		}else{
			$(this).data('lastTouch', now);
			$(this).data('lastPoint', currPoint);
			action = setTimeout(function(evt){
				if(onTapCallback != null && typeof onTapCallback == 'function'){
					onTapCallback(evt);
				}
				clearTimeout(action);   // clear the timeout
			}, delay, [event]);
		}
		$(this).data('lastTouch', now);
		$(this).data('lastPoint', currPoint);		
		
	},false);
};

/*
 * DEPENDENCY
 * Javascript BinaryFile
 * Copyright (c) 2008 Jacob Seidelin, jseidelin@nihilogic.dk, http://blog.nihilogic.dk/
 * Licensed under the MPL License [http://www.nihilogic.dk/licenses/mpl-license.txt]
 * Zip by UglifyJS on http://tool.css-js.com/ 
 */
var BinaryFile=function(a,b,c){var d=a,e=b||0,f=0;this.getRawData=function(){return d},"string"==typeof a?(f=c||d.length,this.getByteAt=function(a){return 255&d.charCodeAt(a+e)},this.getBytesAt=function(a,b){var f,c=[];for(f=0;b>f;f++)c[f]=255&d.charCodeAt(a+f+e);return c}):"unknown"==typeof a&&(f=c||IEBinary_getLength(d),this.getByteAt=function(a){return IEBinary_getByteAt(d,a+e)},this.getBytesAt=function(a,b){return new VBArray(IEBinary_getBytesAt(d,a+e,b)).toArray()}),this.getLength=function(){return f},this.getSByteAt=function(a){var b=this.getByteAt(a);return b>127?b-256:b},this.getShortAt=function(a,b){var c=b?(this.getByteAt(a)<<8)+this.getByteAt(a+1):(this.getByteAt(a+1)<<8)+this.getByteAt(a);return 0>c&&(c+=65536),c},this.getSShortAt=function(a,b){var c=this.getShortAt(a,b);return c>32767?c-65536:c},this.getLongAt=function(a,b){var c=this.getByteAt(a),d=this.getByteAt(a+1),e=this.getByteAt(a+2),f=this.getByteAt(a+3),g=b?(((c<<8)+d<<8)+e<<8)+f:(((f<<8)+e<<8)+d<<8)+c;return 0>g&&(g+=4294967296),g},this.getSLongAt=function(a,b){var c=this.getLongAt(a,b);return c>2147483647?c-4294967296:c},this.getStringAt=function(a,b){var e,c=[],d=this.getBytesAt(a,b);for(e=0;b>e;e++)c[e]=String.fromCharCode(d[e]);return c.join("")},this.getCharAt=function(a){return String.fromCharCode(this.getByteAt(a))},this.toBase64=function(){return window.btoa(d)},this.fromBase64=function(a){d=window.atob(a)}};

/*
 * DEPENDENCY
 * Javascript EXIF Reader 0.1.6
 * Copyright (c) 2008 Jacob Seidelin, jseidelin@nihilogic.dk, http://blog.nihilogic.dk/
 * Licensed under the MPL License [http://www.nihilogic.dk/licenses/mpl-license.txt]
 * Zip by UglifyJS on http://tool.css-js.com/
 */
var EXIF=function(){function g(a){return!!a.exifdata}function h(a,b){BinaryAjax(a.src,function(c){var d=i(c.binaryResponse);a.exifdata=d||{},b&&b.call(a)})}function i(b){if(255!=b.getByteAt(0)||216!=b.getByteAt(1))return!1;for(var e,c=2,d=b.getLength();d>c;){if(255!=b.getByteAt(c))return a&&console.log("Not a valid marker at offset "+c+", found: "+b.getByteAt(c)),!1;if(e=b.getByteAt(c+1),22400==e)return a&&console.log("Found 0xFFE1 marker"),l(b,c+4,b.getShortAt(c+2,!0)-2);if(225==e)return a&&console.log("Found 0xFFE1 marker"),l(b,c+4,b.getShortAt(c+2,!0)-2);c+=2+b.getShortAt(c+2,!0)}}function j(b,c,d,e,f){var i,j,l,g=b.getShortAt(d,f),h={};for(l=0;g>l;l++)i=d+12*l+2,j=e[b.getShortAt(i,f)],!j&&a&&console.log("Unknown tag: "+b.getShortAt(i,f)),h[j]=k(b,i,c,d,f);return h}function k(a,b,c,d,e){var i,j,k,l,m,n,f=a.getShortAt(b+2,e),g=a.getLongAt(b+4,e),h=a.getLongAt(b+8,e)+c;switch(f){case 1:case 7:if(1==g)return a.getByteAt(b+8,e);for(i=g>4?h:b+8,j=[],l=0;g>l;l++)j[l]=a.getByteAt(i+l);return j;case 2:return i=g>4?h:b+8,a.getStringAt(i,g-1);case 3:if(1==g)return a.getShortAt(b+8,e);for(i=g>2?h:b+8,j=[],l=0;g>l;l++)j[l]=a.getShortAt(i+2*l,e);return j;case 4:if(1==g)return a.getLongAt(b+8,e);for(j=[],l=0;g>l;l++)j[l]=a.getLongAt(h+4*l,e);return j;case 5:if(1==g)return m=a.getLongAt(h,e),n=a.getLongAt(h+4,e),k=new Number(m/n),k.numerator=m,k.denominator=n,k;for(j=[],l=0;g>l;l++)m=a.getLongAt(h+8*l,e),n=a.getLongAt(h+4+8*l,e),j[l]=new Number(m/n),j[l].numerator=m,j[l].denominator=n;return j;case 9:if(1==g)return a.getSLongAt(b+8,e);for(j=[],l=0;g>l;l++)j[l]=a.getSLongAt(h+4*l,e);return j;case 10:if(1==g)return a.getSLongAt(h,e)/a.getSLongAt(h+4,e);for(j=[],l=0;g>l;l++)j[l]=a.getSLongAt(h+8*l,e)/a.getSLongAt(h+4+8*l,e);return j}}function l(f,g){if("Exif"!=f.getStringAt(g,4))return a&&console.log("Not valid EXIF data! "+f.getStringAt(g,4)),!1;var h,i,k,l,m,n=g+6;if(18761==f.getShortAt(n))h=!1;else{if(19789!=f.getShortAt(n))return a&&console.log("Not valid TIFF data! (no 0x4949 or 0x4D4D)"),!1;h=!0}if(42!=f.getShortAt(n+2,h))return a&&console.log("Not valid TIFF data! (no 0x002A)"),!1;if(8!=f.getLongAt(n+4,h))return a&&console.log("Not valid TIFF data! (First offset not 8)",f.getShortAt(n+4,h)),!1;if(i=j(f,n,n+8,c,h),i.ExifIFDPointer){l=j(f,n,n+i.ExifIFDPointer,b,h);for(k in l){switch(k){case"LightSource":case"Flash":case"MeteringMode":case"ExposureProgram":case"SensingMethod":case"SceneCaptureType":case"SceneType":case"CustomRendered":case"WhiteBalance":case"GainControl":case"Contrast":case"Saturation":case"Sharpness":case"SubjectDistanceRange":case"FileSource":l[k]=e[k][l[k]];break;case"ExifVersion":case"FlashpixVersion":l[k]=String.fromCharCode(l[k][0],l[k][1],l[k][2],l[k][3]);break;case"ComponentsConfiguration":l[k]=e.Components[l[k][0]]+e.Components[l[k][1]]+e.Components[l[k][2]]+e.Components[l[k][3]]}i[k]=l[k]}}if(i.GPSInfoIFDPointer){m=j(f,n,n+i.GPSInfoIFDPointer,d,h);for(k in m){switch(k){case"GPSVersionID":m[k]=m[k][0]+"."+m[k][1]+"."+m[k][2]+"."+m[k][3]}i[k]=m[k]}}return i}function m(a,b){return a.complete?(g(a)?b&&b.call(a):h(a,b),!0):!1}function n(a,b){return g(a)?a.exifdata[b]:void 0}function o(a){if(!g(a))return{};var b,c=a.exifdata,d={};for(b in c)c.hasOwnProperty(b)&&(d[b]=c[b]);return d}function p(a){if(!g(a))return"";var b,c=a.exifdata,d="";for(b in c)c.hasOwnProperty(b)&&(d+="object"==typeof c[b]?c[b]instanceof Number?b+" : "+c[b]+" ["+c[b].numerator+"/"+c[b].denominator+"]\r\n":b+" : ["+c[b].length+" values]\r\n":b+" : "+c[b]+"\r\n");return d}function q(a){return i(a)}var a=!1,b={36864:"ExifVersion",40960:"FlashpixVersion",40961:"ColorSpace",40962:"PixelXDimension",40963:"PixelYDimension",37121:"ComponentsConfiguration",37122:"CompressedBitsPerPixel",37500:"MakerNote",37510:"UserComment",40964:"RelatedSoundFile",36867:"DateTimeOriginal",36868:"DateTimeDigitized",37520:"SubsecTime",37521:"SubsecTimeOriginal",37522:"SubsecTimeDigitized",33434:"ExposureTime",33437:"FNumber",34850:"ExposureProgram",34852:"SpectralSensitivity",34855:"ISOSpeedRatings",34856:"OECF",37377:"ShutterSpeedValue",37378:"ApertureValue",37379:"BrightnessValue",37380:"ExposureBias",37381:"MaxApertureValue",37382:"SubjectDistance",37383:"MeteringMode",37384:"LightSource",37385:"Flash",37396:"SubjectArea",37386:"FocalLength",41483:"FlashEnergy",41484:"SpatialFrequencyResponse",41486:"FocalPlaneXResolution",41487:"FocalPlaneYResolution",41488:"FocalPlaneResolutionUnit",41492:"SubjectLocation",41493:"ExposureIndex",41495:"SensingMethod",41728:"FileSource",41729:"SceneType",41730:"CFAPattern",41985:"CustomRendered",41986:"ExposureMode",41987:"WhiteBalance",41988:"DigitalZoomRation",41989:"FocalLengthIn35mmFilm",41990:"SceneCaptureType",41991:"GainControl",41992:"Contrast",41993:"Saturation",41994:"Sharpness",41995:"DeviceSettingDescription",41996:"SubjectDistanceRange",40965:"InteroperabilityIFDPointer",42016:"ImageUniqueID"},c={256:"ImageWidth",257:"ImageHeight",34665:"ExifIFDPointer",34853:"GPSInfoIFDPointer",40965:"InteroperabilityIFDPointer",258:"BitsPerSample",259:"Compression",262:"PhotometricInterpretation",274:"Orientation",277:"SamplesPerPixel",284:"PlanarConfiguration",530:"YCbCrSubSampling",531:"YCbCrPositioning",282:"XResolution",283:"YResolution",296:"ResolutionUnit",273:"StripOffsets",278:"RowsPerStrip",279:"StripByteCounts",513:"JPEGInterchangeFormat",514:"JPEGInterchangeFormatLength",301:"TransferFunction",318:"WhitePoint",319:"PrimaryChromaticities",529:"YCbCrCoefficients",532:"ReferenceBlackWhite",306:"DateTime",270:"ImageDescription",271:"Make",272:"Model",305:"Software",315:"Artist",33432:"Copyright"},d={0:"GPSVersionID",1:"GPSLatitudeRef",2:"GPSLatitude",3:"GPSLongitudeRef",4:"GPSLongitude",5:"GPSAltitudeRef",6:"GPSAltitude",7:"GPSTimeStamp",8:"GPSSatellites",9:"GPSStatus",10:"GPSMeasureMode",11:"GPSDOP",12:"GPSSpeedRef",13:"GPSSpeed",14:"GPSTrackRef",15:"GPSTrack",16:"GPSImgDirectionRef",17:"GPSImgDirection",18:"GPSMapDatum",19:"GPSDestLatitudeRef",20:"GPSDestLatitude",21:"GPSDestLongitudeRef",22:"GPSDestLongitude",23:"GPSDestBearingRef",24:"GPSDestBearing",25:"GPSDestDistanceRef",26:"GPSDestDistance",27:"GPSProcessingMethod",28:"GPSAreaInformation",29:"GPSDateStamp",30:"GPSDifferential"},e={ExposureProgram:{0:"Not defined",1:"Manual",2:"Normal program",3:"Aperture priority",4:"Shutter priority",5:"Creative program",6:"Action program",7:"Portrait mode",8:"Landscape mode"},MeteringMode:{0:"Unknown",1:"Average",2:"CenterWeightedAverage",3:"Spot",4:"MultiSpot",5:"Pattern",6:"Partial",255:"Other"},LightSource:{0:"Unknown",1:"Daylight",2:"Fluorescent",3:"Tungsten (incandescent light)",4:"Flash",9:"Fine weather",10:"Cloudy weather",11:"Shade",12:"Daylight fluorescent (D 5700 - 7100K)",13:"Day white fluorescent (N 4600 - 5400K)",14:"Cool white fluorescent (W 3900 - 4500K)",15:"White fluorescent (WW 3200 - 3700K)",17:"Standard light A",18:"Standard light B",19:"Standard light C",20:"D55",21:"D65",22:"D75",23:"D50",24:"ISO studio tungsten",255:"Other"},Flash:{0:"Flash did not fire",1:"Flash fired",5:"Strobe return light not detected",7:"Strobe return light detected",9:"Flash fired, compulsory flash mode",13:"Flash fired, compulsory flash mode, return light not detected",15:"Flash fired, compulsory flash mode, return light detected",16:"Flash did not fire, compulsory flash mode",24:"Flash did not fire, auto mode",25:"Flash fired, auto mode",29:"Flash fired, auto mode, return light not detected",31:"Flash fired, auto mode, return light detected",32:"No flash function",65:"Flash fired, red-eye reduction mode",69:"Flash fired, red-eye reduction mode, return light not detected",71:"Flash fired, red-eye reduction mode, return light detected",73:"Flash fired, compulsory flash mode, red-eye reduction mode",77:"Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected",79:"Flash fired, compulsory flash mode, red-eye reduction mode, return light detected",89:"Flash fired, auto mode, red-eye reduction mode",93:"Flash fired, auto mode, return light not detected, red-eye reduction mode",95:"Flash fired, auto mode, return light detected, red-eye reduction mode"},SensingMethod:{1:"Not defined",2:"One-chip color area sensor",3:"Two-chip color area sensor",4:"Three-chip color area sensor",5:"Color sequential area sensor",7:"Trilinear sensor",8:"Color sequential linear sensor"},SceneCaptureType:{0:"Standard",1:"Landscape",2:"Portrait",3:"Night scene"},SceneType:{1:"Directly photographed"},CustomRendered:{0:"Normal process",1:"Custom process"},WhiteBalance:{0:"Auto white balance",1:"Manual white balance"},GainControl:{0:"None",1:"Low gain up",2:"High gain up",3:"Low gain down",4:"High gain down"},Contrast:{0:"Normal",1:"Soft",2:"Hard"},Saturation:{0:"Normal",1:"Low saturation",2:"High saturation"},Sharpness:{0:"Normal",1:"Soft",2:"Hard"},SubjectDistanceRange:{0:"Unknown",1:"Macro",2:"Close view",3:"Distant view"},FileSource:{3:"DSC"},Components:{0:"",1:"Y",2:"Cb",3:"Cr",4:"R",5:"G",6:"B"}};return{readFromBinaryFile:q,pretty:p,getTag:n,getAllTags:o,getData:m,Tags:b,TiffTags:c,GPSTags:d,StringValues:e}}();

/**
 * MPicCrop class constructor
 * 
 * @param config JSON object, JSON对象，包括：
 * 
 * bgWrapId string, 图片背景包裹元素ID,必须
 * baseWidth integer, 图片背景设计原宽度，可选，默认640，固定不变
 * baseHeight integer, 图片背景设计原高度，可选，默认960，固定不变
 * 
 */
var MPicCrop = function(config) {
	
	this.config = $.extend({
		bgWrapId     : 'MPicCrop',
		bgImg        : 'img/zfy_box_bg.png',
		winBgImg     : 'img/zfy_box_cover.png',
		targetImg    : 'img/zfy_box_pic.png',
		baseWidth    : 640,
		baseHeight   : 960,
		canvasClass  : 'picData',
		winClass     : 'picWin',
		winLeft      : 127,
		winTop       : 142,
		winWidth     : 385,
		winHeight    : 385,
		gestureWidth : 80,    //zoom手势框宽
		gestureHeight: 80,    //zoom手势框高
		gestureDirection: 'horizontal',  //zoom手势方向,'vertical' or 'horizontal'
		zoomHandleClass: 'zoomHandle',
		inputLayerClass: 'inputLayer',
		logClass     : 'debug',
		logEnable    : true
	}, config);
	
	this.$bgWrap     = UNDEF; //背景box
	this.$log        = UNDEF; //打印日志容器句柄
	this.$cropWin    = UNDEF; //裁剪窗口
	this.$inputLayer = UNDEF; //包含input file的层
	this._canvas     = UNDEF; //画布
	this._canvas_ctx = UNDEF; //画布context
	this._img        = UNDEF; //最后加载图片句柄
	this._img_meta   = UNDEF; //存放最后绘画的imgMeta信息
	this._inpfile    = UNDEF; //input file控件句柄
	this._fileReader = new FileReader();
	
	this.scope       = {
		mouseMoveStepLen : 2,  //鼠标移动步长，单位px，当大于此长度时才处理变化
		maxGestureZoomLen: 0,  //最大手势放大长度
		minImgZoomWidth  : 0,  //最小图片缩放宽度
		minImgZoomHeight : 0,  //最小图片缩放高度
		currImgCropLeft  : 0,  //当前图片截取坐标x
		currImgCropTop   : 0,  //当前图片截取坐标y
		currImgZoomWidth : 0,  //当前图片缩放宽度
		currImgZoomHeight: 0,  //当前图片缩放高度
		currScaleImgWidth: 0,  //当前手势开始缩放时的图片宽度
		currScaleImgHeight:0,  //当前手势开始缩放时的图片高度
		exif             : null//图片的exif信息            
	};    //核心逻辑内部数据
	
	this.support = {
		touch: ("createTouch" in document) //是否支持触摸设备
	}; //客户端支持情况
	
	this._init(this.scope);
};
w.MPicCrop = MPicCrop;

MPicCrop.prototype = {
	
	//调试打印
	log: function(text) {
		if (this.config.logEnable) {
			if (this.$log === UNDEF) {
				this.$log = $('#'+this.config.bgWrapId+' .'+this.config.logClass);
				if (this.$log.size()==0) {
					this.$log = null;
				}
			}
			if (this.$log) this.$log.html(text+"<br/>"+this.$log.html());
			w.console.log(text);
		}
		return this;
	},
	
	//启动核心逻辑
	run: function() {
		this._watch(this.scope);
		return this;
	},
	
	//获取截取到的图片Data
	getCroppedImageData: function(mime) {
		var output = document.createElement("canvas");
		output.width  = this._img_meta.cropWidth;
		output.height = this._img_meta.cropHeight;
		output.getContext("2d").drawImage(this._img, this._img_meta.cropLeft, this._img_meta.cropTop, this._img_meta.cropWidth, this._img_meta.cropHeight, 0, 0, output.width, output.height);
		return output.toDataURL(mime || "image/jpeg");
	},
	
	//初始化
	_init: function(scope) {
		this.$bgWrap     = $('#'+this.config.bgWrapId);
		this.$cropWin    = $('.'+this.config.winClass,this.$bgWrap);
		this.$inputLayer = $('.'+this.config.inputLayerClass,this.$bgWrap);
		this._canvas     = $('canvas.'+this.config.canvasClass,this.$bgWrap).get(0);
		this._canvas_ctx = this._canvas.getContext("2d");
		this._inpfile    = this.$inputLayer.find('input[type=file]').get(0);
		
		this.scope.maxGestureZoomLen = this.calcTriangleHyp(this.config.gestureWidth,this.config.gestureHeight);
		
		this.$bgWrap.css('background','url('+this.config.bgImg+') no-repeat scroll 0 0 / 100% auto transparent');
		this.$cropWin.css('background','url('+this.config.winBgImg+') no-repeat scroll 0 0 / 100% auto transparent');
		
		var mpcThis = this;
		$(w).resize(function(){
			var ratio,new_x,new_y,new_w,new_h;
			ratio = mpcThis.calcRatio(mpcThis.$bgWrap.width(), mpcThis.config.baseWidth);
			new_x = Math.round(mpcThis.config.winLeft*ratio);
			new_y = Math.round(mpcThis.config.winTop*ratio);
			new_w = Math.round(mpcThis.config.winWidth*ratio);
			new_h = Math.round(mpcThis.config.winHeight*ratio);
			
			$(mpcThis._canvas).css({left:new_x+'px',top:new_y+'px'}).attr('width',new_w).attr('height',new_h);
			mpcThis.$cropWin.css({left:new_x+'px',top:new_y+'px',width:new_w+'px',height:new_h+'px'});
			mpcThis.$inputLayer.css({left:new_x+'px',top:new_y+'px',width:new_w+'px',height:new_h+'px'});
			
			if (mpcThis.config.targetImg!='') {
				mpcThis._img = new Image()
				mpcThis._img.onload = function() {
					var img_meta = mpcThis._calcCanvasImgZoomMeta();
					scope.minImgZoomWidth   = img_meta.natureWidth;
					scope.minImgZoomHeight  = img_meta.natureHeight;
					mpcThis._drawImage(img_meta);
					
					if (scope.exif && scope.exif.Orientation) {
						mpcThis.$inputLayer.hide();
						//mpcThis.log('orientation: '+scope.exif.Orientation);
						//mpcThis._checkImgExif(mpcThis._img, scope.exif);
					}
				}
				mpcThis._img.src = mpcThis.config.targetImg;
			}
		})
		.resize();
	},
	
	//核心逻辑运行
	_watch: function(scope) {
		var oThis  = this;
		var pStart = new MPoint();
		var zooming= false, moving = false, gesture_zooming = false;
        
        var $handle = document.getElementsByClassName(oThis.config.zoomHandleClass)[0];
        var $cropWin= oThis.$cropWin.get(0);
        
	    
		//fileReader的onload事件
		oThis._fileReader.onload = function(e) {
			oThis._img.src = e.target.result;
			var byteString = atob(e.target.result.split(',')[1]);
			var binary = new BinaryFile(byteString, 0, byteString.length);
			scope.exif = EXIF.readFromBinaryFile(binary);
		};
		//input[type=file]的onchange事件
		oThis._inpfile.onchange = function(e) {
          var files = e.target.files;
          oThis._fileReader.readAsDataURL(files[0]);
		};
		//一些事件
		oThis.$inputLayer.doubletap(function(e){
	    	e.preventDefault();
	    	e.stopPropagation();
	    	oThis.$inputLayer.hide();
		});
		oThis.$cropWin.doubletap(function(e){
	    	e.preventDefault();
	    	e.stopPropagation();
			oThis.$inputLayer.show();
		});
		
		//小窗手势鼠标按下事件
		scope.onHandleMouseDown = function(e) {
			
		    e.preventDefault();
		    e.stopPropagation();

		    zooming = true;
		    moving  = false;
		    gesture_zooming = false;
		    
		    pStart.x = (e.type === 'touchstart') ? e.changedTouches[0].clientX : e.clientX;
		    pStart.y = (e.type === 'touchstart') ? e.changedTouches[0].clientY : e.clientY;
		    
		    if (oThis.support.touch) {
			    oThis.addRootEventListener('touchend', scope.onHandleMouseUp);
			    oThis.addRootEventListener('touchmove', scope.onHandleMouseMove);
		    }else{
			    oThis.addRootEventListener('mouseup', scope.onHandleMouseUp);
			    oThis.addRootEventListener('mousemove', scope.onHandleMouseMove);
		    }
		};

		//小窗手势鼠标弹起事件
		scope.onHandleMouseUp = function(e) {

			if (!zooming) {
				return false;
			}
			
		    e.preventDefault();
		    e.stopPropagation();
		    
		    pStart.x = 0;
		    pStart.y = 0;
		    
			gesture_zooming = false;
        	zooming = false;
            moving  = false;

		    if (oThis.support.touch) {
			    oThis.removeRootEventListener('touchend', scope.onHandleMouseUp);
			    oThis.removeRootEventListener('touchmove', scope.onHandleMouseMove);
		    }else{
			    oThis.removeRootEventListener('mouseup', scope.onHandleMouseUp);
			    oThis.removeRootEventListener('mousemove', scope.onHandleMouseMove);
		    }
		};

		//小窗手势鼠标移动事件
		scope.onHandleMouseMove = function(e) {
			
			if (!zooming) {
				return false;
			}
			
			e.preventDefault();
		    e.stopPropagation();
			
		    var pCurr = new MPoint();
		    pCurr.x   = (e.type === 'touchmove') ? e.changedTouches[0].clientX : e.clientX;
		    pCurr.y   = (e.type === 'touchmove') ? e.changedTouches[0].clientY : e.clientY;
		    var dis   = oThis.calcPointDistance(pStart, pCurr);
		    
			// 太小距离不响应变化以提高效率
			if (dis < scope.mouseMoveStepLen) {
				return false;
			}
		    
		    var diffX = pCurr.x - pStart.x; // how far mouse has moved in current drag
		    var diffY = pCurr.y - pStart.y; // how far mouse has moved in current drag
		    
		    pStart.x = pCurr.x;
		    pStart.y = pCurr.y;
		    
		    var direction = 1; // >0: 放大; <0: 缩小
		    if (oThis.config.gestureDirection=='vertical') {
			    if (diffY < 0 || (diffY === 0 && diffX > 0)) direction = 1;
			    else direction = -1;
		    }else{
			    if (diffX > 0 || (diffX === 0 && diffY < 0)) direction = 1;
			    else direction = -1;		    	
		    }
		    
		    oThis._zoomImage(dis, direction);
		};
	    if (oThis.support.touch) {
			$handle.addEventListener('touchstart', scope.onHandleMouseDown, false);
			$handle.addEventListener('touchend', scope.onHandleMouseUp, false);
			$handle.addEventListener('touchmove', scope.onHandleMouseMove, false);
	    }else{
			$handle.addEventListener('mousedown', scope.onHandleMouseDown, false);
			$handle.addEventListener('mouseup', scope.onHandleMouseUp, false);
			$handle.addEventListener('mousemove', scope.onHandleMouseMove, false);
	    }
	    
	    //裁剪窗口鼠标左键按下事件
        scope.onCropWinMouseDown = function(e) {
        	
            if (gesture_zooming) { //确保不干扰多点触碰事件
            	return false;
            }
            
            e.preventDefault();
            e.stopPropagation();
            
        	pStart.x = e.type === 'touchstart' ? e.changedTouches[0].clientX : e.clientX;
        	pStart.y = e.type === 'touchstart' ? e.changedTouches[0].clientY : e.clientY;
            
        	moving   = true;
        	zooming  = false;
        	gesture_zooming = false;
        };
        
        //裁剪窗口鼠标左键弹起事件
        scope.onCropWinMouseUp = function(e) {
            
        	if (!moving && !gesture_zooming) {
            	return false;
            }
            
            e.preventDefault();
            e.stopPropagation();

            pStart.x = 0;
            pStart.y = 0;
            
			gesture_zooming = false;
        	zooming = false;
            moving  = false;
        };
        
        //裁剪窗口鼠标移动事件
        scope.onCropWinMouseMove = function(e) {

            if (!moving) {
                return false;
            }
            
            e.preventDefault();
            e.stopPropagation();
            
		    var pCurr = new MPoint();
		    pCurr.x   = (e.type === 'touchmove') ? e.changedTouches[0].clientX : e.clientX;
		    pCurr.y   = (e.type === 'touchmove') ? e.changedTouches[0].clientY : e.clientY;
		    
            var diffX = pCurr.x - pStart.x; // how far mouse has moved in current drag
            var diffY = pCurr.y - pStart.y; // how far mouse has moved in current drag
            
            pStart.x  = pCurr.x;
            pStart.y  = pCurr.y;

            oThis._moveImage(diffX, diffY);
        };
	    if (oThis.support.touch) {
			$cropWin.addEventListener('touchstart', scope.onCropWinMouseDown, false);
			$cropWin.addEventListener('touchend', scope.onCropWinMouseUp, false);
			$cropWin.addEventListener('touchmove', scope.onCropWinMouseMove, false);
	    }else{
			$cropWin.addEventListener('mousedown', scope.onCropWinMouseDown, false);
			$cropWin.addEventListener('mouseup', scope.onCropWinMouseUp, false);
			$cropWin.addEventListener('mousemove', scope.onCropWinMouseMove, false);
	    }
	    
	    //裁剪窗口双手指触摸开始事件
	    scope.onCropWinGestureStart = function(e) {
	    	
            e.preventDefault();
            e.stopPropagation();
            
            gesture_zooming = true;
        	zooming = false;
            moving  = false;
            
            //开始手势缩放即记录初始scale的宽高值
            scope.currScaleImgWidth  = scope.currImgZoomWidth;
            scope.currScaleImgHeight = scope.currImgZoomHeight;
	    };
	    
	    //裁剪窗口双手指触摸结束事件
	    scope.onCropWinGestureEnd = function(e) {
			
	    	if (!gesture_zooming) {
				return false;
			}
			
	    	e.preventDefault();
	    	e.stopPropagation();
			
			gesture_zooming = false;
        	zooming = false;
            moving  = false;
	    };
	    
	    //裁剪窗口双手指触摸移动事件
	    scope.onCropWinGestureChange = function(e) {
	    	
			if (!gesture_zooming) {
				return false;
			}
			
	    	if (e.scale < 1.1 && e.scale > 0.9) { //太小的缩放忽略
	    		return false;
	    	}
	    	
	    	e.preventDefault();
	    	e.stopPropagation();
	    	
		    var direction = 1; // >0: 放大; <0: 缩小
		    if (e.scale < 1) direction = -1;
		    
		    oThis._zoomImage(e.scale, direction, true);
	    };
	    if (oThis.support.touch) {
			$cropWin.addEventListener('gesturestart', scope.onCropWinGestureStart, false);
			$cropWin.addEventListener('gestureend', scope.onCropWinGestureEnd, false);
			$cropWin.addEventListener('gesturechange', scope.onCropWinGestureChange, false);
	    }
	},
	
	//计算画布图片的缩放信息，返回结构体res;
	_calcCanvasImgZoomMeta: function(zoom, isGestureScale) {
		if (typeof(zoom)=='undefined') zoom = 0;
		if (typeof(isGestureScale)=='undefined') isGestureScale = false;
		
		var res = this._imgMeta();
		
		if (!zoom) { //初始状态
			var r = this.calcRatio(res.originWidth,res.originHeight);
			if (r >= 1) { //横行图片
				res.natureHeight = this._canvas.height; //高对齐
				res.natureWidth  = Math.round(res.natureHeight * r);
			} else {//竖型图片
				res.natureWidth  = this._canvas.width;  //宽对齐
				res.natureHeight = Math.round(res.natureWidth / (r||1));
			}
			
			//初始截取中间位置
			res.natureLeft = Math.round((res.natureWidth - this._canvas.width) / 2);
			res.natureTop  = Math.round((res.natureHeight - this._canvas.height) / 2);			
		} else { //缩放状态
	        
			var pendingWidth, pendingHeight;
			if (!isGestureScale) { //变化的是绝对值，zoom值的正负表示放大或缩小
	            var gestureZoomRatio  = this.calcRatio(Math.abs(zoom), this.scope.maxGestureZoomLen);
	            var incWidth  = Math.round(this.scope.currImgZoomWidth * gestureZoomRatio);
	            var incHeight = Math.round(this.scope.currImgZoomHeight * gestureZoomRatio);
	            if (zoom < 0) {
	            	incWidth  = -incWidth;
	            	incHeight = -incHeight;
	            }
	            pendingWidth  = this.scope.currImgZoomWidth  + incWidth;
	            pendingHeight = this.scope.currImgZoomHeight + incHeight;
			}
			else { //变化的是比例，zoom值的负值不表示方向，应忽略, zoom>1 放大；zoom<1 缩小
				zoom = Math.abs(zoom);
	            pendingWidth  = this.scope.currScaleImgWidth * zoom;
	            pendingHeight = this.scope.currScaleImgHeight* zoom;				
			}
            
        	if (pendingWidth > this._img.width) { //图片放大到大于图片原尺寸
        		pendingWidth  = this._img.width;
        		pendingHeight = this._img.height;
        	} else if (pendingWidth < this.scope.minImgZoomWidth) { //图片缩小到小于窗口框
        		pendingWidth  = this.scope.minImgZoomWidth;
        		pendingHeight = this.scope.minImgZoomHeight;
        	}
        	//修正数据
        	if (pendingWidth  > this._img.width)  pendingWidth  = this._img.width;
        	if (pendingHeight > this._img.height) pendingHeight = this._img.height;
        	res.natureWidth  = pendingWidth;
        	res.natureHeight = pendingHeight;
        	
        	//检查left,top
        	var rX = this.calcRatio(this.scope.currImgCropLeft,this.scope.currImgZoomWidth);
        	var rY = this.calcRatio(this.scope.currImgCropTop, this.scope.currImgZoomHeight);
	        res.natureLeft   = Math.round(res.natureWidth * rX);
	        res.natureTop    = Math.round(res.natureHeight* rY);
		}
		
        //保存全局数据
        this.scope.currImgCropLeft  = res.natureLeft;
        this.scope.currImgCropTop   = res.natureTop;
        this.scope.currImgZoomWidth = res.natureWidth;
        this.scope.currImgZoomHeight= res.natureHeight;
		
		this._calcCropImgMeta(res);
		
		return res;
	},
	
	//计算画布图片的移动信息，返回结构体res;
	_calcCanvasImgMoveMeta: function(moveX, moveY) {
		
		var res = this._imgMeta();
    	res.natureWidth  = this.scope.currImgZoomWidth;
    	res.natureHeight = this.scope.currImgZoomHeight;
		res.natureLeft   = this.scope.currImgCropLeft;
		res.natureTop    = this.scope.currImgCropTop;
		
        var rw = this.calcRatio(Math.abs(moveX), this._canvas.width);
        var rh = this.calcRatio(Math.abs(moveY), this._canvas.height);
        
        var incW = Math.round(res.natureWidth  * rw);
        var incH = Math.round(res.natureHeight * rh);
        if (moveX > 0) incW = -incW; //反向变化
        if (moveY > 0) incH = -incH;
        res.natureLeft += incW;
        res.natureTop  += incH;
        
        //修正数据
        if (res.natureLeft < 0) res.natureLeft = 0;
        if (res.natureTop < 0)  res.natureTop = 0;
        if ((res.natureLeft + this._canvas.width) > res.natureWidth) 
        	res.natureLeft = res.natureWidth - this._canvas.width;
        if ((res.natureTop + this._canvas.height) > res.natureHeight) 
        	res.natureTop = res.natureHeight - this._canvas.height;
        
        //保存
        this.scope.currImgCropLeft = res.natureLeft;
        this.scope.currImgCropTop  = res.natureTop;
        
		this._calcCropImgMeta(res);

		return res;
	},
	
	//在画布上缩放图片
	_zoomImage: function(distiance, direction, isGestureScale) {
		if (typeof(isGestureScale)=='undefined') isGestureScale = false;
	    if (direction < 0) distiance = -distiance;
	    this._drawImage(this._calcCanvasImgZoomMeta(distiance, isGestureScale));
	},
	
	//在画布上移动图片
	_moveImage: function(moveX, moveY) {
		this._drawImage(this._calcCanvasImgMoveMeta(moveX, moveY));
	},
	
	//返回 imgMeta 初始结构体
	_imgMeta: function() {
		return {
			originWidth : this._img.width,
			originHeight: this._img.height,
			natureLeft  : 0,
			natureTop   : 0,
			natureWidth : 0,
			natureHeight: 0,
			cropLeft    : 0,
			cropTop     : 0,
			cropWidth   : 0,
			cropHeight  : 0
		};
	},
	
	//计算imgMeta的裁剪图片部分信息，需要originWidth,originHeight,natureWidth,natureHeight,natureLeft,natureTop
	_calcCropImgMeta: function(imgMeta) {
		var rx = this.calcRatio(imgMeta.originWidth, imgMeta.natureWidth),
            ry = this.calcRatio(imgMeta.originHeight, imgMeta.natureHeight);

		imgMeta.cropWidth  = Math.round(this._canvas.width * rx);
		imgMeta.cropHeight = Math.round(this._canvas.height * ry);
		imgMeta.cropLeft   = Math.round(imgMeta.natureLeft * rx);
		imgMeta.cropTop    = Math.round(imgMeta.natureTop * ry);

		//检查修正数据
		if (imgMeta.cropLeft < 0) imgMeta.cropLeft = 0;
		if (imgMeta.cropTop < 0) imgMeta.cropTop = 0;
		if ((imgMeta.cropLeft + imgMeta.cropWidth) > imgMeta.originWidth) imgMeta.cropLeft= imgMeta.originWidth-imgMeta.cropWidth;
		if ((imgMeta.cropTop + imgMeta.cropHeight) > imgMeta.originHeight)imgMeta.cropTop = imgMeta.originHeight-imgMeta.cropHeight;
		
		return imgMeta;
	},
	
	//在画布上绘制图片
	_drawImage: function(imgMeta) {
		this._img_meta = imgMeta; //存放最后要绘画的图片信息
		this._canvas_ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);
		this._canvas_ctx.drawImage(this._img,imgMeta.cropLeft,imgMeta.cropTop,imgMeta.cropWidth,imgMeta.cropHeight,0,0,this._canvas.width, this._canvas.height);
	},
	
	_checkImgExif: function(img,exif) {
		var ctx = this._canvas_ctx,imgWidth=img.width,imgHeight=img.height;
        // change mobile orientation, if required
        switch(exif.Orientation){
          case 1:
              // nothing
              break;
          case 2:
              // horizontal flip
              ctx.translate(imgWidth, 0);
              ctx.scale(-1, 1);
              break;
          case 3:
              // 180 rotate left
              ctx.translate(imgWidth, imgHeight);
              ctx.rotate(Math.PI);
              break;
          case 4:
              // vertical flip
              ctx.translate(0, imgHeight);
              ctx.scale(1, -1);
              break;
          case 5:
              // vertical flip + 90 rotate right
              ctx.rotate(0.5 * Math.PI);
              ctx.scale(1, -1);
              break;
          case 6:
              // 90 rotate right
              ctx.rotate(0.5 * Math.PI);
              ctx.translate(0, -imgHeight);
              break;
          case 7:
              // horizontal flip + 90 rotate right
              ctx.rotate(0.5 * Math.PI);
              ctx.translate(imgWidth, -imgHeight);
              ctx.scale(-1, 1);
              break;
          case 8:
              // 90 rotate left
              ctx.rotate(-0.5 * Math.PI);
              ctx.translate(-imgWidth, 0);
              break;
          default:
              break;
        }
	},
	
	//添加事件绑定
	addRootEventListener: function(eventName, func) {
		document.documentElement.addEventListener(eventName, func, false);
	},
	
	//删除事件绑定
	removeRootEventListener: function(eventName, func) {
		document.documentElement.removeEventListener(eventName, func);
	},
	
	//提高浮点精度到小数点后3位
	to2Dp: function(val) {
		return Math.round(val * 1000) / 1000;
	},
	
	//计算两点间距离
	calcPointDistance: function(p1, p2) {
		return this.calcTriangleHyp( (p2.x-p1.x), (p2.y-p1.y) );
	},
	
	//计算直接三角形斜边长度
	calcTriangleHyp: function(side_1,side_2) {
		return Math.sqrt( Math.pow(side_1, 2) + Math.pow(side_2, 2) );
	},
	
	//计算图片缩放率
	calcRatio: function(numerator,denominator) {
		return numerator/(denominator||1);
	},
	
	//是否可用FileReader
	isAvailable: function() {
		return typeof(FileReader) !== "undefined";
	},
	
	//是否图片类型的MIME
	isImage: function(file) {
		return (/image/i).test(file.type);
	}
};

})(window, jQuery);