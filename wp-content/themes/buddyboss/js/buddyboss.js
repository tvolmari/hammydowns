/**
 * BuddyBoss JavaScript functionality
 *
 * @since    3.0
 * @package  buddyboss
 *
 * ====================================================================
 *
 * 1. jQuery Global
 * 2. Main BuddyBoss Class
 * 3. Functions & Inline Plugins
 */



/**
 * 1. jQuery Global
 * ====================================================================
 */
var jq = $ = jQuery;



/**
 * 2. Main BuddyBoss Class
 *
 * This class takes care of BuddyPress additional functionality and
 * provides a global name space for BuddyBoss plugins to communicate
 * through.
 *
 * Event name spacing:
 * $(document).on( "buddyboss:*module*:*event*", myCallBackFunction );
 * $(document).trigger( "buddyboss:*module*:*event*", [a,b,c]/{k:v} );
 * ====================================================================
 * @return {class}
 */
var BuddyBoss = ( function( $, window, undefined ) {

	/**
	 * Globals/Options
	 */
	var _l = {
		$document: $(document),
		$window: $(window)
	};

	// Controller
	var App = {};

	// Custom Events
	var Vent = $({});

	// Responsive
	var Responsive = {};

	// BuddyPress Defaults
	var BuddyPress = {};

	// BuddyPress Legacy
	var BP_Legacy = {};


	/** --------------------------------------------------------------- */

	/**
	 * Application
	 */

	// Initialize, runs when script is processed/loaded
	App.init = function() {

		_l.$document.ready( App.domReady );

		BP_Legacy.init();
		Responsive.init();
	}

	// When the DOM is ready (page laoded)
	App.domReady = function() {
		_l.body = $('body');
		_l.$buddypress = $('#buddypress');
	}

	// Event API helper, will auto namespace buddyboss
	App.on = function( key, callback ) {
		_l.$document()
	}
	App.trigger = function( key, args ) {

	}
	/**
	 * Extending the event API
	 * var BuddyBoss = window.BuddyBoss
	 * Pics.on = BuddyBoss
	 */


	// Data API
	App.store = {};
	App.store_keys = [];

	App.data = function( key, value ) {
		console.log( '' );
		console.log( 'key', key );
		console.log( '!!key', !!key );
		console.log( '!!!key', !!!key );
		// if ( !!key ) {

		// }

	};


	/** --------------------------------------------------------------- */

	/**
	 * BuddyPress Responsive Help
	 */
	Responsive.init = function() {

	}


	/** --------------------------------------------------------------- */

	/**
	 * BuddyPress Legacy Support
	 */

	// Initialize
	BP_Legacy.init = function() {
		BP_Legacy.injected = false;
		_l.$document.ready( BP_Legacy.domReady );
	}

	// On dom ready we'll check if we need legacy BP support
	BP_Legacy.domReady = function() {
		BP_Legacy.check();
	}

	// Check for legacy support
	BP_Legacy.check = function() {
		if ( ! BP_Legacy.injected && _l.body.hasClass('buddypress') && _l.$buddypress.length == 0 ) {
			BP_Legacy.inject();
		}
		// _l.$buddypress.animate({opacity:1});
	}

	// Inject the right code depending on what kind of legacy support
	// we deduce we need
	BP_Legacy.inject = function() {
		BP_Legacy.injected = true;

		var $secondary  = $('#secondary'),
				do_legacy = false;

		var $content  = $('#content'),
				$padder   = $content.find('.padder').first(),
				do_legacy = false;

		var $article = $content.children('article').first();

		var $legacy_page_title,
				$legacy_item_header;

		// Check if we're using the #secondary widget area and add .bp-legacy inside that
		if ( $secondary.length ) {
			$secondary.prop( 'id', 'secondary' ).addClass('bp-legacy');

			do_legacy = true;
		}
		
		// Check if the plugin is using the #content wrapper and add #buddypress inside that
		if ( $padder.length ) {
			$padder.prop( 'id', 'buddypress' ).addClass('bp-legacy entry-content');

			do_legacy = true;

			// console.log( 'Buddypress.js #buddypress fix: Adding #buddypress to .padder' );
		}
		else if ( $content.length ) {
			$content.wrapInner( '<div class="bp-legacy entry-content" id="buddypress"/>' );

			do_legacy = true;

			// console.log( 'Buddypress.js #buddypress fix: Dynamically wrapping with #buddypresss' );
		}

		// Apply legacy styles if needed
		if ( do_legacy ) {

			_l.$buddypress = $('#buddypress');

			$legacy_page_title = $('.buddyboss-bp-legacy.page-title');
			$legacy_item_header = $('.buddyboss-bp-legacy.item-header');

			// Article Element
			if ( $article.length === 0 ) {
				$content.wrapInner('<article/>');
				$article = $( $content.find('article').first() );
			}

			// Page Title
			if ( $content.find('.entry-header').length === 0 || $content.find('.entry-title').length === 0 ) {
				$legacy_page_title.prependTo( $article ).show();
				$legacy_page_title.children().unwrap();
			}

			// Item Header
			if ( $content.find('#item-header-avatar').length === 0 && _l.$buddypress.find('#item-header').length ) {
				$legacy_item_header.prependTo( _l.$buddypress.find('#item-header') ).show();
				$legacy_item_header.children().unwrap();
			}
		}
	}

	// Boot er' up
	App.init();

	// Expose public API:
	return {
		Data: App.data
	};

}( jQuery, window ) );




/**
 * 3. Functions & Inline Plugins
 * ====================================================================
 * 3a. jQuery.fn.style
 *
 */

/**
 * 3a. jQuery.fn.style
 * The style function, some times jQuery doesn't set certain styles
 * we need with the $.css() function, for example !important on
 * margin-top which we need absolute control over for responsive
 * adminbar and plugin conflict resultion.
 * ====================================================================
 * @param  {string} styleName ex: 'margin-top'
 * @param  {string} value     ex: '0px;'
 * @param  {string} priority  ex: '!important'
 * @return {style}           Element style
 */
jQuery.fn.style = function(styleName, value, priority) {
    // DOM node
    var node = this.get(0);
    // Ensure we have a DOM node
    if (typeof node == 'undefined') {
        return;
    }
    // CSSStyleDeclaration
    var style = this.get(0).style;
    // Getter/Setter
    if (typeof styleName != 'undefined') {
        if (typeof value != 'undefined') {
            // Set style property
            var priority = typeof priority != 'undefined' ? priority : '';
            style.setProperty(styleName, value, priority);
        } else {
            // Get style property
            return style.getPropertyValue(styleName);
        }
    } else {
        // Get CSSStyleDeclaration
        return style;
    }
}

/* jQuery Easing Plugin, v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/ */
jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return -h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}return -h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return -h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}return -h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return -h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return -h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}if(f==g){return a+h}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return -h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return -h/2*(Math.sqrt(1-f*f)-1)+a}return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return -(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k/2)==2){return e+l}if(!j){j=k*(0.3*1.5)}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}if(h<1){return -0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});