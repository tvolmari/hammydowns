// AJAX Functions
var jq = $ = jQuery;

// Global variable to prevent multiple AJAX requests
var bp_ajax_request = null;

// Global variables to temporarly store newest activities
var newest_activities = '';
var activity_last_recorded  = 0;

/* BuddyBoss Fix: Add friend buttons don't work after pagination */
var attachFriendshipButtonHandlers;

jq(document).ready( function() {

	/**** Buddypress **************************************************************/

	// If a theme editor / developer disables the admin bar on the front end
	if ( $('#wpadminbar').length == 0 ) {
		$('html').style( 'margin-top', '0px', 'important' );
	}

	/**** Page Load Actions *******************************************************/

	/* Hide Forums Post Form */
	if ( '-1' == window.location.search.indexOf('new') && jq('div.forums').length )
		jq('#new-topic-post').hide();
	else
		jq('#new-topic-post').show();

	/* Activity filter and scope set */
	bp_init_activity();

	/* Object filter and scope set. */
	var objects = [ 'members', 'groups', 'blogs', 'forums' ];
	bp_init_objects( objects );

	/* @mention Compose Scrolling */
	if ( jq.query.get('r') && jq('#whats-new').length ) {
		jq('#whats-new-options').animate({
			height:'40px'
		});
		jq("#whats-new-form textarea").animate({
			height:'50px'
		});
		jq.scrollTo( jq('#whats-new'), 500, {
			offset:-125,
			easing:'easeOutQuad'
		} );
		jq('#whats-new').focus();
	}

	/**** Buddyboss Picture Grid ********************************************************/
	window.Code = window.Code || { Util: false, PhotoSwipe: false };
	var BuddyBossSwiper = ( function( window, Util, PhotoSwipe ) {

		if ( ! Util || ! PhotoSwipe ) {
			return false;
		}

		var
			$buddyboss_pic_grid = jq('#buddyboss-pics-grid'),
			buddyboss_photoswipe = false,
			current_pic_permalink,
			current_pic_activity_text,
			$caption,
			$comment_link,
			buddyboss_photoswipe_options   = {

				preventSlideshow: true,
				imageScaleMethod: 'fitNoUpscale',
				loop: false,
				captionAndToolbarAutoHideDelay: 0,

				// Toolbar HTML
				getToolbar: function() {
					return '<div class="ps-toolbar-close"><div class="ps-toolbar-content"></div></div><div class="ps-toolbar-comments"><div class="ps-toolbar-content"></div></div><div class="ps-toolbar-previous ps-toolbar-previous-disabled"><div class="ps-toolbar-content"></div></div><div class="ps-toolbar-next"><div class="ps-toolbar-content"></div></div>';
					// NB. Calling PhotoSwipe.Toolbar.getToolbar() wil return the default toolbar HTML
				},

				// Return the current activity text for the caption
				getImageCaption: function(el) {
					var $pic = jq( el );

					current_pic_permalink = '#';
					current_pic_activity_text = '';

					if ( $pic.find('img').length == 0 )
						return '';

					current_pic_permalink = $pic.find('img')[0].getAttribute( 'data-permalink' );
					current_pic_activity_text = $buddyboss_pic_grid.length > 0
																		? $pic.parents('.gallery-icon').find('.buddyboss_pics_action').text()
																		: $pic.parents('.activity-content').find('.activity-header').text();

					return current_pic_activity_text;
				},

				// Store data we need
				getImageMetaData: function(el) {
					return {
						href: current_pic_permalink,
						caption: current_pic_activity_text
					}
				}

			}; // End PhotoSwipe setup

		BuddyBossSwiperClass = {
			reset: function() {
				// console.log( 'resetting' );

				if ( buddyboss_photoswipe !== false ) {
					PhotoSwipe.detatch( buddyboss_photoswipe );
				}
				BuddyBossSwiperClass.start();
			},
			start: function() {
				// console.log( 'starting' );

				var $buddyboss_pics;

				if ( $buddyboss_pic_grid.length === 1 )
				{
					$buddyboss_pics = jq('.gallery-icon > a');
				}
				else {
					$buddyboss_pics = jq('.buddyboss-pics-photo-wrap');
				}

				// console.log( $buddyboss_pics );

				if ( $buddyboss_pics.length > 0 ) {
					// Load PhotoSwipe
					buddyboss_photoswipe = $buddyboss_pics.photoSwipe( buddyboss_photoswipe_options );

					// Before showing we need to update the comment icon with the
					// proper permalink
					buddyboss_photoswipe.addEventHandler(PhotoSwipe.EventTypes.onBeforeShow, function(e){
						// Prevent scrolling while active
						jq('html').css({overflow: 'hidden'});
					});

					// After showing we need to revert any changes we made during the
					// onBeforeShow event
					buddyboss_photoswipe.addEventHandler(PhotoSwipe.EventTypes.onHide, function(e){
						// Allow scrolling again
						jq('html').css({overflow: 'auto'});

						current_pic_activity_text = null;
						current_pic_permalink = null;

						$caption.off( 'click' );
						$caption = null;

						$comment_link = null;

						// console.log( 'Hiding PhotoSwipe' );
						setTimeout( function() { jq(window).trigger('reset_carousel'); }, 555 );
					});

					// onCaptionAndToolbarShow
					buddyboss_photoswipe.addEventHandler( PhotoSwipe.EventTypes.onCaptionAndToolbarShow, function(e) {
						$caption = jq( '.ps-caption' ).on( 'click', function( e ) {
							window.location = buddyboss_photoswipe.getCurrentImage().metaData.href;
						});
						$comment_link = jq( '.ps-toolbar-comments' );
					});

					buddyboss_photoswipe.addEventHandler(PhotoSwipe.EventTypes.onToolbarTap, function(e) {
						if ( e.toolbarAction === PhotoSwipe.Toolbar.ToolbarAction.none ) {
							if ( e.tapTarget === $comment_link[0] || Util.DOM.isChildOf( e.tapTarget, $comment_link[0] ) ) {
								window.location = buddyboss_photoswipe.getCurrentImage().metaData.href;
							}
						}
					});


				} // End if pics.length > 0
			}
		}

		BuddyBossSwiperClass.start();

		function ajaxSuccessHandler( e, xhr, options ) {
			var doReset = false;
			var resetTimeout = 201;

			if ( bp_ajax_request ) {
				// console.log( '' );
				// console.log( 'ajaxSuccessHandler()' );
				// console.log( options );
				// console.log( '********************' );
				// console.log( '' );
				doReset = true;
			}

			if ( doReset ) {
				window.setTimeout( BuddyBossSwiperClass.reset, resetTimeout );
			}
		}

		$(document).ajaxSuccess( ajaxSuccessHandler );

		return BuddyBossSwiperClass;

	}
	(window, window.Code.Util || false, window.Code.PhotoSwipe || false)

	);

	/**** Activity Posting ********************************************************/

	/* Textarea focus */
	var textAreaExpanded = false,
			textAreaAnimating = false,
			textAreaTyping = false,
			wnTextArea = jq('#whats-new'),
			wnOptions = jq('#whats-new-options'),
			wnSubmit = jq("#aw-whats-new-submit"),
			previewPane = jq('#buddyboss-pics-preview'),
			previewInner = jq('#buddyboss-pics-preview-inner'),
			postButton = jq("#aw-whats-new-submit"),
			maxWidth = jq('#whats-new-options').width();

	/* Pic Uploading */
	var	progressWrap = jq('.buddyboss-pics-progress').first(),
			progressBar = progressWrap.find('progress').first(),
			progressValue = progressWrap.find('.buddyboss-pics-progress-value'),
			progressPercent = 0,
			progressAnimation = function() {

				if ( progressBar.val() < progressPercent ) {
					progressBar.val( progressBar.val() + 1 );
				}

				if ( progressBar.val() < 100 && progressPercent != 100 ) {
					progressTimeout = setTimeout( progressAnimation, 1000/60 );
				}
				else if ( progressPercent == 100 ) {
					progressBar.val( 100 );
				}

			},
			progressTimeout,
			has_pic = false,
			uploader;

	// When a user clicks on the status update box, the form should animate down
	wnTextArea.focus( function(){
		wnTextArea.animate({height:'75px'}, function(){
			textAreaExpanded = true;
		});

		// Prepare image preview dimensions
		var picWidth,
				picHeight,
				picRatio;

		// Try to find an existing preview image
		var $img = jq('#buddyboss-pics-preview-inner img');

		if ($img.hasOwnProperty('length') && $img.length > 0)
		{
			picWidth = $img.width();   // Note: $(this).width() will not
			picHeight = $img.height(); // work for in memory images.

			//console.log('// ----- clicked again ----- //');
			//console.log( picWidth );
			//console.log( picHeight );
			//console.log('// ----- clicked again ----- //');

			if ( picWidth > maxWidth )
			{
        picRatio = maxWidth / width;
        picWidth = maxWidth;
        picHeight = picHeight * picRatio;
        $img.css("width", picWidth);
        $img.css("height", picHeight);  // Scale height based on ratio
			}
			previewPane.animate({ height: picHeight+10+'px' });
		};

		wnSubmit.prop("disabled", false);

		// Return to the 'All Members' tab and 'Everything' filter,
		// to avoid inconsistencies with the heartbeat integration
		var $activity_all = jq( '#activity-all' );
		if ( $activity_all.length  ) {
			if ( ! $activity_all.hasClass( 'selected' ) ) {
				// reset to everyting
				jq( '#activity-filter-select select' ).val( '-1' );
				$activity_all.children( 'a' ).trigger( "click" );
			} else if ( '-1' != jq( '#activity-filter-select select' ).val() ) {
				jq( '#activity-filter-select select' ).val( '-1' );
				jq( '#activity-filter-select select' ).trigger( 'change' );
			}
		}
	} );

	jq('#whats-new').focusout( function(e){
		setTimeout(function(){
			textAreaTyping = false;
			textAreaAnimating = true;

		wnTextArea.stop().animate({height:'20px'}, function(){
			textAreaExpanded = false;
			textAreaAnimating = false;
		})
		}, 111);
	});

	wnTextArea.keyup(function(e){
		if (textAreaTyping == false)
		{
			textAreaTyping = true;
			wnTextArea.trigger('focus');
		}
	} );

	// This function animates the status box back to it's original state
	function handleTextAreaFocusOut()
	{
		textAreaAnimating = true;

		wnTextArea.stop().animate({height:'20px'}, function(){
			textAreaExpanded = false;
			textAreaAnimating = false;
		})

		wnSubmit.prop("disabled", true).removeClass('loading');
		jq("form#whats-new-form").focus();
	}

	if ( wnTextArea.length > 0 )
	{
		jq(document).click( function(e){
			if (textAreaExpanded == false || textAreaAnimating == true)
				return;

			var tgt = jq(e.target), status;

			//console.log ( '//-------------------------//' );
			//console.log ( tgt.attr('id') );
			//console.log ( wnTextArea.attr('id') );
			//console.log ( wnOptions.attr('id') );
			//console.log ( '//-------------------------//' );
			//console.log ( tgt.attr('id') == wnTextArea.attr('id') );
			//console.log ( tgt.attr('id') == wnOptions.attr('id') );
			//console.log ( '//-------------------------//' );
			//console.log( tgt.parents( '#whats-new-options' ).length );
			if ( tgt.attr('id') == wnTextArea.attr('id') || tgt.attr('id') == wnOptions.attr('id')
					 || tgt.parents('#whats-new-options').length > 0 || tgt.parents(previewPane).length > 0
					 || tgt.attr('id') == previewPane.attr('id') || tgt.attr('id') == previewInner.attr('id') )
			{
				return;
			}
			else {
				textAreaTyping = false;
				handleTextAreaFocusOut();
			}
		});

		jq(document).keyup(function(e) {
	  	if (e.keyCode == 27) {
				setTimeout(function(){
	  			textAreaTyping = false;
	 				handleTextAreaFocusOut();
				}, 33);
	  	}
		});
	}

	if (jq('#buddyboss-pics-add-photo-button').length)
	{
		var opt = window.BuddyBossPicsOptions || {};
		var pl_runtimes  = opt['runtimes'] || 'html5,flash,silverlight,html4';
		var pl_filetypes = opt['filetypes'] || 'jpg,jpeg,gif,png,bmp';
		var pl_filesize  = opt['filesize'] || '10mb';
		var pl_label     = opt['upload_label'] || 'Upload a Picture';

		var state = 'closed';
		var ieMobile = navigator.userAgent.indexOf('IEMobile') !== -1;

		// IE mobile
		if ( ieMobile ) {
			$('#buddyboss-pics-add-photo').addClass('legacy');
		}

		// iOS/mobile
		$('#buddyboss-pics-add-photo-button').on( 'click', function( e ) {
			if ( state === 'closed' ) {
				$('#buddyboss-pics-add-photo').find('.moxie-shim').find('input').trigger( 'click' );
				return false;
			}
		});

		uploader = new plupload.Uploader({
			runtimes: pl_runtimes,
			browse_button: $('#buddyboss-pics-add-photo-button')[0],
			container: $('#buddyboss-pics-add-photo')[0],
			unique_names : false,
			multi_selection: false,
			url: ajaxurl,
			multipart: true,
			multipart_params: {
				action: 'buddyboss_pics_post_photo',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce_post_update': jq("input#_wpnonce_post_update").val()
			},
			flash_swf_url: BuddyBossOptions.tpl_url + '/buddyboss-inc/buddyboss-pics/js/Moxie.swf',
			silverlight_xap_url: BuddyBossOptions.tpl_url + '/buddyboss-inc/buddyboss-pics/js/Moxie.xap',
			filters: {
				mime_types: [
				  { title : pl_label, extensions : pl_filetypes }
				],
				max_file_size: pl_filesize
			},
			init: {
				PostInit: function() {
			    // Called after initialization is finished and internal event handlers bound
				},
				Browse: function(up) {
					state = 'browse';
					// Called when file picker is clicked
					console.log( '[Browse]' );
				},
				FilesAdded: function(up, files) {
					// console.log('////// onsubmit ///////');

					jq('#buddyboss-pics-preview').animate({height:'0px'});

					progressWrap.addClass('uploading');
					postButton.attr('disabled','disabled');

					progressPercent = 0;
					progressBar.val(0);
					progressValue.html(0 + '%');
					progressTimeout = setTimeout( progressAnimation, 1000/60 );

					state = 'closed';

					up.start();
				},

				UploadProgress: function(up, file) {
					if ( file && file.hasOwnProperty( 'percent' ) ) {
						progressPercent = file.percent;
						progressValue.html( progressPercent + '%' );
					}

					if ( file && file.hasOwnProperty( 'percent' ) && file.percent == 100 && window.BuddyBoss && window.BuddyBoss.is_mobile ) {
						progressValue.html( 'One moment...' );
					}
				},

				FileUploaded: function(up, file, info) {
					var responseJSON = $.parseJSON( info.response );
					// console.log('// ----- upload response ----- //');
					// console.log(up,file,info,responseJSON);

					if ( ! responseJSON ) {
						alert( opt.upload_error || "An error occurred!" );
					}

					if (responseJSON.hasOwnProperty('error'))
					{
						alert(responseJSON.message);
						return false;
					}

					if ( window.BuddyBoss && window.BuddyBoss.is_mobile ) {
						progressValue.html( opt.one_moment || 'One moment...' );
					}

					var pic_uri = responseJSON.hasOwnProperty('url') ? responseJSON.url : false;

					previewInner.html('');

					previewPane.animate({height: '0px'}, function()
					{
						progressWrap.removeClass('uploading');
						postButton[0].disabled = false;
						postButton.removeAttr('disabled');

						if ( pic_uri )
						{
							previewInner.html('<img src="'+pic_uri+'" />');

							var picWidth,
									picHeight,
									picRatio;

							var $img = jq('#buddyboss-pics-preview-inner img')
								.load(function()
								{
									picWidth = this.width;   // Note: $(this).width() will not
									picHeight = this.height; // work for in memory images.

									//console.log( picWidth );
									//console.log( picHeight );
									//console.log('// ----- upload success ----- //');

									if ( picWidth > maxWidth )
									{
						        picRatio = maxWidth / picWidth;
						        picWidth = maxWidth;
						        picHeight = picHeight * picRatio;
						        jq(this).css("width", picWidth);
						        jq(this).css("height", picHeight);  // Scale height based on ratio
									}
									previewPane.animate({ height: picHeight+10+'px' });
								});

							has_pic = responseJSON;
						}
					});

				},

				Error: function(up, args) {
					alert( "An error occurred!" );

					progressWrap.removeClass('uploading');
					postButton.attr('disabled','');
				}
			}
		});

		uploader.init();
	}

	/* BP Activity Privacy Code from bp-activity-privacy.js?ver=1.3*/
	if( jq("select#activity-privacy").length != 0 ){
		jq.fn.extend({
        customStyle : function(options) {

	        if(!jq.browser.msie || (jq.browser.msie&&jq.browser.version>6)) {
	            return this.each(function() {
	            	if ( jq(this).data('customStyle') == undefined ) {

		            	jq(this).data('customStyle', true);
		                var currentSelected = jq(this).find(':selected');

		                jq(this).after('<span class="customStyleSelectBox'+options+'"><i class="'+currentSelected.attr("class")+'"></i><span class="customStyleSelectBoxInner'+options+'">'+currentSelected.text()+'</span><i class="fa fa-caret-down"></i></span>').css({position:'absolute', opacity:0,fontSize:jq(this).next().css('font-size')});
		                var selectBoxSpan = jq(this).next();

		                var selectBoxWidth = parseInt(jq(this).width()) - parseInt(selectBoxSpan.css('padding-left')) -parseInt(selectBoxSpan.css('padding-right'));
		                var selectBoxSpanInner = selectBoxSpan.find(':first-child').next();
		                selectBoxSpan.css({display:'inline-block'});
		               //alert(selectBoxSpan.width());
		                jq(this).css('width',selectBoxSpan.width());
		                if(options=="") selectBoxSpanInner.css({width:selectBoxWidth, display:'inline-block'});
		                var selectBoxHeight = parseInt(selectBoxSpan.height()) + parseInt(selectBoxSpan.css('padding-top')) + parseInt(selectBoxSpan.css('padding-bottom'));
		                jq(this).height(selectBoxHeight).change(function() {
		                	selectBoxSpanInner.parent().find('i:first-child').attr('class',  jq(this).find(':selected').attr('class') );
		                    selectBoxSpanInner.text(jq(this).find(':selected').text()).parent().addClass('changed');
		                    jq(this).css('width',selectBoxSpan.width());
		                });


	            	}
	         });
	        }
    }
    });

	jq('body').on('change', '.bp-ap-selectbox',  function(event) {
		var target = jq(event.target);
    	var parent = target.closest('.activity-item');
    	var parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

		if (typeof bp_get_cookies == 'function')
			var cookie = bp_get_cookies();
    	else
    		var cookie = encodeURIComponent(document.cookie);

        jq.post( ajaxurl, {
			action: 'update_activity_privacy',
			'cookie': cookie,
			'visibility': jq(this).val(),
			'id': parent_id
		},

		function(response) {
		});

		return false;
	});

	//fix the scroll problem
    jq('#whats-new').off('focus');
    jq('#whats-new').on('focus', function(){
        jq("#whats-new-options").css('height','auto');
        jq("form#whats-new-form textarea").animate({
            height:'50px'
        });
        jq("#aw-whats-new-submit").prop("disabled", false);
    });

	jq('span#activity-visibility').prependTo('div#whats-new-submit');
	jq("input#aw-whats-new-submit").off("click");

	var selected_item_id = jq("select#whats-new-post-in").val();

	jq("select#whats-new-post-in").data('selected', selected_item_id );
	//if selected item is not 'My profil'
	if( selected_item_id != undefined && selected_item_id != 0 ){
		jq('select#activity-privacy').replaceWith(visibility_levels.groups);
	}

	jq("select#whats-new-post-in").on("change", function() {
		var old_selected_item_id = jq(this).data('selected');
		var item_id = jq("#whats-new-post-in").val();

		if(item_id == 0 && item_id != old_selected_item_id){
			jq('select#activity-privacy').replaceWith(visibility_levels.profil);
		}else{
			if(item_id != 0 && old_selected_item_id == 0 ){
				jq('select#activity-privacy').replaceWith(visibility_levels.groups);
			}
		}
		jq('select#activity-privacy').next().remove();
		jq('select#activity-privacy').customStyle('1');
		jq(this).data('selected',item_id);
	});
	}

	/* New posts */
	jq("#aw-whats-new-submit").on( 'click', function() {
		var last_date_recorded = 0;

		// Check if we're currently uploading a picture and alert the user
		if ( progressWrap.hasClass('uploading') ) {
			alert( "Picture upload currently in progress, please wait until completed." );
			return false;
		}

		var button = jq(this);
		var form = button.parent().parent().parent().parent();

		form.children().each( function() {
			if ( jq.nodeName(this, "textarea") || jq.nodeName(this, "input") )
				jq(this).prop( 'disabled', true );
		});

		/* Remove any errors */
		jq('div.error').remove();
		button.addClass('loading');
		button.prop('disabled', true);

		/* Default POST values */
		var object = '';
		var item_id = jq("#whats-new-post-in").val();
		var content = jq("#whats-new").val();
		var firstrow = jq( '#buddypress ul.activity-list li' ).first();
		var activity_row = firstrow;
		var timestamp = null;

		// Checks if at least one activity exists
		if ( firstrow.length ) {

			if ( activity_row.hasClass( 'load-newest' ) ) {
				activity_row = firstrow.next();
			}

			timestamp = activity_row.prop( 'class' ).match( /date-recorded-([0-9]+)/ );
		}

 		if ( timestamp ) {
			last_date_recorded = timestamp[1];
		}

		if ( jq("select#activity-privacy").length != 0 )
		{
		var visibility = jq("select#activity-privacy").val();
		}

		if ( typeof has_pic == 'object' && has_pic.hasOwnProperty('name') )
		{
			content += ' <a target="_blank" class="buddyboss-pics-photo-link" href="'+has_pic.url+'" title="'+has_pic.name+'">'+has_pic.name+'</a>';
		}

		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = jq("#whats-new-post-object").val();
		}

		jq.post( ajaxurl, {
			action: 'post_update',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_post_update': jq("#_wpnonce_post_update").val(),
			'content': content,
			'visibility': visibility,
			'object': object,
			'item_id': item_id,
			'has_pic': has_pic,
			'since': last_date_recorded,
			'_bp_as_nonce': jq('#_bp_as_nonce').val() || ''
		},
		function(response) {

			form.children().each( function() {
				if ( jq.nodeName(this, "textarea") || jq.nodeName(this, "input") ) {
					jq(this).prop( 'disabled', false );
				}
			});

			/* Check for errors and append if found. */
			if ( response[0] + response[1] == '-1' ) {
				form.prepend( response.substr( 2, response.length ) );
				jq( '#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				if ( 0 == jq("ul.activity-list").length ) {
					jq("div.error").slideUp(100).remove();
					jq("#message").slideUp(100).remove();
					jq("div.activity").append( '<ul id="activity-stream" class="activity-list item-list">' );
				}

				if ( firstrow.hasClass( 'load-newest' ) )
					firstrow.remove();

				/* BuddyBoss: Remove picture preview and animate up */
				previewInner.html('');
				previewPane.stop().animate({height:'0px'});
				has_pic = false;

				jq("#activity-stream").prepend(response);

				if ( ! last_date_recorded )
					jq("#activity-stream li:first").addClass('new-update just-posted');

				if ( 0 != jq("#latest-update").length ) {
					var l = jq("#activity-stream li.new-update .activity-content .activity-inner p").html();
					var v = jq("#activity-stream li.new-update .activity-content .activity-header p a.view").attr('href');

					var ltext = jq("#activity-stream li.new-update .activity-content .activity-inner p").text();

					var u = '';
					if ( ltext != '' )
						u = l + ' ';

					u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

					jq("#latest-update").slideUp(300,function(){
						jq("#latest-update").html( u );
						jq("#latest-update").slideDown(300);
					});
				}

				/* BuddyBoss: Check if we're on the pic page and refresh after upload */
				if ( 0 !== jq( '#is-buddyboss-pics-grid' ).length ) {
					var refreshUrl = jq( '#is-buddyboss-pics-grid' ).data( 'url' );
					if ( refreshUrl.length > 6 ) {
						document.location = refreshUrl;
					}
				}

				/* BuddyBoss: If we're using pics, we need to attach PhotoSwipe */
				var $new = jq("li.new-update").find('.buddyboss-pics-photo-wrap');
				if ( $new.length > 0 && typeof BuddyBossSwiper == 'object'
						 && BuddyBossSwiper.hasOwnProperty( 'reset' ) ) {
					BuddyBossSwiper.reset();
				}

				jq("li.new-update").hide().slideDown( 300 );
				jq("li.new-update").removeClass( 'new-update' );
				jq("#whats-new").val('');

				// reset vars to get newest activities
				newest_activities = '';
				activity_last_recorded  = 0;

			}

			jq("#whats-new-form textarea").animate({
				height:'20px'
			});
			jq("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');
		});

		return false;
	});

	/* List tabs event delegation */
	jq('div.activity-type-tabs').on( 'click', function(event) {
		var target = jq(event.target).parent();

		if ( event.target.nodeName == 'STRONG' || event.target.nodeName == 'SPAN' )
			target = target.parent();
		else if ( event.target.nodeName != 'A' )
			return false;

		/* Reset the page */
		jq.cookie( 'bp-activity-oldestpage', 1, {
			path: '/'
		} );

		/* Activity Stream Tabs */
		var scope = target.attr('id').substr( 9, target.attr('id').length );
		var filter = jq("#activity-filter-select select").val();

		if ( scope == 'mentions' )
			jq( '#' + target.attr('id') + ' a strong' ).remove();

		bp_activity_request(scope, filter);

		return false;
	});

	/* Activity filter select */
	jq('#activity-filter-select select').change( function() {
		var selected_tab = jq( 'div.activity-type-tabs li.selected' );

		if ( !selected_tab.length )
			var scope = null;
		else
			var scope = selected_tab.attr('id').substr( 9, selected_tab.attr('id').length );

		var filter = jq(this).val();

		bp_activity_request(scope, filter);

		return false;
	});

	/* Stream event delegation */
	jq('div.activity').on( 'click', function(event) {
		var target = jq(event.target);

		if ( target.hasClass( 'loading' ) ) {
			return false;
		}

		/* Favoriting activity stream items */
		if ( target.hasClass('fav') || target.hasClass('unfav') ) {
			var type = target.hasClass('fav') ? 'fav' : 'unfav';
			var parent = target.closest('.activity-item');
			var parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

			target.addClass('loading');

			document.oldDocumentWrite = document.write;
			document.write = function () {};

			jq.post( ajaxurl, {
				action: 'activity_mark_' + type,
				dataType: 'json',
				'cookie': encodeURIComponent(document.cookie),
				'id': parent_id
			},
			function(response) {

				/* BuddyBoss: Modified to get number of likes */
				var has_like_text = false,
						but_text = '',
						num_likes_text = '',
						bp_default_like_count = 0,
						remove_comment_ul = false,
						responseJSON = response.indexOf('{') != -1
												 ? jQuery.parseJSON( response )
												 : false;

				// console.log( response );

				// We have a response and button text
				if ( responseJSON && responseJSON.but_text ) {
					but_text = responseJSON.but_text;
				}
				else {
					but_text = response;
				}

				// We have a response and like count (int)
				if ( responseJSON && responseJSON.hasOwnProperty( 'like_count' ) ) {

					// If the count is above 0
					if ( responseJSON.like_count ) {
						has_like_text = true;
						num_likes_text = responseJSON.num_likes;
					}

					// If the count is 0 we need to remove the activity wrap
					else {
						remove_comment_ul = true;
					}
				}

				// console.log(  has_like_text  );

				target.fadeOut( 200, function() {
					var button             = jq(this),
							item               = button.parents('.activity-item'),
							comments_wrap      = item.find('.activity-comments'),
							comments_ul        = comments_wrap.find('ul').first(),
							existing_like_text = comments_wrap.find('.activity-like-count'),
							existing_comments  = comments_wrap.find('li').not('.activity-like-count'),
							new_like_text      = $(num_likes_text).unwrap('ul');

					// Take care of replacing the button with like/unlike
					button.html(but_text);
					button.attr('title', 'fav' == type ? BP_DTheme.remove_fav : BP_DTheme.mark_as_fav);
					button.fadeIn(200);

					// Remove existing like text, might be replaced if this isn't an unlike
					// or there are existing likes left
					existing_like_text.remove();

					// If we have 'you like this' type text
					if ( has_like_text ) {

						// console.log( num_likes_text );
						// console.log( new_like_text.html() );
						// console.log( bp_default_like_count );

						// If we have an existing UL prepend the LI
						if ( comments_ul.length ) {
							comments_ul.prepend( new_like_text );
							// console.log( 'UL found' );
						}

						// Otherwise lets wrap it up again and add to the comments
						else {
							comments_wrap.prepend( new_like_text.wrap('ul') );
							// console.log( 'UL not found' );
						}

					}

					// If we need to clean up the comment UL, this happens when
					// someone unlikes a post and there are no comments so an empty
					// UL element stays around causing some spacing and design flaws,
					// we remove that below
					if ( remove_comment_ul && comments_ul.length && ! existing_comments.length ) {
						comments_ul.remove();
					}

				});

				if ( 'fav' == type ) {
					bp_default_like_count = Number( jq('.item-list-tabs ul #activity-favorites span').html() || 0 ) + 1;

					if ( !jq('.item-list-tabs #activity-favs-personal-li').length ) {
						if ( !jq('.item-list-tabs #activity-favorites').length )
							jq('.item-list-tabs ul #activity-mentions').before( '<li id="activity-favorites"><a href="#">' + BP_DTheme.my_favs + ' <span>0</span></a></li>');

						jq('.item-list-tabs ul #activity-favorites span').html( bp_default_like_count );
					}

					target.removeClass('fav');
					target.addClass('unfav');
				}
				else {

					bp_default_like_count = Number( jq('.item-list-tabs ul #activity-favorites span').html() || 0 ) - 1;

					target.removeClass('unfav');
					target.addClass('fav');

					jq('.item-list-tabs ul #activity-favorites span').html( bp_default_like_count );

					if ( bp_default_like_count == 0 ) {
						if ( jq('.item-list-tabs ul #activity-favorites').hasClass('selected') )
							bp_activity_request( null, null );

						jq('.item-list-tabs ul #activity-favorites').remove();
					}
				}

				// BuddyBoss: usually there's parent().parent().parent(), but our markup is slightly different.
				if ( 'activity-favorites' == jq( '.item-list-tabs li.selected').attr('id') )
					target.parent().parent().slideUp(100);

				target.removeClass('loading');
				// document.write = document.oldDocumentWrite;
			});

			return false;
		}

		/* Delete activity stream items */
		if ( target.hasClass('delete-activity') ) {
			var li        = target.parents('div.activity ul li');
			var id        = li.attr('id').substr( 9, li.attr('id').length );
			var link_href = target.attr('href');
			var nonce     = link_href.split('_wpnonce=');
			var timestamp = li.prop( 'class' ).match( /date-recorded-([0-9]+)/ );

			nonce = nonce[1];

			target.addClass('loading');

			jq.post( ajaxurl, {
				action: 'delete_activity',
				'cookie': encodeURIComponent(document.cookie),
				'id': id,
				'_wpnonce': nonce
			},
			function(response) {

				if ( response[0] + response[1] == '-1' ) {
					li.prepend( response.substr( 2, response.length ) );
					li.children('#message').hide().fadeIn(300);
				} else {
					li.slideUp(300);

					// reset vars to get newest activities
					if ( timestamp && activity_last_recorded == timestamp[1] ) {
						newest_activities = '';
						activity_last_recorded  = 0;
					}
				}
			});

			return false;
		}

		// Spam activity stream items
		if ( target.hasClass( 'spam-activity' ) ) {
			var li = target.parents( 'div.activity ul li' );
			var timestamp = li.prop( 'class' ).match( /date-recorded-([0-9]+)/ );
			target.addClass( 'loading' );

			jq.post( ajaxurl, {
				action: 'bp_spam_activity',
				'cookie': encodeURIComponent( document.cookie ),
				'id': li.attr( 'id' ).substr( 9, li.attr( 'id' ).length ),
				'_wpnonce': target.attr( 'href' ).split( '_wpnonce=' )[1]
			},

			function(response) {
				if ( response[0] + response[1] === '-1' ) {
					li.prepend( response.substr( 2, response.length ) );
					li.children( '#message' ).hide().fadeIn(300);
				} else {
					li.slideUp( 300 );

					// reset vars to get newest activities
					if ( timestamp && activity_last_recorded == timestamp[1] ) {
						newest_activities = '';
						activity_last_recorded  = 0;
					}
				}
			});

			return false;
		}

		/* Load more updates at the end of the page */
		if ( target.parent().hasClass('load-more') ) {

			if ( jq("#content li.load-more").hasClass('loading') ) {
				return false;
			}

			jq("#content li.load-more").addClass('loading');

			if ( null == jq.cookie('bp-activity-oldestpage') )
				jq.cookie('bp-activity-oldestpage', 1, {
					path: '/'
				} );

			var oldest_page = ( jq.cookie('bp-activity-oldestpage') * 1 ) + 1;

			var just_posted = [];

			jq('.activity-list li.just-posted').each( function(){
				just_posted.push( jq(this).attr('id').replace( 'activity-','' ) );
			});

			jq.post( ajaxurl, {
				action: 'activity_get_older_updates',
				'cookie': encodeURIComponent(document.cookie),
				'page': oldest_page,
				'exclude_just_posted': just_posted.join(',')
			},
			function(response)
			{
				jq("#content li.load-more").removeClass('loading');
				jq.cookie( 'bp-activity-oldestpage', oldest_page, {
					path: '/'
				} );
				jq("#content ul.activity-list").append(response.contents);

				/* BuddyBoss: Attach PhotoSwipe */
				if ( typeof BuddyBossSwiper == 'object' && BuddyBossSwiper.hasOwnProperty( 'reset' ) ) {
					BuddyBossSwiper.reset();
				}

				target.parent().hide();
			}, 'json' );

			return false;
		}

		/* Load newest updates at the top of the list */
		if ( target.parent().hasClass('load-newest') ) {

			event.preventDefault();

			target.parent().hide();

			/**
			 * If a plugin is updating the recorded_date of an activity
			 * it will be loaded as a new one. We need to look in the
			 * stream and eventually remove similar ids to avoid "double".
			 */
			activity_html = jq.parseHTML( newest_activities );

			jq.each( activity_html, function( i, el ){
				if( 'LI' == el.nodeName && jq(el).hasClass( 'just-posted' ) ) {
					if( jq( '#' + jq(el).attr( 'id' ) ).length )
						jq( '#' + jq(el).attr( 'id' ) ).remove();
				}
			} );

			// Now the stream is cleaned, prepend newest
			jq( '#buddypress ul.activity-list' ).prepend( newest_activities );

			// reset the newest activities now they're displayed
			newest_activities = '';
		}

	});

	// Activity "Read More" links
	jq('.activity-read-more a').on('click', function(event) {
		var target = jq(event.target);
		var link_id = target.parent().attr('id').split('-');
		var a_id = link_id[3];
		var type = link_id[0]; /* activity or acomment */

		var inner_class = type == 'acomment' ? 'acomment-content' : 'activity-inner';
		//This fixes images disappearing when you click read more
		var a_inner = jq('#' + type + '-' + a_id + ' .' + inner_class + ':first' );
		jq(target).addClass('loading');

		jq.post( ajaxurl, {
			action: 'get_single_activity_content',
			'activity_id': a_id
		},
		function(response) {
			jq(a_inner).slideUp(300).html(response).slideDown(300);
		});

		return false;
	});

	/**** Activity Comments *******************************************************/

	/* Hide all activity comment forms */
	jq('form.ac-form').hide();

	/* Hide excess comments */
	if ( jq('.activity-comments').length )
		bp_legacy_theme_hide_comments();

	/* Activity list event delegation */
	jq('div.activity').on( 'click', function(event) {
		var target = jq(event.target);

		/* Comment / comment reply links */
		if ( target.hasClass('acomment-reply') || target.parent().hasClass('acomment-reply') ) {
			if ( target.parent().hasClass('acomment-reply') )
				target = target.parent();

			var id = target.attr('id');
			ids = id.split('-');

			var a_id = ids[2]
			var c_id = target.attr('href').substr( 10, target.attr('href').length );
			var form = jq( '#ac-form-' + a_id );

			form.css( 'display', 'none' );
			form.removeClass('root');
			jq('.ac-form').hide();

			/* Hide any error messages */
			form.children('div').each( function() {
				if ( jq(this).hasClass( 'error' ) )
					jq(this).hide();
			});

			if ( ids[1] != 'comment' ) {
				jq('#acomment-' + c_id).append( form );
			} else {
				jq('#activity-' + a_id + ' .activity-comments').append( form );
			}

			if ( form.parent().hasClass( 'activity-comments' ) )
				form.addClass('root');

			is_mobile = false;
			// Check if on a mobile device
			if ($('#mobile-check').is(':visible')){
				is_mobile = true;
			}

			if (is_mobile){ form.show(); } else{ form.slideDown( 200 );}

			jq.scrollTo( form, 500, {
				offset:-100,
				easing:'easeOutQuad'
			} );
			jq('#ac-form-' + ids[2] + ' textarea').focus();

			return false;
		}

		/* Activity comment posting */
		if ( target.attr('name') == 'ac_form_submit' ) {
			var form = target.parents( 'form' );
			var form_parent = form.parent();
			var form_id = form.attr('id').split('-');

			if ( !form_parent.hasClass('activity-comments') ) {
				var tmp_id = form_parent.attr('id').split('-');
				var comment_id = tmp_id[1];
			} else {
				var comment_id = form_id[2];
			}

			var content = jq( '#' + form.attr('id') + ' textarea' );

			/* Hide any error messages */
			jq( '#' + form.attr('id') + ' div.error').hide();
			target.addClass('loading').prop('disabled', true);
			content.addClass('loading').prop('disabled', true);

			var ajaxdata = {
				action: 'new_activity_comment',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce_new_activity_comment': jq("#_wpnonce_new_activity_comment").val(),
				'comment_id': comment_id,
				'form_id': form_id[2],
				'content': content.val()
			};

			// Akismet
			var ak_nonce = jq('#_bp_as_nonce_' + comment_id).val();
			if ( ak_nonce ) {
				ajaxdata['_bp_as_nonce_' + comment_id] = ak_nonce;
			}

			jq.post( ajaxurl, ajaxdata, function(response) {
				target.removeClass('loading');
				content.removeClass('loading');

				/* Check for errors and append if found. */
				if ( response[0] + response[1] == '-1' ) {
					form.append( jq( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );
				} else {
					form.fadeOut( 200, function() {
						if ( 0 == form.parent().children('ul').length ) {
							if ( form.parent().hasClass('activity-comments') ) {
								form.parent().prepend('<ul></ul>');
							} else {
								form.parent().append('<ul></ul>');
							}
						}

						/* Preceeding whitespace breaks output with jQuery 1.9.0 */
						var the_comment = jq.trim( response );

						form.parent().children('ul').append( jq( the_comment ).hide().fadeIn( 200 ) );
						form.children('textarea').val('');
						form.parent().parent().addClass('has-comments');
					} );
					jq( '#' + form.attr('id') + ' textarea').val('');

					/* Increase the "Reply (X)" button count */
					jq('#activity-' + form_id[2] + ' a.acomment-reply span').html( Number( jq('#activity-' + form_id[2] + ' a.acomment-reply span').html() ) + 1 );
				}

				jq(target).prop("disabled", false);
				jq(content).prop("disabled", false);
			});

			return false;
		}

		/* Deleting an activity comment */
		if ( target.hasClass('acomment-delete') ) {
			var link_href = target.attr('href');
			var comment_li = target.parent().parent();
			var form = comment_li.parents('div.activity-comments').children('form');

			var nonce = link_href.split('_wpnonce=');
			nonce = nonce[1];

			var comment_id = link_href.split('cid=');
			comment_id = comment_id[1].split('&');
			comment_id = comment_id[0];

			target.addClass('loading');

			/* Remove any error messages */
			jq('.activity-comments ul .error').remove();

			/* Reset the form position */
			comment_li.parents('.activity-comments').append(form);

			jq.post( ajaxurl, {
				action: 'delete_activity_comment',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': nonce,
				'id': comment_id
			},
			function(response) {
				/* Check for errors and append if found. */
				if ( response[0] + response[1] == '-1' ) {
					comment_li.prepend( jq( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );
				} else {
					var children = jq( '#' + comment_li.attr('id') + ' ul' ).children('li');
					var child_count = 0;
					jq(children).each( function() {
						if ( !jq(this).is(':hidden') )
							child_count++;
					});
					comment_li.fadeOut(200);

					/* Decrease the "Reply (X)" button count */
					var count_span = jq('#' + comment_li.parents('#activity-stream > li').attr('id') + ' a.acomment-reply span');
					var new_count = count_span.html() - ( 1 + child_count );
					count_span.html(new_count);

					/* If that was the last comment for the item, remove the has-comments class to clean up the styling */
					if ( 0 == new_count ) {
						jq(comment_li.parents('#activity-stream > li')).removeClass('has-comments');
					}
				}
			});

			return false;
		}

		// Spam an activity stream comment
		if ( target.hasClass( 'spam-activity-comment' ) ) {
			var link_href  = target.attr( 'href' );
			var comment_li = target.parent().parent();

			target.addClass('loading');

			// Remove any error messages
			jq( '.activity-comments ul div.error' ).remove();

			// Reset the form position
			comment_li.parents( '.activity-comments' ).append( comment_li.parents( '.activity-comments' ).children( 'form' ) );

			jq.post( ajaxurl, {
				action: 'bp_spam_activity_comment',
				'cookie': encodeURIComponent( document.cookie ),
				'_wpnonce': link_href.split( '_wpnonce=' )[1],
				'id': link_href.split( 'cid=' )[1].split( '&' )[0]
			},

			function ( response ) {
				// Check for errors and append if found.
				if ( response[0] + response[1] == '-1' ) {
					comment_li.prepend( jq( response.substr( 2, response.length ) ).hide().fadeIn( 200 ) );

				} else {
					var children = jq( '#' + comment_li.attr( 'id' ) + ' ul' ).children( 'li' );
					var child_count = 0;
					jq(children).each( function() {
						if ( !jq( this ).is( ':hidden' ) ) {
							child_count++;
						}
					});
					comment_li.fadeOut( 200 );

					// Decrease the "Reply (X)" button count
					var parent_li = comment_li.parents( '#activity-stream > li' );
					jq( '#' + parent_li.attr( 'id' ) + ' a.acomment-reply span' ).html( jq( '#' + parent_li.attr( 'id' ) + ' a.acomment-reply span' ).html() - ( 1 + child_count ) );
				}
			});

			return false;
		}

		/* Showing hidden comments - pause for half a second */
		if ( target.parent().hasClass('show-all') ) {
			target.parent().addClass('loading');

			setTimeout( function() {
				target.parent().parent().children('li').fadeIn(200, function() {
					target.parent().remove();
				});
			}, 600 );

			return false;
		}
	});

	/* Escape Key Press for cancelling comment forms + activity text area */
	jq(document).keydown( function(e) {
		e = e || window.event;
		if (e.target)
			element = e.target;
		else if (e.srcElement)
			element = e.srcElement;

		if( element.nodeType == 3)
			element = element.parentNode;

		if( e.ctrlKey == true || e.altKey == true || e.metaKey == true )
			return;

		var keyCode = (e.keyCode) ? e.keyCode : e.which;

		if ( keyCode == 27 ) {
			if (element.tagName == 'TEXTAREA') {
				if ( jq(element).hasClass('ac-input') )
					jq(element).parent().parent().parent().slideUp( 200 );
			}

			jq("#whats-new-form textarea").animate({
				height:'20px'
			}).blur();
			jq("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');
		}
	});

	/* Link for cancelling comment forms */
	jq('.ac-reply-cancel').on( 'click', function() {
		jq(this).closest('.ac-form').slideUp( 200 );
		return false;
	});

	/**** Directory Search ****************************************************/

	/* The search form on all directory pages */
	jq('.dir-search').on( 'click', function(event) {
		if ( jq(this).hasClass('no-ajax') )
			return;

		var target = jq(event.target);

		if ( target.attr('type') == 'submit' ) {
			var css_id = jq('.item-list-tabs li.selected').attr('id').split( '-' );
			var object = css_id[0];

			bp_filter_request( object, jq.cookie('bp-' + object + '-filter'), jq.cookie('bp-' + object + '-scope') , 'div.' + object, target.parent().children('label').children('input').val(), 1, jq.cookie('bp-' + object + '-extras') );

			return false;
		}
	});

	/**** Tabs and Filters ****************************************************/

	/* When a navigation tab is clicked - e.g. | All Groups | My Groups | */
	jq('div.item-list-tabs').on( 'click', function(event) {
		if ( jq(this).hasClass('no-ajax') )
			return;

		var targetElem = ( event.target.nodeName == 'SPAN' ) ? event.target.parentNode : event.target;
		var target     = jq( targetElem ).parent();

		if ( 'LI' == target[0].nodeName && !target.hasClass( 'last' ) ) {
			var css_id = target.attr('id').split( '-' );
			var object = css_id[0];

			if ( 'activity' == object )
				return false;

			var scope = css_id[1];
			var filter = jq("#" + object + "-order-select select").val();
			var search_terms = jq("#" + object + "_search").val();

			bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );

			return false;
		}
	});

	/* When the filter select box is changed re-query */
	jq('li.filter select').change( function() {
		if ( jq('.item-list-tabs li.selected').length )
			var el = jq('.item-list-tabs li.selected');
		else
			var el = jq(this);

		var css_id = el.attr('id').split('-');
		var object = css_id[0];
		var scope = css_id[1];
		var filter = jq(this).val();
		var search_terms = false;

		if ( jq('.dir-search input').length )
			search_terms = jq('.dir-search input').val();

		if ( 'friends' == object )
			object = 'members';

		bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );

		return false;
	});

	/* All pagination links run through this function */
	jq('#content').on( 'click', function(event) {
		var target = jq(event.target);

		if ( target.hasClass('button') )
			return true;

		if ( target.parent().parent().hasClass('pagination') && !target.parent().parent().hasClass('no-ajax') ) {
			if ( target.hasClass('dots') || target.hasClass('current') )
				return false;

			if ( jq('.item-list-tabs li.selected').length )
				var el = jq('.item-list-tabs li.selected');
			else
				var el = jq('li.filter select');

			var page_number = 1;
			var css_id = el.attr('id').split( '-' );
			var object = css_id[0];
			var search_terms = false;

			if ( jq('div.dir-search input').length )
				search_terms = jq('.dir-search input').val();

			if ( jq(target).hasClass('next') )
				var page_number = Number( jq('.pagination span.current').html() ) + 1;
			else if ( jq(target).hasClass('prev') )
				var page_number = Number( jq('.pagination span.current').html() ) - 1;
			else
				var page_number = Number( jq(target).html() );

			bp_filter_request( object, jq.cookie('bp-' + object + '-filter'), jq.cookie('bp-' + object + '-scope'), 'div.' + object, search_terms, page_number, jq.cookie('bp-' + object + '-extras') );

			return false;
		}

	});

	/**** New Forum Directory Post **************************************/

	/* Hit the "New Topic" button on the forums directory page */
	jq('a.show-hide-new').on( 'click', function() {
		if ( !jq('#new-topic-post').length )
			return false;

		if ( jq('#new-topic-post').is(":visible") )
			jq('#new-topic-post').slideUp(200);
		else
			jq('#new-topic-post').slideDown(200, function() {
				jq('#topic_title').focus();
			} );

		return false;
	});

	/* Cancel the posting of a new forum topic */
	jq('#submit_topic_cancel').on( 'click', function() {
		if ( !jq('#new-topic-post').length )
			return false;

		jq('#new-topic-post').slideUp(200);
		return false;
	});

	/* Clicking a forum tag */
	jq('#forum-directory-tags a').on( 'click', function() {
		bp_filter_request( 'forums', 'tags', jq.cookie('bp-forums-scope'), 'div.forums', jq(this).html().replace( /&nbsp;/g, '-' ), 1, jq.cookie('bp-forums-extras') );
		return false;
	});

	/** Invite Friends Interface ****************************************/

	/* Select a user from the list of friends and add them to the invite list */
	jq("#invite-list input").on( 'click', function() {
		jq('.ajax-loader').toggle();

		var friend_id = jq(this).val();

		if ( jq(this).prop('checked') == true )
			var friend_action = 'invite';
		else
			var friend_action = 'uninvite';

		jq('.item-list-tabs li.selected').addClass('loading');

		jq.post( ajaxurl, {
			action: 'groups_invite_user',
			'friend_action': friend_action,
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jq("#_wpnonce_invite_uninvite_user").val(),
			'friend_id': friend_id,
			'group_id': jq("#group_id").val()
		},
		function(response)
		{
			if ( jq("#message") )
				jq("#message").hide();

			jq('.ajax-loader').toggle();

			if ( friend_action == 'invite' ) {
				jq('#friend-list').append(response);
			} else if ( friend_action == 'uninvite' ) {
				jq('#friend-list li#uid-' + friend_id).remove();
			}

			jq('.item-list-tabs li.selected').removeClass('loading');
		});
	});

	/* Remove a user from the list of users to invite to a group */
	jq("#friend-list li a.remove").on('click', function() {
		jq('.ajax-loader').toggle();

		var friend_id = jq(this).attr('id');
		friend_id = friend_id.split('-');
		friend_id = friend_id[1];

		jq.post( ajaxurl, {
			action: 'groups_invite_user',
			'friend_action': 'uninvite',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jq("#_wpnonce_invite_uninvite_user").val(),
			'friend_id': friend_id,
			'group_id': jq("#group_id").val()
		},
		function(response)
		{
			jq('.ajax-loader').toggle();
			jq('#friend-list #uid-' + friend_id).remove();
			jq('#invite-list #f-' + friend_id).prop('checked', false);
		});

		return false;
	});

	/** Profile Visibility Settings *********************************/
	jq('.field-visibility-settings').hide();
	jq('.visibility-toggle-link').on( 'click', function() {
		var toggle_div = jq(this).parent();

		jq(toggle_div).fadeOut( 600, function(){
			jq(toggle_div).siblings('.field-visibility-settings').slideDown(400);
		});

		return false;
	} );

	jq('.field-visibility-settings-close').on( 'click', function() {
		var settings_div = jq(this).parent();

		jq(settings_div).slideUp( 400, function(){
			jq(settings_div).siblings('.field-visibility-settings-toggle').fadeIn(800);
		});

		return false;
	} );


	/** Friendship Requests **************************************/

	/* Accept and Reject friendship request buttons */
	jq("#friend-list a.accept, #friend-list a.reject").on( 'click', function() {
		var button = jq(this);
		var li = jq(this).parents('#friend-list li');
		var action_div = jq(this).parents('li div.action');

		var id = li.attr('id').substr( 11, li.attr('id').length );
		var link_href = button.attr('href');

		var nonce = link_href.split('_wpnonce=');
		nonce = nonce[1];

		if ( jq(this).hasClass('accepted') || jq(this).hasClass('rejected') )
			return false;

		if ( jq(this).hasClass('accept') ) {
			var action = 'accept_friendship';
			action_div.children('a.reject').css( 'visibility', 'hidden' );
		} else {
			var action = 'reject_friendship';
			action_div.children('a.accept').css( 'visibility', 'hidden' );
		}

		button.addClass('loading');

		jq.post( ajaxurl, {
			action: action,
			'cookie': encodeURIComponent(document.cookie),
			'id': id,
			'_wpnonce': nonce
		},
		function(response) {
			button.removeClass('loading');

			if ( response[0] + response[1] == '-1' ) {
				li.prepend( response.substr( 2, response.length ) );
				li.children('#message').hide().fadeIn(200);
			} else {
				button.fadeOut( 100, function() {
					if ( jq(this).hasClass('accept') ) {
						action_div.children('a.reject').hide();
						jq(this).html( BP_DTheme.accepted ).contents().unwrap();
					} else {
						action_div.children('a.accept').hide();
						jq(this).html( BP_DTheme.accepted ).contents().unwrap();
					}
				});
			}
		});

		return false;
	});

	/* BuddyBoss Fix: Add friend buttons don't work after pagination */
	attachFriendshipButtonHandlers = function() {

		/* Add / Remove friendship buttons */
		jq(".friendship-button a").on('click', function() {
			jq(this).parent().addClass('loading');
			var fid = jq(this).attr('id');
			fid = fid.split('-');
			fid = fid[1];

			var nonce = jq(this).attr('href');
			nonce = nonce.split('?_wpnonce=');
			nonce = nonce[1].split('&');
			nonce = nonce[0];

			var thelink = jq(this);

			jq.post( ajaxurl, {
				action: 'addremove_friend',
				'cookie': encodeURIComponent(document.cookie),
				'fid': fid,
				'_wpnonce': nonce
			},
			function(response)
			{
				var action = thelink.attr('rel');
				var parentdiv = thelink.parent();

				if ( action == 'add' ) {
					jq(parentdiv).fadeOut(200,
						function() {
							parentdiv.removeClass('add_friend');
							parentdiv.removeClass('loading');
							parentdiv.addClass('pending_friend');
							parentdiv.fadeIn(200).html(response);
						}
						);

				} else if ( action == 'remove' ) {
					jq(parentdiv).fadeOut(200,
						function() {
							parentdiv.removeClass('remove_friend');
							parentdiv.removeClass('loading');
							parentdiv.addClass('add');
							parentdiv.fadeIn(200).html(response);
						}
						);
				}
			});
			return false;
		} );
	} // attachFriendshipButtonHandlers()

	// Call this on page load
	attachFriendshipButtonHandlers();

	/** Group Join / Leave Buttons **************************************/

	jq(".group-button a").on('click', function() {
		var gid = jq(this).parent().attr('id');
		gid = gid.split('-');
		gid = gid[1];

		var nonce = jq(this).attr('href');
		nonce = nonce.split('?_wpnonce=');
		nonce = nonce[1].split('&');
		nonce = nonce[0];

		var thelink = jq(this);

		jq.post( ajaxurl, {
			action: 'joinleave_group',
			'cookie': encodeURIComponent(document.cookie),
			'gid': gid,
			'_wpnonce': nonce
		},
		function(response)
		{
			var parentdiv = thelink.parent();

			if ( !jq('body.directory').length )
				location.href = location.href;
			else {
				jq(parentdiv).fadeOut(200,
					function() {
						parentdiv.fadeIn(200).html(response);
					}
					);
			}
		});
		return false;
	} );

	/** Button disabling ************************************************/

	jq('.pending').click(function() {
		return false;
	});

	/** Private Messaging ******************************************/

	/** Message search*/
	jq('.message-search').on( 'click', function(event) {
		if ( jq(this).hasClass('no-ajax') )
			return;

		var target = jq(event.target);

		if ( target.attr('type') == 'submit' ) {
			//var css_id = jq('.item-list-tabs li.selected').attr('id').split( '-' );
			var object = 'messages';

			bp_filter_request( object, jq.cookie('bp-' + object + '-filter'), jq.cookie('bp-' + object + '-scope') , 'div.' + object, target.parent().children('label').children('input').val(), 1, jq.cookie('bp-' + object + '-extras') );

			return false;
		}
	});

	/* AJAX send reply functionality */
	jq("#send_reply_button").click(
		function() {
			var order = jq('#messages_order').val() || 'ASC',
			offset = jq('#message-recipients').offset();

			var button = jq("#send_reply_button");
			jq(button).addClass('loading');

			jq.post( ajaxurl, {
				action: 'messages_send_reply',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jq("#send_message_nonce").val(),

				'content': jq("#message_content").val(),
				'send_to': jq("#send_to").val(),
				'subject': jq("#subject").val(),
				'thread_id': jq("#thread_id").val()
			},
			function(response)
			{
				if ( response[0] + response[1] == "-1" ) {
					jq('#send-reply').prepend( response.substr( 2, response.length ) );
				} else {
					jq('#send-reply #message').remove();
					jq("#message_content").val('');

					if ( 'ASC' == order ) {
						jq('#send-reply').before( response );
					} else {
						jq('#message-recipients').after( response );
						jq(window).scrollTop(offset.top);
					}

					jq(".new-message").hide().slideDown( 200, function() {
						jq('.new-message').removeClass('new-message');
					});
				}
				jq(button).removeClass('loading');
			});

			return false;
		}
	);

	/* Marking private messages as read and unread */
	jq("#mark_as_read, #mark_as_unread").click(function() {
		var checkboxes_tosend = '';
		var checkboxes = jq("#message-threads tr td input[type='checkbox']");

		if ( 'mark_as_unread' == jq(this).attr('id') ) {
			var currentClass = 'read'
			var newClass = 'unread'
			var unreadCount = 1;
			var inboxCount = 0;
			var unreadCountDisplay = 'inline';
			var action = 'messages_markunread';
		} else {
			var currentClass = 'unread'
			var newClass = 'read'
			var unreadCount = 0;
			var inboxCount = 1;
			var unreadCountDisplay = 'none';
			var action = 'messages_markread';
		}

		checkboxes.each( function(i) {
			if(jq(this).is(':checked')) {
				if ( jq('#m-' + jq(this).attr('value')).hasClass(currentClass) ) {
					checkboxes_tosend += jq(this).attr('value');
					jq('#m-' + jq(this).attr('value')).removeClass(currentClass);
					jq('#m-' + jq(this).attr('value')).addClass(newClass);
					var thread_count = jq('#m-' + jq(this).attr('value') + ' td span.unread-count').html();

					jq('#m-' + jq(this).attr('value') + ' td span.unread-count').html(unreadCount);
					jq('#m-' + jq(this).attr('value') + ' td span.unread-count').css('display', unreadCountDisplay);

					var inboxcount = jq('tr.unread').length;

					jq('#user-messages span').html( inboxcount );

					if ( i != checkboxes.length - 1 ) {
						checkboxes_tosend += ','
					}
				}
			}
		});
		jq.post( ajaxurl, {
			action: action,
			'thread_ids': checkboxes_tosend
		});
		return false;
	});

	/* Selecting unread and read messages in inbox */
	jq("#message-type-select").change(
		function() {
			var selection = jq("#message-type-select").val();
			var checkboxes = jq("ul input[type='checkbox']");
			checkboxes.each( function(i) {
				checkboxes[i].checked = "";
			});

			switch(selection) {
				case 'unread':
					var checkboxes = jq("ul.unread input[type='checkbox']");
					break;
				case 'read':
					var checkboxes = jq("ul.read input[type='checkbox']");
					break;
			}
			if ( selection != '' ) {
				checkboxes.each( function(i) {
					checkboxes[i].checked = "checked";
				});
			} else {
				checkboxes.each( function(i) {
					checkboxes[i].checked = "";
				});
			}
		}
	);

	/* Bulk delete messages */
	jq("#delete_inbox_messages, #delete_sentbox_messages").on( 'click', function() {
		checkboxes_tosend = '';
		checkboxes = jq("#message-threads ul input[type='checkbox']");

		jq('#message').remove();
		jq(this).addClass('loading');

		jq(checkboxes).each( function(i) {
			if( jq(this).is(':checked') )
				checkboxes_tosend += jq(this).attr('value') + ',';
		});

		if ( '' == checkboxes_tosend ) {
			jq(this).removeClass('loading');
			return false;
		}

		jq.post( ajaxurl, {
			action: 'messages_delete',
			'thread_ids': checkboxes_tosend
		}, function(response) {
			if ( response[0] + response[1] == "-1" ) {
				jq('#message-threads').prepend( response.substr( 2, response.length ) );
			} else {
				jq('#message-threads').before( '<div id="message" class="updated"><p>' + response + '</p></div>' );

				jq(checkboxes).each( function(i) {
					if( jq(this).is(':checked') )
						jq(this).parent().parent().fadeOut(150);
				});
			}

			jq('#message').hide().slideDown(150);
			jq("#delete_inbox_messages, #delete_sentbox_messages").removeClass('loading');
		});
		return false;
	});

	/* Close site wide notices in the sidebar */
	jq("#close-notice").on( 'click', function() {
		jq(this).addClass('loading');
		jq('#sidebar div.error').remove();

		jq.post( ajaxurl, {
			action: 'messages_close_notice',
			'notice_id': jq('.notice').attr('rel').substr( 2, jq('.notice').attr('rel').length )
		},
		function(response) {
			jq("#close-notice").removeClass('loading');

			if ( response[0] + response[1] == '-1' ) {
				jq('.notice').prepend( response.substr( 2, response.length ) );
				jq( '#sidebar div.error').hide().fadeIn( 200 );
			} else {
				jq('.notice').slideUp( 100 );
			}
		});
		return false;
	});

	/* Toolbar & wp_list_pages Javascript IE6 hover class */
	jq("#wp-admin-bar ul.main-nav li, #nav li").mouseover( function() {
		jq(this).addClass('sfhover');
	});

	jq("#wp-admin-bar ul.main-nav li, #nav li").mouseout( function() {
		jq(this).removeClass('sfhover');
	});

	/* Clear BP cookies on logout */
	jq('a.logout').on( 'click', function() {
		jq.cookie('bp-activity-scope', null, {
			path: '/'
		});
		jq.cookie('bp-activity-filter', null, {
			path: '/'
		});
		jq.cookie('bp-activity-oldestpage', null, {
			path: '/'
		});

		var objects = [ 'members', 'groups', 'blogs', 'forums' ];
		jq(objects).each( function(i) {
			jq.cookie('bp-' + objects[i] + '-scope', null, {
				path: '/'
			} );
			jq.cookie('bp-' + objects[i] + '-filter', null, {
				path: '/'
			} );
			jq.cookie('bp-' + objects[i] + '-extras', null, {
				path: '/'
			} );
		});
	});

	/** Activity HeartBeat ************************************************/

	// Set the interval and the namespace event
	if ( typeof wp != 'undefined' && typeof wp.heartbeat != 'undefined' && typeof BP_DTheme.pulse != 'undefined' ) {

		wp.heartbeat.interval( Number( BP_DTheme.pulse ) );

		jq.fn.extend({
			'heartbeat-send': function() {
			return this.bind( 'heartbeat-send.buddypress' );
	        },
	    });

	}

	// Set the last id to request after
	jq( document ).on( 'heartbeat-send.buddypress', function( e, data ) {

		firstrow = 0;

		// First row is default latest activity id
		if ( jq( '#buddypress ul.activity-list li' ).first().prop( 'id' ) ) {
			// getting the timestamp
			timestamp = jq( '#buddypress ul.activity-list li' ).first().prop( 'class' ).match( /date-recorded-([0-9]+)/ );

			if ( timestamp ) {
				firstrow = timestamp[1];
			}
		}

		if ( 0 == activity_last_recorded || Number( firstrow ) > activity_last_recorded )
			activity_last_recorded = Number( firstrow );

		data['bp_activity_last_recorded'] = activity_last_recorded;
	});

	// Increment newest_activities and activity_last_recorded if data has been returned
	jq( document ).on( 'heartbeat-tick', function( e, data ) {

		// Only proceed if we have newest activities
		if ( ! data['bp_activity_newest_activities'] ) {
			return;
		}

		newest_activities = data['bp_activity_newest_activities']['activities'] + newest_activities;
		activity_last_recorded  = Number( data['bp_activity_newest_activities']['last_recorded'] );

		if ( jq( '#buddypress ul.activity-list li' ).first().hasClass( 'load-newest' ) )
			return;

		jq( '#buddypress ul.activity-list' ).prepend( '<li class="load-newest"><a href="#newest">' + BP_DTheme.newest + '</a></li>' );
	});
});

/* Setup activity scope and filter based on the current cookie settings. */
function bp_init_activity() {
	/* Reset the page */
	jq.cookie( 'bp-activity-oldestpage', 1, {
		path: '/'
	} );

	if ( null != jq.cookie('bp-activity-filter') && jq('#activity-filter-select').length )
		jq('#activity-filter-select select option[value="' + jq.cookie('bp-activity-filter') + '"]').prop( 'selected', true );

	/* Activity Tab Set */
	if ( null != jq.cookie('bp-activity-scope') && jq('.activity-type-tabs').length ) {
		jq('.activity-type-tabs li').each( function() {
			jq(this).removeClass('selected');
		});
		jq('#activity-' + jq.cookie('bp-activity-scope') + ', .item-list-tabs li.current').addClass('selected');
	}
}

/* Setup object scope and filter based on the current cookie settings for the object. */
function bp_init_objects(objects) {
	jq(objects).each( function(i) {
		if ( null != jq.cookie('bp-' + objects[i] + '-filter') && jq('#' + objects[i] + '-order-select select').length )
			jq('#' + objects[i] + '-order-select select option[value="' + jq.cookie('bp-' + objects[i] + '-filter') + '"]').prop( 'selected', true );

		if ( null != jq.cookie('bp-' + objects[i] + '-scope') && jq('div.' + objects[i]).length ) {
			jq('.item-list-tabs li').each( function() {
				jq(this).removeClass('selected');
			});
			jq('#' + objects[i] + '-' + jq.cookie('bp-' + objects[i] + '-scope') + ', #object-nav li.current').addClass('selected');
		}
	});
}

/* Filter the current content list (groups/members/blogs/topics) */
function bp_filter_request( object, filter, scope, target, search_terms, page, extras ) {
	if ( 'activity' == object )
		return false;

	if ( jq.query.get('s') && !search_terms )
		search_terms = jq.query.get('s');

	if ( null == scope )
		scope = 'all';

	/* Save the settings we want to remain persistent to a cookie */
	jq.cookie( 'bp-' + object + '-scope', scope, {
		path: '/'
	} );
	jq.cookie( 'bp-' + object + '-filter', filter, {
		path: '/'
	} );
	jq.cookie( 'bp-' + object + '-extras', extras, {
		path: '/'
	} );

	/* Set the correct selected nav and filter */
	jq('.item-list-tabs li').each( function() {
		jq(this).removeClass('selected');
	});
	jq('#' + object + '-' + scope + ', #object-nav li.current').addClass('selected');
	jq('.item-list-tabs li.selected').addClass('loading');
	jq('.item-list-tabs select option[value="' + filter + '"]').prop( 'selected', true );

	if ( 'friends' == object )
		object = 'members';

	if ( bp_ajax_request )
		bp_ajax_request.abort();

	bp_ajax_request = jq.post( ajaxurl, {
		action: object + '_filter',
		'cookie': encodeURIComponent(document.cookie),
		'object': object,
		'filter': filter,
		'search_terms': search_terms,
		'scope': scope,
		'page': page,
		'extras': extras
	},
	function(response)
	{
		jq(target).fadeOut( 100, function() {
			jq(this).html(response);
			jq(this).fadeIn(100);

			/* BuddyBoss Fix: Add friend buttons don't work after pagination */
			if ( 'members' == object ) {
				attachFriendshipButtonHandlers();
			}
		});
		jq('.item-list-tabs li.selected').removeClass('loading');
	});
}

/* Activity Loop Requesting */
function bp_activity_request(scope, filter) {
	/* Save the type and filter to a session cookie */
	jq.cookie( 'bp-activity-scope', scope, {
		path: '/'
	} );
	jq.cookie( 'bp-activity-filter', filter, {
		path: '/'
	} );
	jq.cookie( 'bp-activity-oldestpage', 1, {
		path: '/'
	} );

	/* Remove selected and loading classes from tabs */
	jq('.item-list-tabs li').each( function() {
		jq(this).removeClass('selected loading');
	});
	/* Set the correct selected nav and filter */
	jq('#activity-' + scope + ', .item-list-tabs li.current').addClass('selected');
	jq('#object-nav.item-list-tabs li.selected, div.activity-type-tabs li.selected').addClass('loading');
	jq('#activity-filter-select select option[value="' + filter + '"]').prop( 'selected', true );

	/* Reload the activity stream based on the selection */
	jq('.widget_bp_activity_widget h2 span.ajax-loader').show();

	if ( bp_ajax_request )
		bp_ajax_request.abort();

	bp_ajax_request = jq.post( ajaxurl, {
		action: 'activity_widget_filter',
		'cookie': encodeURIComponent(document.cookie),
		'_wpnonce_activity_filter': jq("#_wpnonce_activity_filter").val(),
		'scope': scope,
		'filter': filter
	},
	function(response)
	{
		jq('.widget_bp_activity_widget h2 span.ajax-loader').hide();

		jq('div.activity').fadeOut( 100, function() {
			jq(this).html(response.contents);
			jq(this).fadeIn(100);

			/* Selectively hide comments */
			bp_legacy_theme_hide_comments();
		});

		/* Update the feed link */
		if ( null != response.feed_url )
			jq('.directory #subnav li.feed a, .home-page #subnav li.feed a').attr('href', response.feed_url);

		jq('.item-list-tabs li.selected').removeClass('loading');

	}, 'json' );
}

/* Hide long lists of activity comments, only show the latest five root comments. */
function bp_legacy_theme_hide_comments() {
	var comments_divs = jq('div.activity-comments');

	if ( !comments_divs.length )
		return false;

	comments_divs.each( function() {
		if ( jq(this).children('ul').children('li:not(.activity-like-count)').length < 5 ) return;

		var comments_div = jq(this);
		var parent_li = comments_div.parents('#activity-stream > li');
		var comment_lis = jq(this).children('ul').children('li:not(.activity-like-count)');
		var comment_count = ' ';

		if ( jq('#' + parent_li.attr('id') + ' a.acomment-reply span').length )
			var comment_count = jq('#' + parent_li.attr('id') + ' a.acomment-reply span').html();

		comment_lis.each( function(i) {
			/* Show the latest 5 root comments */
			if ( i < comment_lis.length - 5 ) {
				jq(this).addClass('hidden');
				jq(this).toggle();

				if ( !i )
					jq(this).before( '<li class="show-all"><a href="#' + parent_li.attr('id') + '/show-all/" title="' + BP_DTheme.show_all_comments + '">' + BP_DTheme.show_all + ' ' + comment_count + ' ' + BP_DTheme.comments + '</a></li>' );
			}
		});

	});
}

/* BuddyBoss Functions */
// BuddyBoss added function: The style function, some times jQuery
// doesn't set certain styles we need with the $.css() function.
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


/* Helper Functions */

function checkAll() {
	var checkboxes = document.getElementsByTagName("input");
	for(var i=0; i<checkboxes.length; i++) {
		if(checkboxes[i].type == "checkbox") {
			if($("check_all").checked == "") {
				checkboxes[i].checked = "";
			}
			else {
				checkboxes[i].checked = "checked";
			}
		}
	}
}

/**
 * Deselects any select options or input options for the specified field element.
 *
 * @param {String} container HTML ID of the field
 * @since BuddyPress (1.2.0)
 */
function clear( container ) {
	container = document.getElementById( container );
	if ( ! container ) {
		return;
	}

	var radioButtons = container.getElementsByTagName( 'INPUT' ),
		options = container.getElementsByTagName( 'OPTION' ),
		i       = 0;

	if ( radioButtons ) {
		for ( i = 0; i < radioButtons.length; i++ ) {
			radioButtons[i].checked = '';
		}
	}

	if ( options ) {
		for ( i = 0; i < options.length; i++ ) {
			options[i].selected = false;
		}
	}
}

/* Returns a querystring of BP cookies (cookies beginning with 'bp-') */
function bp_get_cookies() {
	// get all cookies and split into an array
	var allCookies   = document.cookie.split(";");

	var bpCookies    = {};
	var cookiePrefix = 'bp-';

	// loop through cookies
	for (var i = 0; i < allCookies.length; i++) {
		var cookie    = allCookies[i];
		var delimiter = cookie.indexOf("=");
		var name      = jq.trim( unescape( cookie.slice(0, delimiter) ) );
		var value     = unescape( cookie.slice(delimiter + 1) );

		// if BP cookie, store it
		if ( name.indexOf(cookiePrefix) == 0 ) {
			bpCookies[name] = value;
		}
	}

	// returns BP cookies as querystring
	return encodeURIComponent( jq.param(bpCookies) );
}

/* ScrollTo plugin - just inline and minified */
;(function($){var h=$.scrollTo=function(a,b,c){$(window).scrollTo(a,b,c)};h.defaults={axis:'xy',duration:parseFloat($.fn.jquery)>=1.3?0:1,limit:true};h.window=function(a){return $(window)._scrollable()};$.fn._scrollable=function(){return this.map(function(){var a=this,isWin=!a.nodeName||$.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!isWin)return a;var b=(a.contentWindow||a).document||a.ownerDocument||a;return/webkit/i.test(navigator.userAgent)||b.compatMode=='BackCompat'?b.body:b.documentElement})};$.fn.scrollTo=function(e,f,g){if(typeof f=='object'){g=f;f=0}if(typeof g=='function')g={onAfter:g};if(e=='max')e=9e9;g=$.extend({},h.defaults,g);f=f||g.duration;g.queue=g.queue&&g.axis.length>1;if(g.queue)f/=2;g.offset=both(g.offset);g.over=both(g.over);return this._scrollable().each(function(){if(e==null)return;var d=this,$elem=$(d),targ=e,toff,attr={},win=$elem.is('html,body');switch(typeof targ){case'number':case'string':if(/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)){targ=both(targ);break}targ=$(targ,this);if(!targ.length)return;case'object':if(targ.is||targ.style)toff=(targ=$(targ)).offset()}$.each(g.axis.split(''),function(i,a){var b=a=='x'?'Left':'Top',pos=b.toLowerCase(),key='scroll'+b,old=d[key],max=h.max(d,a);if(toff){attr[key]=toff[pos]+(win?0:old-$elem.offset()[pos]);if(g.margin){attr[key]-=parseInt(targ.css('margin'+b))||0;attr[key]-=parseInt(targ.css('border'+b+'Width'))||0}attr[key]+=g.offset[pos]||0;if(g.over[pos])attr[key]+=targ[a=='x'?'width':'height']()*g.over[pos]}else{var c=targ[pos];attr[key]=c.slice&&c.slice(-1)=='%'?parseFloat(c)/100*max:c}if(g.limit&&/^\d+$/.test(attr[key]))attr[key]=attr[key]<=0?0:Math.min(attr[key],max);if(!i&&g.queue){if(old!=attr[key])animate(g.onAfterFirst);delete attr[key]}});animate(g.onAfter);function animate(a){$elem.animate(attr,f,g.easing,a&&function(){a.call(this,e,g)})}}).end()};h.max=function(a,b){var c=b=='x'?'Width':'Height',scroll='scroll'+c;if(!$(a).is('html,body'))return a[scroll]-$(a)[c.toLowerCase()]();var d='client'+c,html=a.ownerDocument.documentElement,body=a.ownerDocument.body;return Math.max(html[scroll],body[scroll])-Math.min(html[d],body[d])};function both(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);

/* jQuery Cookie plugin */
jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1;}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000));}else{date=options.expires;}expires='; expires='+date.toUTCString();}var path=options.path?'; path='+(options.path):'';var domain=options.domain?'; domain='+(options.domain):'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('');}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break;}}}return cookieValue;}};

/* jQuery querystring plugin */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('M 6(A){4 $11=A.11||\'&\';4 $V=A.V===r?r:j;4 $1p=A.1p===r?\'\':\'[]\';4 $13=A.13===r?r:j;4 $D=$13?A.D===j?"#":"?":"";4 $15=A.15===r?r:j;v.1o=M 6(){4 f=6(o,t){8 o!=1v&&o!==x&&(!!t?o.1t==t:j)};4 14=6(1m){4 m,1l=/\\[([^[]*)\\]/g,T=/^([^[]+)(\\[.*\\])?$/.1r(1m),k=T[1],e=[];19(m=1l.1r(T[2]))e.u(m[1]);8[k,e]};4 w=6(3,e,7){4 o,y=e.1b();b(I 3!=\'X\')3=x;b(y===""){b(!3)3=[];b(f(3,L)){3.u(e.h==0?7:w(x,e.z(0),7))}n b(f(3,1a)){4 i=0;19(3[i++]!=x);3[--i]=e.h==0?7:w(3[i],e.z(0),7)}n{3=[];3.u(e.h==0?7:w(x,e.z(0),7))}}n b(y&&y.T(/^\\s*[0-9]+\\s*$/)){4 H=1c(y,10);b(!3)3=[];3[H]=e.h==0?7:w(3[H],e.z(0),7)}n b(y){4 H=y.B(/^\\s*|\\s*$/g,"");b(!3)3={};b(f(3,L)){4 18={};1w(4 i=0;i<3.h;++i){18[i]=3[i]}3=18}3[H]=e.h==0?7:w(3[H],e.z(0),7)}n{8 7}8 3};4 C=6(a){4 p=d;p.l={};b(a.C){v.J(a.Z(),6(5,c){p.O(5,c)})}n{v.J(1u,6(){4 q=""+d;q=q.B(/^[?#]/,\'\');q=q.B(/[;&]$/,\'\');b($V)q=q.B(/[+]/g,\' \');v.J(q.Y(/[&;]/),6(){4 5=1e(d.Y(\'=\')[0]||"");4 c=1e(d.Y(\'=\')[1]||"");b(!5)8;b($15){b(/^[+-]?[0-9]+\\.[0-9]*$/.1d(c))c=1A(c);n b(/^[+-]?[0-9]+$/.1d(c))c=1c(c,10)}c=(!c&&c!==0)?j:c;b(c!==r&&c!==j&&I c!=\'1g\')c=c;p.O(5,c)})})}8 p};C.1H={C:j,1G:6(5,1f){4 7=d.Z(5);8 f(7,1f)},1h:6(5){b(!f(5))8 d.l;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];19(3!=x&&e.h!=0){3=3[e.1b()]}8 I 3==\'1g\'?3:3||""},Z:6(5){4 3=d.1h(5);b(f(3,1a))8 v.1E(j,{},3);n b(f(3,L))8 3.z(0);8 3},O:6(5,c){4 7=!f(c)?x:c;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];d.l[k]=w(3,e.z(0),7);8 d},w:6(5,c){8 d.N().O(5,c)},1s:6(5){8 d.O(5,x).17()},1z:6(5){8 d.N().1s(5)},1j:6(){4 p=d;v.J(p.l,6(5,7){1y p.l[5]});8 p},1F:6(Q){4 D=Q.B(/^.*?[#](.+?)(?:\\?.+)?$/,"$1");4 S=Q.B(/^.*?[?](.+?)(?:#.+)?$/,"$1");8 M C(Q.h==S.h?\'\':S,Q.h==D.h?\'\':D)},1x:6(){8 d.N().1j()},N:6(){8 M C(d)},17:6(){6 F(G){4 R=I G=="X"?f(G,L)?[]:{}:G;b(I G==\'X\'){6 1k(o,5,7){b(f(o,L))o.u(7);n o[5]=7}v.J(G,6(5,7){b(!f(7))8 j;1k(R,5,F(7))})}8 R}d.l=F(d.l);8 d},1B:6(){8 d.N().17()},1D:6(){4 i=0,U=[],W=[],p=d;4 16=6(E){E=E+"";b($V)E=E.B(/ /g,"+");8 1C(E)};4 1n=6(1i,5,7){b(!f(7)||7===r)8;4 o=[16(5)];b(7!==j){o.u("=");o.u(16(7))}1i.u(o.P(""))};4 F=6(R,k){4 12=6(5){8!k||k==""?[5].P(""):[k,"[",5,"]"].P("")};v.J(R,6(5,7){b(I 7==\'X\')F(7,12(5));n 1n(W,12(5),7)})};F(d.l);b(W.h>0)U.u($D);U.u(W.P($11));8 U.P("")}};8 M C(1q.S,1q.D)}}(v.1o||{});',62,106,'|||target|var|key|function|value|return|||if|val|this|tokens|is||length||true|base|keys||else||self||false|||push|jQuery|set|null|token|slice|settings|replace|queryObject|hash|str|build|orig|index|typeof|each|parsed|Array|new|copy|SET|join|url|obj|search|match|queryString|spaces|chunks|object|split|get||separator|newKey|prefix|parse|numbers|encode|COMPACT|temp|while|Object|shift|parseInt|test|decodeURIComponent|type|number|GET|arr|EMPTY|add|rx|path|addFields|query|suffix|location|exec|REMOVE|constructor|arguments|undefined|for|empty|delete|remove|parseFloat|compact|encodeURIComponent|toString|extend|load|has|prototype'.split('|'),0,{}))