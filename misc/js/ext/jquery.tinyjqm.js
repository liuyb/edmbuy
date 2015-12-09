/*!
 * part of jquery mobile
 * See http://jquerymobile.com
 */
(function ( root, doc, factory ) {
	if ( typeof define === "function" && define.amd ) {
		// AMD. Register as an anonymous module.
		define( [ "jquery" ], function ( $ ) {
			factory( $, root, doc );
			return $.mobile;
		});
	} else {
		// Browser globals
		factory( root.jQuery, root, doc );
	}
}( this, document, function( jQuery, window, document, undefined ) {
/*-----BEGIN factory callback function-----*/

//jQuery fn extension
(function( $, window, undefined ) {
	
	var _animationEndEvents  = "webkitAnimationEnd oAnimationEnd oanimationend animationend msAnimationEnd";
	var _transitionEndEvents = "webkitTransitionEnd oTransitionEnd otransitionend transitionend msTransitionEnd";

	//animation complete callback
	$.fn.animationComplete = function( callback ) {
		if ( $.support.cssTransitions ) {
			return $( this ).one( _animationEndEvents, callback );
		}
		else{
			// defer execution for consistency between webkit/non webkit
			setTimeout( callback, 0 );
			return $( this );
		}
	};
	//transition complete callback
	$.fn.transitionComplete = function( callback ) {
		if ( $.support.cssTransitions ) {
			return $( this ).one( _transitionEndEvents, callback );
		}
		else{
			// defer execution for consistency between webkit/non webkit
			setTimeout( callback, 0 );
			return $( this );
		}
	};	
	
})( jQuery, this );

//jQuery mobile object initialization
(function( $ ) {
	$.mobile = {};
}( jQuery ));

(function( $, window, undefined ) {
	
	// jQuery.mobile configurable options
	$.mobile = $.extend($.mobile, {

		// define the window and the document objects
		window: $( window ),
		document: $( document ),

		getScreenHeight: function() {
			// Native innerHeight returns more accurate value for this across platforms,
			// jQuery version is here as a normalized fallback for platforms like Symbian
			return window.innerHeight || $.mobile.window.height();
		}
	}, $.mobile );
	
})( jQuery, this );

(function( $, undefined ) {

	/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas. Dual MIT/BSD license */
	window.matchMedia = window.matchMedia || (function( doc, undefined ) {
		var bool,
			docElem = doc.documentElement,
			refNode = docElem.firstElementChild || docElem.firstChild,
			// fakeBody required for <FF4 when executed in <head>
			fakeBody = doc.createElement( "body" ),
			div = doc.createElement( "div" );

		div.id = "mq-test-1";
		div.style.cssText = "position:absolute;top:-100em";
		fakeBody.style.background = "none";
		fakeBody.appendChild(div);

		return function(q){

			div.innerHTML = "&shy;<style media=\"" + q + "\"> #mq-test-1 { width: 42px; }</style>";

			docElem.insertBefore( fakeBody, refNode );
			bool = div.offsetWidth === 42;
			docElem.removeChild( fakeBody );

			return {
				matches: bool,
				media: q
			};

		};

	}( document ));

	// $.mobile.media uses matchMedia to return a boolean.
	$.mobile.media = function( q ) {
		return window.matchMedia( q ).matches;
	};

})(jQuery);

(function( $, undefined ) {
	var support = {
		touch: "ontouchend" in document
	};

	$.mobile.support = $.mobile.support || {};
	$.extend( $.support, support );
	$.extend( $.mobile.support, support );
}( jQuery ));

(function( $, undefined ) {
	$.extend( $.support, {
		orientation: "orientation" in window && "onorientationchange" in window
	});
}( jQuery ));

(function( $, undefined ) {

	// thx Modernizr
	function propExists( prop ) {
		var uc_prop = prop.charAt( 0 ).toUpperCase() + prop.substr( 1 ),
			props = ( prop + " " + vendors.join( uc_prop + " " ) + uc_prop ).split( " " );

		for ( var v in props ) {
			if ( fbCSS[ props[ v ] ] !== undefined ) {
				return true;
			}
		}
	}

	var fakeBody = $( "<body>" ).prependTo( "html" ),
		fbCSS = fakeBody[ 0 ].style,
		vendors = [ "Webkit", "Moz", "O" ],
		webos = "palmGetResource" in window, //only used to rule out scrollTop
		opera = window.opera,
		operamini = window.operamini && ({}).toString.call( window.operamini ) === "[object OperaMini]",
		bb = window.blackberry && !propExists( "-webkit-transform" ); //only used to rule out box shadow, as it's filled opaque on BB 5 and lower


	function validStyle( prop, value, check_vend ) {
		var div = document.createElement( 'div' ),
			uc = function( txt ) {
				return txt.charAt( 0 ).toUpperCase() + txt.substr( 1 );
			},
			vend_pref = function( vend ) {
				if( vend === "" ) {
					return "";
				} else {
					return  "-" + vend.charAt( 0 ).toLowerCase() + vend.substr( 1 ) + "-";
				}
			},
			check_style = function( vend ) {
				var vend_prop = vend_pref( vend ) + prop + ": " + value + ";",
					uc_vend = uc( vend ),
					propStyle = uc_vend + ( uc_vend === "" ? prop : uc( prop ) );

				div.setAttribute( "style", vend_prop );

				if ( !!div.style[ propStyle ] ) {
					ret = true;
				}
			},
			check_vends = check_vend ? check_vend : vendors,
			ret;

		for( var i = 0; i < check_vends.length; i++ ) {
			check_style( check_vends[i] );
		}
		return !!ret;
	}

	function transform3dTest() {
		var mqProp = "transform-3d",
			// Because the `translate3d` test below throws false positives in Android:
			ret = $.mobile.media( "(-" + vendors.join( "-" + mqProp + "),(-" ) + "-" + mqProp + "),(" + mqProp + ")" );

		if( ret ) {
			return !!ret;
		}

		var el = document.createElement( "div" ),
			transforms = {
				// We’re omitting Opera for the time being; MS uses unprefixed.
				'MozTransform':'-moz-transform',
				'transform':'transform'
			};

		fakeBody.append( el );

		for ( var t in transforms ) {
			if( el.style[ t ] !== undefined ){
				el.style[ t ] = 'translate3d( 100px, 1px, 1px )';
				ret = window.getComputedStyle( el ).getPropertyValue( transforms[ t ] );
			}
		}
		return ( !!ret && ret !== "none" );
	}

	// Test for dynamic-updating base tag support ( allows us to avoid href,src attr rewriting )
	function baseTagTest() {
		var fauxBase = location.protocol + "//" + location.host + location.pathname + "ui-dir/",
			base = $( "head base" ),
			fauxEle = null,
			href = "",
			link, rebase;

		if ( !base.length ) {
			base = fauxEle = $( "<base>", { "href": fauxBase }).appendTo( "head" );
		} else {
			href = base.attr( "href" );
		}

		link = $( "<a href='testurl' />" ).prependTo( fakeBody );
		rebase = link[ 0 ].href;
		base[ 0 ].href = href || location.pathname;

		if ( fauxEle ) {
			fauxEle.remove();
		}
		return rebase.indexOf( fauxBase ) === 0;
	}

	// Thanks Modernizr
	function cssPointerEventsTest() {
		var element = document.createElement( 'x' ),
			documentElement = document.documentElement,
			getComputedStyle = window.getComputedStyle,
			supports;

		if ( !( 'pointerEvents' in element.style ) ) {
			return false;
		}

		element.style.pointerEvents = 'auto';
		element.style.pointerEvents = 'x';
		documentElement.appendChild( element );
		supports = getComputedStyle &&
		getComputedStyle( element, '' ).pointerEvents === 'auto';
		documentElement.removeChild( element );
		return !!supports;
	}

	function boundingRect() {
		var div = document.createElement( "div" );
		return typeof div.getBoundingClientRect !== "undefined";
	}

	// non-UA-based IE version check by James Padolsey, modified by jdalton - from http://gist.github.com/527683
	// allows for inclusion of IE 6+, including Windows Mobile 7
	$.extend( $.mobile, { browser: {} } );
	$.mobile.browser.oldIE = (function() {
		var v = 3,
			div = document.createElement( "div" ),
			a = div.all || [];

		do {
			div.innerHTML = "<!--[if gt IE " + ( ++v ) + "]><br><![endif]-->";
		} while( a[0] );

		return v > 4 ? v : !v;
	})();

	function fixedPosition() {
		var w = window,
			ua = navigator.userAgent,
			platform = navigator.platform,
			// Rendering engine is Webkit, and capture major version
			wkmatch = ua.match( /AppleWebKit\/([0-9]+)/ ),
			wkversion = !!wkmatch && wkmatch[ 1 ],
			ffmatch = ua.match( /Fennec\/([0-9]+)/ ),
			ffversion = !!ffmatch && ffmatch[ 1 ],
			operammobilematch = ua.match( /Opera Mobi\/([0-9]+)/ ),
			omversion = !!operammobilematch && operammobilematch[ 1 ];

		if(
			// iOS 4.3 and older : Platform is iPhone/Pad/Touch and Webkit version is less than 534 (ios5)
			( ( platform.indexOf( "iPhone" ) > -1 || platform.indexOf( "iPad" ) > -1  || platform.indexOf( "iPod" ) > -1 ) && wkversion && wkversion < 534 ) ||
			// Opera Mini
			( w.operamini && ({}).toString.call( w.operamini ) === "[object OperaMini]" ) ||
			( operammobilematch && omversion < 7458 )	||
			//Android lte 2.1: Platform is Android and Webkit version is less than 533 (Android 2.2)
			( ua.indexOf( "Android" ) > -1 && wkversion && wkversion < 533 ) ||
			// Firefox Mobile before 6.0 -
			( ffversion && ffversion < 6 ) ||
			// WebOS less than 3
			( "palmGetResource" in window && wkversion && wkversion < 534 )	||
			// MeeGo
			( ua.indexOf( "MeeGo" ) > -1 && ua.indexOf( "NokiaBrowser/8.5.0" ) > -1 ) ) {
			return false;
		}

		return true;
	}

	$.extend( $.support, {
		cssTransitions: "WebKitTransitionEvent" in window ||
			validStyle( 'transition', 'height 100ms linear', [ "Webkit", "Moz", "" ] ) &&
			!$.mobile.browser.oldIE && !opera,

		// Note, Chrome for iOS has an extremely quirky implementation of popstate.
		// We've chosen to take the shortest path to a bug fix here for issue #5426
		// See the following link for information about the regex chosen
		// https://developers.google.com/chrome/mobile/docs/user-agent#chrome_for_ios_user-agent
		pushState: "pushState" in history &&
			"replaceState" in history &&
			// When running inside a FF iframe, calling replaceState causes an error
			!( window.navigator.userAgent.indexOf( "Firefox" ) >= 0 && window.top !== window ) &&
			( window.navigator.userAgent.search(/CriOS/) === -1 ),

		mediaquery: $.mobile.media( "only all" ),
		cssPseudoElement: !!propExists( "content" ),
		touchOverflow: !!propExists( "overflowScrolling" ),
		cssTransform3d: transform3dTest(),
		boxShadow: !!propExists( "boxShadow" ) && !bb,
		fixedPosition: fixedPosition(),
		scrollTop: ("pageXOffset" in window ||
			"scrollTop" in document.documentElement ||
			"scrollTop" in fakeBody[ 0 ]) && !webos && !operamini,

		dynamicBaseTag: baseTagTest(),
		cssPointerEvents: cssPointerEventsTest(),
		boundingRect: boundingRect()
	});

	fakeBody.remove();


	// $.mobile.ajaxBlacklist is used to override ajaxEnabled on platforms that have known conflicts with hash history updates (BB5, Symbian)
	// or that generally work better browsing in regular http for full page refreshes (Opera Mini)
	// Note: This detection below is used as a last resort.
	// We recommend only using these detection methods when all other more reliable/forward-looking approaches are not possible
	var nokiaLTE7_3 = (function() {

		var ua = window.navigator.userAgent;

		//The following is an attempt to match Nokia browsers that are running Symbian/s60, with webkit, version 7.3 or older
		return ua.indexOf( "Nokia" ) > -1 &&
				( ua.indexOf( "Symbian/3" ) > -1 || ua.indexOf( "Series60/5" ) > -1 ) &&
				ua.indexOf( "AppleWebKit" ) > -1 &&
				ua.match( /(BrowserNG|NokiaBrowser)\/7\.[0-3]/ );
	})();

	// Support conditions that must be met in order to proceed
	// default enhanced qualifications are media query support OR IE 7+

	$.mobile.gradeA = function() {
		return ( $.support.mediaquery || $.mobile.browser.oldIE && $.mobile.browser.oldIE >= 7 ) && ( $.support.boundingRect || $.fn.jquery.match(/1\.[0-7+]\.[0-9+]?/) !== null );
	};

	$.mobile.ajaxBlacklist =
				// BlackBerry browsers, pre-webkit
				window.blackberry && !window.WebKitPoint ||
				// Opera Mini
				operamini ||
				// Symbian webkits pre 7.3
				nokiaLTE7_3;

	// Lastly, this workaround is the only way we've found so far to get pre 7.3 Symbian webkit devices
	// to render the stylesheets when they're referenced before this script, as we'd recommend doing.
	// This simply reappends the CSS in place, which for some reason makes it apply
	if ( nokiaLTE7_3 ) {
		$(function() {
			$( "head link[rel='stylesheet']" ).attr( "rel", "alternate stylesheet" ).attr( "rel", "stylesheet" );
		});
	}

})( jQuery );

/*-----END factory callback function-----*/
}/*factory callback function*/)/*factory structure call*/)/*factory structure*/;
