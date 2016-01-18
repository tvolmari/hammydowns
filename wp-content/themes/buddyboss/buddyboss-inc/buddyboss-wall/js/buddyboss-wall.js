/**
 * BuddyBoss Wall
 *
 * A BuddyPress plugin combining user activity feeds with media management.
 *
 * This file should load in the footer
 *
 * @author      BuddyBoss
 * @since       BuddyBoss 3.0, BuddyBoss Media 1.0, BuddyBoss Pics 1.0
 * @package     buddyboss-wall
 *
 * ====================================================================
 *
 * 1. BuddyBoss Wall
 */

;(function ($, window, document) {

	var config = {
		ajaxResetTimeout: 201
	}

	var Wall = {};
	var $el = {};

	/**
	 * Init

	 * @return {void}
	 */
	Wall.init = function() {
		// Globals
		$el.document = $(document);

		// Events
		$el.document.ajaxSuccess( Wall.ajaxSuccessListener );

		// First run
		Wall.initTooltips();

		// Localization, we need to override some BP_Dtheme variables
		if ( BP_DTheme && BuddyBossWallOptions ) {
			$.extend( BP_DTheme, BuddyBossWallOptions );
		}
	}

	/**
	 * Listen to AJAX requests and refresh dynamic content/functionality

	 * @return {void}
	 */
	Wall.ajaxSuccessListener = function( event, jqXHR, options ) {
		Wall.destroyTooltips();

		window.setTimeout( Wall.initTooltips, config.ajaxResetTimeout );
	}

	/**
	 * Teardown tooltips if they exist
	 *
	 * @return {void}
	 */
	Wall.destroyTooltips = function() {
		if ( $el.tooltips && $el.tooltips.length ) {
			$el.tooltips.tooltipster('destroy');
			$el.tooltips = null;
		}
	}

	/**
	 * Prepare tooltips
	 *
	 * @return {void}
	 */
	Wall.initTooltips = function() {
		// Destroy any existing tooltips
		// if ( $el.tooltips && $el.tooltips.length ) {
		// 	$el.tooltips.tooltipster('destroy');
		// 	$el.tooltips = null;
		// }

		// Find tooltips on page
		$el.tooltips = $('.buddyboss-wall-tt-others');

		// Init tooltips
		if ( $el.tooltips.length ) {
			$el.tooltips.tooltipster({
				contentAsHTML:  true,
				functionInit:   Wall.getTooltipContent,
				interactive:    true,
				position:       'top-left',
				theme:          'tooltipster-buddyboss'
			});
		}
	}

	/**
	 * Get tooltip content
	 *
	 * @param  {object} origin  Original tooltip element
	 * @param  {string} content Current tooltip content
	 *
	 * @return {string}         Tooltip content
	 */
	Wall.getTooltipContent = function( origin, content ) {
		var $content = origin.parent().find('.buddyboss-wall-tt-content').detach().html();

		return $content;
	}

	Wall.init();

})( jQuery, window, document );

