// AJAX Functions
var jq = jQuery;

// Global variable to prevent multiple AJAX requests
var bp_ajax_request = null;

jq(document).ready( function() {
	/**** Page Load Actions *******************************************************/

	// initialise drop-down menu
    jq("ul.sf-menu").superfish({ 
      delay:        0, // No delay on mouseout. Set to 1000 for one second delay. 
			autoArrows:   true, // Set to "false" to disable auto-generation of arrows in drop-downs.
			dropShadows:  true // Set to "false" to disable drop shadows.
    }); 

	/* Hide Forums Post Form */
	if ( '-1' == window.location.search.indexOf('new') && jq('div.forums').length )
		jq('div#new-topic-post').hide();
	else
		jq('div#new-topic-post').show();

	/* Activity filter and scope set */
	bp_init_activity();

	/* Object filter and scope set. */
	var objects = [ 'members', 'groups', 'blogs', 'forums' ];
	bp_init_objects( objects );

	/* @mention Compose Scrolling */
	if ( jq.query.get('r') && jq('textarea#whats-new').length ) {
		jq("form#whats-new-form textarea").animate({height:'75px'});
		jq.scrollTo( jq('textarea#whats-new'), 500, { offset:-125, easing:'easeOutQuad' } );
		jq('textarea#whats-new').focus();
	}

	/**** Buddyboss Picture Grid ********************************************************/

	var $pic_grid = jq('#buddyboss-pics-grid');
	if( $pic_grid.length === 1 )
	{
		var opts = ( jq.browser.msie && parseInt(jq.browser.version, 10) < 8 )
						 ? { autoDimensions: true, autoScale: true, width: 840, height: 550 }
						 : {};
		
		$pic_grid.find( 'a' ).fancybox( opts );
	}


	/**** Activity Posting ********************************************************/

	/* Textarea focus */
	var textAreaExpanded = false, 
			textAreaAnimating = false,
			textAreaTyping = false, 
			wnTextArea = jq('#whats-new'),
			wnOptions = jq('#whats-new-options'),
			wnSubmit = jq("#aw-whats-new-submit"),
			previewPane = jq('#whats-new-pic-preview'),
			previewInner = jq('#whats-new-pic-preview-inner'),
			maxWidth = jq('#whats-new-options').width(),
			has_pic = false;	
	
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
		var $img = jq('#whats-new-pic-preview-inner img');
		
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
	} );
	
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
	/*
	jq('#whats-new').focusout( function(e){
		setTimeout(function(){
			textAreaTyping = false;
			handleTextAreaFocusOut();
		}, 111);
	});
	*/
	
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
	
	if (jq('#whats-new-pic').length)
	{
		var uploader = new qq.FileUploader({
			
			element: document.getElementById('whats-new-pic'),
			
			action: ajaxurl,
			
			debug: true,
			
			params: {
				action: 'buddyboss_post_picture',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce_post_update': jq("input#_wpnonce_post_update").val()
			},
			
			// validation    
			// ex. ['jpg', 'jpeg', 'png', 'gif'] or []
			allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
			
			// each file size limit in bytes
			// this option isn't supported in all browsers
			sizeLimit: 10 * 1024 * 1024, // max size   
			minSizeLimit: 1024, // min size
			
			// events         
			// you can return false to abort submit
			onSubmit: function(id, fileName){
				//console.log('////// onsubmit ///////');
				jq('#whats-new-pic-preview').animate({height:'0px'})
				jq('#whats-new-pic').addClass('ajax-loader');
			},
			onProgress: function(id, fileName, loaded, total){},
			onComplete: function(id, fileName, responseJSON){
				//console.log('// ----- upload success ----- //');
				//console.log(responseJSON);
				if (responseJSON.hasOwnProperty('error'))
				{
					alert(responseJSON.message);
					return false;
				}
					
				var pic_uri = responseJSON.hasOwnProperty('url') ? responseJSON.url : false;
				
				//console.log(pic_uri);
				//console.log(previewPane)
				
				previewPane.animate({height: '0px'}, function()
				{
					if ( pic_uri )
					{
						previewInner.html('<img src="'+pic_uri+'" />');
						
						var picWidth,
								picHeight,
								picRatio;
						
						var $img = jq('#whats-new-pic-preview-inner img')
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
								jq('#whats-new-pic').removeClass('ajax-loader');
								previewPane.animate({ height: picHeight+10+'px' });
							});
						
						has_pic = responseJSON;
					}
				});
	
			},
			onCancel: function(id, fileName){},
			
			messages: {
			    // error messages, see qq.FileUploaderBasic for content            
			},
			showMessage: function(message){ alert(message); }
		});
	} 
	
	/* pic upload */
	jq('#whats-new-pic').live('click', function(e)
	{
		
	});
	
	
	
	/* New posts */
	jq("input#aw-whats-new-submit").click( function() {
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
		var content = jq("textarea#whats-new").val();
		
		if ( has_pic !== false && has_pic.hasOwnProperty('name') )
		{
			content += ' <a target="_blank" class="buddyboss-pics-picture-link" href="'+has_pic.url+'" title="'+has_pic.name+'">'+has_pic.name+'</a>';
		}
		
		//console.log ( 'pre post update' );
		//console.log ( content );
		
		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = jq("#whats-new-post-object").val();
		}

		jq.post( ajaxurl, {
			action: 'post_update',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_post_update': jq("input#_wpnonce_post_update").val(),
			'content': content,
			'object': object,
			'item_id': item_id,
			'has_pic': has_pic
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
				jq( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				if ( 0 == jq("ul.activity-list").length ) {
					jq("div.error").slideUp(100).remove();
					jq("div#message").slideUp(100).remove();
					jq("div.activity").append( '<ul id="activity-stream" class="activity-list item-list">' );
				}
				
				previewInner.html('');
				previewPane.stop().animate({height:'0px'});

				jq("ul#activity-stream").prepend(response);
				jq("ul#activity-stream li:first").addClass('new-update');

				if ( 0 != jq("div#latest-update").length ) {
					var l = jq("ul#activity-stream li.new-update .activity-content .activity-inner p").html();
					var v = jq("ul#activity-stream li.new-update .activity-content .activity-header p a.view").attr('href');

					var ltext = jq("ul#activity-stream li.new-update .activity-content .activity-inner p").text();

					var u = '';
					if ( ltext != '' )
						u = '&quot;' + l + '&quot; ';

					u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

					jq("div#latest-update").slideUp(300,function(){
						jq("div#latest-update").html( u );
						jq("div#latest-update").slideDown(300);
					});
				}
				else {
					console.log( jq( '#is-buddyboss-pics-grid' ) );
					if ( 0 !== jq( '#is-buddyboss-pics-grid' ).length )
					{
						var refreshUrl = jq( '#is-buddyboss-pics-grid' ).attr('data');
						if ( refreshUrl.length > 6 )
							document.location = refreshUrl;
					}
				}

				jq("li.new-update").hide().slideDown( 300 );
				jq("li.new-update").removeClass( 'new-update' );
				jq("textarea#whats-new").val('');
			}

			jq("form#whats-new-form textarea").animate({height:'20px'});
			jq("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');
		});
		has_pic = false;
		return false;
	});

	/* List tabs event delegation */
	jq('div.activity-type-tabs').click( function(event) {
		var target = jq(event.target).parent();

		if ( event.target.nodeName == 'STRONG' || event.target.nodeName == 'SPAN' )
			target = target.parent();
		else if ( event.target.nodeName != 'A' )
			return false;

		/* Reset the page */
		jq.cookie( 'bp-activity-oldestpage', 1, {path: '/'} );

		/* Activity Stream Tabs */
		var scope = target.attr('id').substr( 9, target.attr('id').length );
		var filter = jq("#activity-filter-select select").val();

		if ( scope == 'mentions' )
			jq( 'li#' + target.attr('id') + ' a strong' ).remove();

		bp_activity_request(scope, filter, target);

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
	jq('div.activity').click( function(event) {
		var target = jq(event.target);

		/* Favoriting activity stream items */
		if ( target.hasClass('fav') || target.hasClass('unfav') ) {
			var type = target.hasClass('fav') ? 'fav' : 'unfav';
			var parent = target.parent().parent().parent();
			var parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

			target.addClass('loading');

			jq.post( ajaxurl, {
				action: 'activity_mark_' + type,
				'cookie': encodeURIComponent(document.cookie),
				'id': parent_id
			},
			function(response) {
				target.removeClass('loading');

				target.fadeOut( 100, function() {
					jq(this).html(response);
					jq(this).fadeIn(100);
				});

				if ( 'fav' == type ) {
					if ( !jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').length ){
						jq('div.activity ul li#activity-'+parent_id+' div.activity-comments').prepend( '<ul><li class="activity-like-count">1 person likes this.</li></ul>');}else{jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html( Number(jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html().substr(0,1) ) + 1 + ' people like this.' );}

					target.removeClass('fav');
					target.addClass('unfav');
					
					
				} else {
					target.removeClass('unfav');
					target.addClass('fav');
					
					if(jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html().substr(0,1) == 2){jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html( Number(jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html().substr(0,1) ) - 1 + ' person likes this.' );
					} else {
					jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html( Number(jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html().substr(0,1) ) - 1 + ' people like this.' );
					}

					if ( jq('div.activity ul li#activity-'+parent_id+' ul li.activity-like-count').html().substr(0,1) == 0 ) {
						
						jq('div.activity ul li#activity-'+parent_id+' ul:first').remove()
					}
				}

				if ( 'activity-favorites' == jq( 'div.activity li.selected').attr('id') )
					target.parent().parent().parent().slideUp(100);
			});

			return false;
		}

		/* Delete activity stream items */
		if ( target.hasClass('delete-activity') ) {
			var li        = target.parents('div.activity ul li');
			var id        = li.attr('id').substr( 9, li.attr('id').length );
			var link_href = target.attr('href');
			var nonce     = link_href.split('_wpnonce=');

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
					li.children('div#message').hide().fadeIn(300);
				} else {
					li.slideUp(300);
				}
			});

			return false;
		}

		/* Load more updates at the end of the page */
		if ( target.parent().hasClass('load-more') ) {
			jq("#content li.load-more").addClass('loading');

			if ( null == jq.cookie('bp-activity-oldestpage') )
				jq.cookie('bp-activity-oldestpage', 1, {path: '/'} );

			var oldest_page = ( jq.cookie('bp-activity-oldestpage') * 1 ) + 1;

			jq.post( ajaxurl, {
				action: 'activity_get_older_updates',
				'cookie': encodeURIComponent(document.cookie),
				'page': oldest_page
			},
			function(response)
			{
				jq("#content li.load-more").removeClass('loading');
				jq.cookie( 'bp-activity-oldestpage', oldest_page, {path: '/'} );
				jq("#content ul.activity-list").append(response.contents);

				target.parent().hide();
			}, 'json' );

			return false;
		}

	});

	// Activity "Read More" links
	jq('.activity-read-more a').live('click', function(event) {
		var target = jq(event.target);
		var link_id = target.parent().attr('id').split('-');
		var a_id = link_id[3];
		var type = link_id[0]; /* activity or acomment */

		var inner_class = type == 'acomment' ? 'acomment-content p' : 'activity-inner';
		var a_inner = jq('li#' + type + '-' + a_id + ' .' + inner_class + ':first' );
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
	if ( jq('div.activity-comments').length )
		bp_dtheme_hide_comments();

	/* Activity list event delegation */
	jq('div.activity').click( function(event) {
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
				jq('div.activity-comments li#acomment-' + c_id).append( form );
			} else {
				jq('li#activity-' + a_id + ' div.activity-comments').append( form );
			}

	 		if ( form.parent().hasClass( 'activity-comments' ) )
				form.addClass('root');

			form.slideDown( 200 );
			
			// BuddyBoss: Stop this from happening if we're inside an inline overlay.
			if ( target.parents('.buddyboss-activity-ajax').length !== 1 )
			{
				jq.scrollTo( form, 500, { offset:-100, easing:'easeOutQuad' } );
			}

			jq('#ac-form-' + ids[2] + ' textarea').focus();

			return false;
		}

		/* Activity comment posting */
		if ( target.attr('name') == 'ac_form_submit' ) {
			var form = target.parent().parent();
			var form_parent = form.parent();
			var form_id = form.attr('id').split('-');

			if ( !form_parent.hasClass('activity-comments') ) {
				var tmp_id = form_parent.attr('id').split('-');
				var comment_id = tmp_id[1];
			} else {
				var comment_id = form_id[2];
			}

			/* Hide any error messages */
			jq( 'form#' + form + ' div.error').hide();
			target.addClass('loading').prop('disabled', true);

			jq.post( ajaxurl, {
				action: 'new_activity_comment',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce_new_activity_comment': jq("input#_wpnonce_new_activity_comment").val(),
				'comment_id': comment_id,
				'form_id': form_id[2],
				'content': jq('form#' + form.attr('id') + ' textarea').val()
			},
			function(response)
			{
				target.removeClass('loading');

				/* Check for errors and append if found. */
				if ( response[0] + response[1] == '-1' ) {
					form.append( response.substr( 2, response.length ) ).hide().fadeIn( 200 );
				} else {
					form.fadeOut( 200,
						function() {
							if ( 0 == form.parent().children('ul').length ) {
								if ( form.parent().hasClass('activity-comments') )
									form.parent().prepend('<ul></ul>');
								else
									form.parent().append('<ul></ul>');
							}

							form.parent().children('ul').append(response).hide().fadeIn( 200 );
							form.children('textarea').val('');
							form.parent().parent().addClass('has-comments');
						}
					);
					jq( 'form#' + form + ' textarea').val('');
					
					//console.log( jq('li#activity-' + form_id[2] + ' a.acomment-reply span').html().replace(/[^\d]*/g, '') );
					/*
					console.log( 'li#activity-' + form_id[2] + ' a.acomment-reply span' );
					console.log( jq('li#activity-' + form_id[2] + ' a.acomment-reply span') );
					console.log( Number( jq('li#activity-' + form_id[2] + ' a.acomment-reply span').html() ) );
					console.log( parseInt( jq('li#activity-' + form_id[2] + ' a.acomment-reply span').html() ) );
					*/

					/* Increase the "Reply (X)" button count */
					var numComments = Number( jq('li#activity-' + form_id[2] + ' a.acomment-reply span').html().replace(/[^\d]*/g, '') ) + 1;
					jq('li#activity-' + form_id[2] + ' a.acomment-reply span').html( '('+numComments+')' );
					
				}

				jq(target).prop("disabled", false);
			});

			return false;
		}

		/* Deleting an activity comment */
		if ( target.hasClass('acomment-delete') ) {
			var link_href = target.attr('href');
			var comment_li = target.parents('li').first();
			var form = comment_li.parents('div.activity-comments').children('form');

			var nonce = link_href.split('_wpnonce=');
				nonce = nonce[1];

			var comment_id = link_href.split('cid=');
				comment_id = comment_id[1].split('&');
				comment_id = comment_id[0];

			target.addClass('loading');

			/* Remove any error messages */
			jq('div.activity-comments ul div.error').remove();

			/* Reset the form position */
			comment_li.parents('div.activity-comments').append(form);

			jq.post( ajaxurl, {
				action: 'delete_activity_comment',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': nonce,
				'id': comment_id
			},
			function(response)
			{
				/* Check for errors and append if found. */
				if ( response[0] + response[1] == '-1' ) {
					comment_li.prepend( response.substr( 2, response.length ) ).hide().fadeIn( 200 );
				} else {
					var children = jq( 'li#' + comment_li.attr('id') + ' ul' ).children('li');
					var child_count = 0;
					jq(children).each( function() {
						if ( !jq(this).is(':hidden') )
							child_count++;
					});
					comment_li.fadeOut(200);

					/* Decrease the "Reply (X)" button count */
					var parent_li = comment_li.parents('ul#activity-stream > li');
					//console.log( '//////////////////' );
					//console.log( jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').html().replace(/[()]/g, '') );
					var numComments = Number( jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').html().replace(/[()]/g, '') ) - ( 1 + child_count );
					jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').html( '(' + numComments + ')' );
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

	/* Escape Key Press for cancelling comment forms */
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
		}
	});

	/**** Directory Search ****************************************************/

	/* The search form on all directory pages */
	jq('div.dir-search').click( function(event) {
		if ( jq(this).hasClass('no-ajax') )
			return;

		var target = jq(event.target);

		if ( target.attr('type') == 'submit' ) {
			var css_id = jq('div.item-list-tabs li.selected').attr('id').split( '-' );
			var object = css_id[0];

			bp_filter_request( object, jq.cookie('bp-' + object + '-filter'), jq.cookie('bp-' + object + '-scope') , 'div.' + object, target.parent().children('label').children('input').val(), 1, jq.cookie('bp-' + object + '-extras') );

			return false;
		}
	});

	/**** Tabs and Filters ****************************************************/

	/* When a navigation tab is clicked - e.g. | All Groups | My Groups | */
	jq('div.item-list-tabs').click( function(event) {
		if ( jq(this).hasClass('no-ajax') )
			return;

		var target = jq(event.target).parent();

		if ( 'LI' == event.target.parentNode.nodeName && !target.hasClass('last') ) {
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
		if ( jq('div.item-list-tabs li.selected').length )
			var el = jq('div.item-list-tabs li.selected');
		else
			var el = jq(this);

		var css_id = el.attr('id').split('-');
		var object = css_id[0];
		var scope = css_id[1];
		var filter = jq(this).val();
		var search_terms = false;

		if ( jq('div.dir-search input').length )
			search_terms = jq('div.dir-search input').val();

		if ( 'friends' == object )
			object = 'members';

		bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );

		return false;
	});

	/* All pagination links run through this function */
	jq('div#content').click( function(event) {
		var target = jq(event.target);

		if ( target.hasClass('button') )
			return true;

		if ( target.parent().parent().hasClass('pagination') && !target.parent().parent().hasClass('no-ajax') ) {
			if ( target.hasClass('dots') || target.hasClass('current') )
				return false;

			if ( jq('div.item-list-tabs li.selected').length )
				var el = jq('div.item-list-tabs li.selected');
			else
				var el = jq('li.filter select');

			var page_number = 1;
			var css_id = el.attr('id').split( '-' );
			var object = css_id[0];
			var search_terms = false;

			if ( jq('div.dir-search input').length )
				search_terms = jq('div.dir-search input').val();

			if ( jq(target).hasClass('next') )
				var page_number = Number( jq('div.pagination span.current').html() ) + 1;
			else if ( jq(target).hasClass('prev') )
				var page_number = Number( jq('div.pagination span.current').html() ) - 1;
			else
				var page_number = Number( jq(target).html() );

			bp_filter_request( object, jq.cookie('bp-' + object + '-filter'), jq.cookie('bp-' + object + '-scope'), 'div.' + object, search_terms, page_number, jq.cookie('bp-' + object + '-extras') );

			return false;
		}

	});

	/**** New Forum Directory Post **************************************/

	/* Hit the "New Topic" button on the forums directory page */
	jq('a.show-hide-new').click( function() {
		if ( !jq('div#new-topic-post').length )
			return false;

		if ( jq('div#new-topic-post').is(":visible") )
			jq('div#new-topic-post').slideUp(200);
		else
			jq('div#new-topic-post').slideDown(200, function() { jq('#topic_title').focus(); } );

		return false;
	});

	/* Cancel the posting of a new forum topic */
	jq('input#submit_topic_cancel').click( function() {
		if ( !jq('div#new-topic-post').length )
			return false;

		jq('div#new-topic-post').slideUp(200);
		return false;
	});

	/* Clicking a forum tag */
	jq('div#forum-directory-tags a').click( function() {
		bp_filter_request( 'forums', 'tags', jq.cookie('bp-forums-scope'), 'div.forums', jq(this).html().replace( /&nbsp;/g, '-' ), 1, jq.cookie('bp-forums-extras') );
		return false;
	});

	/** Invite Friends Interface ****************************************/

	/* Select a user from the list of friends and add them to the invite list */
	jq("div#invite-list input").click( function() {
		jq('.ajax-loader').toggle();

		var friend_id = jq(this).val();

		if ( jq(this).prop('checked') == true )
			var friend_action = 'invite';
		else
			var friend_action = 'uninvite';

		jq('div.item-list-tabs li.selected').addClass('loading');

		jq.post( ajaxurl, {
			action: 'groups_invite_user',
			'friend_action': friend_action,
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jq("input#_wpnonce_invite_uninvite_user").val(),
			'friend_id': friend_id,
			'group_id': jq("input#group_id").val()
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

			jq('div.item-list-tabs li.selected').removeClass('loading');
		});
	});

	/* Remove a user from the list of users to invite to a group */
	jq("#friend-list li a.remove").live('click', function() {
		jq('.ajax-loader').toggle();

		var friend_id = jq(this).attr('id');
		friend_id = friend_id.split('-');
		friend_id = friend_id[1];

		jq.post( ajaxurl, {
			action: 'groups_invite_user',
			'friend_action': 'uninvite',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jq("input#_wpnonce_invite_uninvite_user").val(),
			'friend_id': friend_id,
			'group_id': jq("input#group_id").val()
		},
		function(response)
		{
			jq('.ajax-loader').toggle();
			jq('#friend-list li#uid-' + friend_id).remove();
			jq('#invite-list input#f-' + friend_id).prop('checked', false);
		});

		return false;
	});

	/** Friendship Requests **************************************/

	/* Accept and Reject friendship request buttons */
	jq("ul#friend-list a.accept, ul#friend-list a.reject").click( function() {
		var button = jq(this);
		var li = jq(this).parents('ul#friend-list li');
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
				li.children('div#message').hide().fadeIn(200);
			} else {
				button.fadeOut( 100, function() {
					if ( jq(this).hasClass('accept') ) {
						action_div.children('a.reject').hide();
						jq(this).html( BP_DTheme.accepted ).fadeIn(50);
						jq(this).addClass('accepted');
					} else {
						action_div.children('a.accept').hide();
						jq(this).html( BP_DTheme.rejected ).fadeIn(50);
						jq(this).addClass('rejected');
					}
				});
			}
		});

		return false;
	});

	/* Add / Remove friendship buttons */
	jq("div.friendship-button a").live('click', function() {
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
						parentdiv.addClass('pending');
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

	/** Group Join / Leave Buttons **************************************/

	jq("div.group-button a").live('click', function() {
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

	jq('div.pending').click(function() {
		return false;
	});

	/** Private Messaging ******************************************/

	/* AJAX send reply functionality */
	jq("input#send_reply_button").click(
		function() {
			var order = jq('#messages_order').val() || 'ASC',
				offset = jq('#message-recipients').offset();

			var button = jq("input#send_reply_button");
			jq(button).addClass('loading');

			jq.post( ajaxurl, {
				action: 'messages_send_reply',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jq("input#send_message_nonce").val(),

				'content': jq("#message_content").val(),
				'send_to': jq("input#send_to").val(),
				'subject': jq("input#subject").val(),
				'thread_id': jq("input#thread_id").val()
			},
			function(response)
			{
				if ( response[0] + response[1] == "-1" ) {
					jq('form#send-reply').prepend( response.substr( 2, response.length ) );
				} else {
					jq('form#send-reply div#message').remove();
					jq("#message_content").val('');

					if ( 'ASC' == order ) {
						jq('form#send-reply').before( response );
					} else {
						jq('#message-recipients').after( response );
						jq(window).scrollTop(offset.top);
					}

					jq("div.new-message").hide().slideDown( 200, function() {
						jq('div.new-message').removeClass('new-message');
					});
				}
				jq(button).removeClass('loading');
			});

			return false;
		}
	);

	/* Marking private messages as read and unread */
	jq("a#mark_as_read, a#mark_as_unread").click(function() {
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
				if ( jq('tr#m-' + jq(this).attr('value')).hasClass(currentClass) ) {
					checkboxes_tosend += jq(this).attr('value');
					jq('tr#m-' + jq(this).attr('value')).removeClass(currentClass);
					jq('tr#m-' + jq(this).attr('value')).addClass(newClass);
					var thread_count = jq('tr#m-' + jq(this).attr('value') + ' td span.unread-count').html();

					jq('tr#m-' + jq(this).attr('value') + ' td span.unread-count').html(unreadCount);
					jq('tr#m-' + jq(this).attr('value') + ' td span.unread-count').css('display', unreadCountDisplay);

					var inboxcount = jq('tr.unread').length;

					jq('a#user-messages span').html( inboxcount );
					
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
	jq("select#message-type-select").change(
		function() {
			var selection = jq("select#message-type-select").val();
			var checkboxes = jq("td input[type='checkbox']");
			checkboxes.each( function(i) {
				checkboxes[i].checked = "";
			});

			switch(selection) {
				case 'unread':
					var checkboxes = jq("tr.unread td input[type='checkbox']");
				break;
				case 'read':
					var checkboxes = jq("tr.read td input[type='checkbox']");
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
	jq("a#delete_inbox_messages, a#delete_sentbox_messages").click( function() {
		checkboxes_tosend = '';
		checkboxes = jq("#message-threads tr td input[type='checkbox']");

		jq('div#message').remove();
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

			jq('div#message').hide().slideDown(150);
			jq("a#delete_inbox_messages, a#delete_sentbox_messages").removeClass('loading');
		});
		return false;
	});

	/* Close site wide notices in the sidebar */
	jq("a#close-notice").click( function() {
		jq(this).addClass('loading');
		jq('div#sidebar div.error').remove();

		jq.post( ajaxurl, {
			action: 'messages_close_notice',
			'notice_id': jq('.notice').attr('rel').substr( 2, jq('.notice').attr('rel').length )
		},
		function(response) {
			jq("a#close-notice").removeClass('loading');

			if ( response[0] + response[1] == '-1' ) {
				jq('.notice').prepend( response.substr( 2, response.length ) );
				jq( 'div#sidebar div.error').hide().fadeIn( 200 );
			} else {
				jq('.notice').slideUp( 100 );
			}
		});
		return false;
	});

	/* Admin Bar & wp_list_pages Javascript IE6 hover class */
	jq("#wp-admin-bar ul.main-nav li, #nav li").mouseover( function() {
		jq(this).addClass('sfhover');
	});

	jq("#wp-admin-bar ul.main-nav li, #nav li").mouseout( function() {
		jq(this).removeClass('sfhover');
	});

	/* Clear BP cookies on logout */
	jq('a.logout').click( function() {
		jq.cookie('bp-activity-scope', null, {path: '/'});
		jq.cookie('bp-activity-filter', null, {path: '/'});
		jq.cookie('bp-activity-oldestpage', null, {path: '/'});

		var objects = [ 'members', 'groups', 'blogs', 'forums' ];
		jq(objects).each( function(i) {
			jq.cookie('bp-' + objects[i] + '-scope', null, {path: '/'} );
			jq.cookie('bp-' + objects[i] + '-filter', null, {path: '/'} );
			jq.cookie('bp-' + objects[i] + '-extras', null, {path: '/'} );
		});
	});
});

/* Setup activity scope and filter based on the current cookie settings. */
function bp_init_activity() {
	/* Reset the page */
	jq.cookie( 'bp-activity-oldestpage', 1, {path: '/'} );

	if ( null != jq.cookie('bp-activity-filter') && jq('#activity-filter-select').length )
		jq('#activity-filter-select select option[value="' + jq.cookie('bp-activity-filter') + '"]').prop( 'selected', true );

	/* Activity Tab Set */
	if ( null != jq.cookie('bp-activity-scope') && jq('div.activity-type-tabs').length ) {
		jq('div.activity-type-tabs li').each( function() {
			jq(this).removeClass('selected');
		});
		jq('li#activity-' + jq.cookie('bp-activity-scope') + ', div.item-list-tabs li.current').addClass('selected');
	}
}

/* Setup object scope and filter based on the current cookie settings for the object. */
function bp_init_objects(objects) {
	jq(objects).each( function(i) {
		if ( null != jq.cookie('bp-' + objects[i] + '-filter') && jq('li#' + objects[i] + '-order-select select').length )
			jq('li#' + objects[i] + '-order-select select option[value="' + jq.cookie('bp-' + objects[i] + '-filter') + '"]').prop( 'selected', true );

		if ( null != jq.cookie('bp-' + objects[i] + '-scope') && jq('div.' + objects[i]).length ) {
			jq('div.item-list-tabs li').each( function() {
				jq(this).removeClass('selected');
			});
			jq('div.item-list-tabs li#' + objects[i] + '-' + jq.cookie('bp-' + objects[i] + '-scope') + ', div.item-list-tabs#object-nav li.current').addClass('selected');
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
	jq.cookie( 'bp-' + object + '-scope', scope, {path: '/'} );
	jq.cookie( 'bp-' + object + '-filter', filter, {path: '/'} );
	jq.cookie( 'bp-' + object + '-extras', extras, {path: '/'} );

	/* Set the correct selected nav and filter */
	jq('div.item-list-tabs li').each( function() {
		jq(this).removeClass('selected');
	});
	jq('div.item-list-tabs li#' + object + '-' + scope + ', div.item-list-tabs#object-nav li.current').addClass('selected');
	jq('div.item-list-tabs li.selected').addClass('loading');
	jq('div.item-list-tabs select option[value="' + filter + '"]').prop( 'selected', true );

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
	 	});
		jq('div.item-list-tabs li.selected').removeClass('loading');
	});
}

/* Activity Loop Requesting */
function bp_activity_request(scope, filter) {
	/* Save the type and filter to a session cookie */
	jq.cookie( 'bp-activity-scope', scope, {path: '/'} );
	jq.cookie( 'bp-activity-filter', filter, {path: '/'} );
	jq.cookie( 'bp-activity-oldestpage', 1, {path: '/'} );

	/* Remove selected and loading classes from tabs */
	jq('div.item-list-tabs li').each( function() {
		jq(this).removeClass('selected loading');
	});
	/* Set the correct selected nav and filter */
	jq('li#activity-' + scope + ', div.item-list-tabs li.current').addClass('selected');
	jq('div#object-nav.item-list-tabs li.selected, div.activity-type-tabs li.selected').addClass('loading');
	jq('#activity-filter-select select option[value="' + filter + '"]').prop( 'selected', true );

	/* Reload the activity stream based on the selection */
	jq('.widget_bp_activity_widget h2 span.ajax-loader').show();

	if ( bp_ajax_request )
		bp_ajax_request.abort();

	bp_ajax_request = jq.post( ajaxurl, {
		action: 'activity_widget_filter',
		'cookie': encodeURIComponent(document.cookie),
		'_wpnonce_activity_filter': jq("input#_wpnonce_activity_filter").val(),
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
			bp_dtheme_hide_comments();
		});

		/* Update the feed link */
		if ( null != response.feed_url )
			jq('.directory div#subnav li.feed a, .home-page div#subnav li.feed a').attr('href', response.feed_url);

		jq('div.item-list-tabs li.selected').removeClass('loading');

	}, 'json' );
}

/* Hide long lists of activity comments, only show the latest five root comments. */
function bp_dtheme_hide_comments() {
	var comments_divs = jq('div.activity-comments');

	if ( !comments_divs.length )
		return false;

	comments_divs.each( function() {
		if ( jq(this).children('ul').children('li').length < 5 ) return;

		var comments_div = jq(this);
		var parent_li = comments_div.parents('ul#activity-stream > li');
		var comment_lis = jq(this).children('ul').children('li');
		var comment_count = ' ';

		if ( jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').length )
			var comment_count = jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').html();

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

function clear(container) {
	if( !document.getElementById(container) ) return;

	var container = document.getElementById(container);

	if ( radioButtons = container.getElementsByTagName('INPUT') ) {
		for(var i=0; i<radioButtons.length; i++) {
			radioButtons[i].checked = '';
		}
	}

	if ( options = container.getElementsByTagName('OPTION') ) {
		for(var i=0; i<options.length; i++) {
			options[i].selected = false;
		}
	}

	return;
}

/* ScrollTo plugin - just inline and minified */
;(function(d){var k=d.scrollTo=function(a,i,e){d(window).scrollTo(a,i,e)};k.defaults={axis:'xy',duration:parseFloat(d.fn.jquery)>=1.3?0:1};k.window=function(a){return d(window)._scrollable()};d.fn._scrollable=function(){return this.map(function(){var a=this,i=!a.nodeName||d.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!i)return a;var e=(a.contentWindow||a).document||a.ownerDocument||a;return d.browser.safari||e.compatMode=='BackCompat'?e.body:e.documentElement})};d.fn.scrollTo=function(n,j,b){if(typeof j=='object'){b=j;j=0}if(typeof b=='function')b={onAfter:b};if(n=='max')n=9e9;b=d.extend({},k.defaults,b);j=j||b.speed||b.duration;b.queue=b.queue&&b.axis.length>1;if(b.queue)j/=2;b.offset=p(b.offset);b.over=p(b.over);return this._scrollable().each(function(){var q=this,r=d(q),f=n,s,g={},u=r.is('html,body');switch(typeof f){case'number':case'string':if(/^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(f)){f=p(f);break}f=d(f,this);case'object':if(f.is||f.style)s=(f=d(f)).offset()}d.each(b.axis.split(''),function(a,i){var e=i=='x'?'Left':'Top',h=e.toLowerCase(),c='scroll'+e,l=q[c],m=k.max(q,i);if(s){g[c]=s[h]+(u?0:l-r.offset()[h]);if(b.margin){g[c]-=parseInt(f.css('margin'+e))||0;g[c]-=parseInt(f.css('border'+e+'Width'))||0}g[c]+=b.offset[h]||0;if(b.over[h])g[c]+=f[i=='x'?'width':'height']()*b.over[h]}else{var o=f[h];g[c]=o.slice&&o.slice(-1)=='%'?parseFloat(o)/100*m:o}if(/^\d+$/.test(g[c]))g[c]=g[c]<=0?0:Math.min(g[c],m);if(!a&&b.queue){if(l!=g[c])t(b.onAfterFirst);delete g[c]}});t(b.onAfter);function t(a){r.animate(g,j,b.easing,a&&function(){a.call(this,n,b)})}}).end()};k.max=function(a,i){var e=i=='x'?'Width':'Height',h='scroll'+e;if(!d(a).is('html,body'))return a[h]-d(a)[e.toLowerCase()]();var c='client'+e,l=a.ownerDocument.documentElement,m=a.ownerDocument.body;return Math.max(l[h],m[h])-Math.min(l[c],m[c])};function p(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);

/* jQuery Easing Plugin, v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/ */
jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return -h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}return -h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return -h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}return -h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return -h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return -h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}if(f==g){return a+h}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return -h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return -h/2*(Math.sqrt(1-f*f)-1)+a}return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return -(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k/2)==2){return e+l}if(!j){j=k*(0.3*1.5)}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}if(h<1){return -0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});

/* jQuery Cookie plugin */
jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1;}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000));}else{date=options.expires;}expires='; expires='+date.toUTCString();}var path=options.path?'; path='+(options.path):'';var domain=options.domain?'; domain='+(options.domain):'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('');}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break;}}}return cookieValue;}};

/* jQuery querystring plugin */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('M 6(A){4 $11=A.11||\'&\';4 $V=A.V===r?r:j;4 $1p=A.1p===r?\'\':\'[]\';4 $13=A.13===r?r:j;4 $D=$13?A.D===j?"#":"?":"";4 $15=A.15===r?r:j;v.1o=M 6(){4 f=6(o,t){8 o!=1v&&o!==x&&(!!t?o.1t==t:j)};4 14=6(1m){4 m,1l=/\\[([^[]*)\\]/g,T=/^([^[]+)(\\[.*\\])?$/.1r(1m),k=T[1],e=[];19(m=1l.1r(T[2]))e.u(m[1]);8[k,e]};4 w=6(3,e,7){4 o,y=e.1b();b(I 3!=\'X\')3=x;b(y===""){b(!3)3=[];b(f(3,L)){3.u(e.h==0?7:w(x,e.z(0),7))}n b(f(3,1a)){4 i=0;19(3[i++]!=x);3[--i]=e.h==0?7:w(3[i],e.z(0),7)}n{3=[];3.u(e.h==0?7:w(x,e.z(0),7))}}n b(y&&y.T(/^\\s*[0-9]+\\s*$/)){4 H=1c(y,10);b(!3)3=[];3[H]=e.h==0?7:w(3[H],e.z(0),7)}n b(y){4 H=y.B(/^\\s*|\\s*$/g,"");b(!3)3={};b(f(3,L)){4 18={};1w(4 i=0;i<3.h;++i){18[i]=3[i]}3=18}3[H]=e.h==0?7:w(3[H],e.z(0),7)}n{8 7}8 3};4 C=6(a){4 p=d;p.l={};b(a.C){v.J(a.Z(),6(5,c){p.O(5,c)})}n{v.J(1u,6(){4 q=""+d;q=q.B(/^[?#]/,\'\');q=q.B(/[;&]$/,\'\');b($V)q=q.B(/[+]/g,\' \');v.J(q.Y(/[&;]/),6(){4 5=1e(d.Y(\'=\')[0]||"");4 c=1e(d.Y(\'=\')[1]||"");b(!5)8;b($15){b(/^[+-]?[0-9]+\\.[0-9]*$/.1d(c))c=1A(c);n b(/^[+-]?[0-9]+$/.1d(c))c=1c(c,10)}c=(!c&&c!==0)?j:c;b(c!==r&&c!==j&&I c!=\'1g\')c=c;p.O(5,c)})})}8 p};C.1H={C:j,1G:6(5,1f){4 7=d.Z(5);8 f(7,1f)},1h:6(5){b(!f(5))8 d.l;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];19(3!=x&&e.h!=0){3=3[e.1b()]}8 I 3==\'1g\'?3:3||""},Z:6(5){4 3=d.1h(5);b(f(3,1a))8 v.1E(j,{},3);n b(f(3,L))8 3.z(0);8 3},O:6(5,c){4 7=!f(c)?x:c;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];d.l[k]=w(3,e.z(0),7);8 d},w:6(5,c){8 d.N().O(5,c)},1s:6(5){8 d.O(5,x).17()},1z:6(5){8 d.N().1s(5)},1j:6(){4 p=d;v.J(p.l,6(5,7){1y p.l[5]});8 p},1F:6(Q){4 D=Q.B(/^.*?[#](.+?)(?:\\?.+)?$/,"$1");4 S=Q.B(/^.*?[?](.+?)(?:#.+)?$/,"$1");8 M C(Q.h==S.h?\'\':S,Q.h==D.h?\'\':D)},1x:6(){8 d.N().1j()},N:6(){8 M C(d)},17:6(){6 F(G){4 R=I G=="X"?f(G,L)?[]:{}:G;b(I G==\'X\'){6 1k(o,5,7){b(f(o,L))o.u(7);n o[5]=7}v.J(G,6(5,7){b(!f(7))8 j;1k(R,5,F(7))})}8 R}d.l=F(d.l);8 d},1B:6(){8 d.N().17()},1D:6(){4 i=0,U=[],W=[],p=d;4 16=6(E){E=E+"";b($V)E=E.B(/ /g,"+");8 1C(E)};4 1n=6(1i,5,7){b(!f(7)||7===r)8;4 o=[16(5)];b(7!==j){o.u("=");o.u(16(7))}1i.u(o.P(""))};4 F=6(R,k){4 12=6(5){8!k||k==""?[5].P(""):[k,"[",5,"]"].P("")};v.J(R,6(5,7){b(I 7==\'X\')F(7,12(5));n 1n(W,12(5),7)})};F(d.l);b(W.h>0)U.u($D);U.u(W.P($11));8 U.P("")}};8 M C(1q.S,1q.D)}}(v.1o||{});',62,106,'|||target|var|key|function|value|return|||if|val|this|tokens|is||length||true|base|keys||else||self||false|||push|jQuery|set|null|token|slice|settings|replace|queryObject|hash|str|build|orig|index|typeof|each|parsed|Array|new|copy|SET|join|url|obj|search|match|queryString|spaces|chunks|object|split|get||separator|newKey|prefix|parse|numbers|encode|COMPACT|temp|while|Object|shift|parseInt|test|decodeURIComponent|type|number|GET|arr|EMPTY|add|rx|path|addFields|query|suffix|location|exec|REMOVE|constructor|arguments|undefined|for|empty|delete|remove|parseFloat|compact|encodeURIComponent|toString|extend|load|has|prototype'.split('|'),0,{}))

/* ajax file upload */
var qq=qq||{};qq.extend=function(a,b){for(var c in b){a[c]=b[c]}};qq.indexOf=function(a,b,c){if(a.indexOf)return a.indexOf(b,c);c=c||0;var d=a.length;if(c<0)c+=d;for(;c<d;c++){if(c in a&&a[c]===b){return c}}return-1};qq.getUniqueId=function(){var a=0;return function(){return a++}}();qq.attach=function(a,b,c){if(a.addEventListener){a.addEventListener(b,c,false)}else if(a.attachEvent){a.attachEvent("on"+b,c)}};qq.detach=function(a,b,c){if(a.removeEventListener){a.removeEventListener(b,c,false)}else if(a.attachEvent){a.detachEvent("on"+b,c)}};qq.preventDefault=function(a){if(a.preventDefault){a.preventDefault()}else{a.returnValue=false}};qq.insertBefore=function(a,b){b.parentNode.insertBefore(a,b)};qq.remove=function(a){a.parentNode.removeChild(a)};qq.contains=function(a,b){if(a==b)return true;if(a.contains){return a.contains(b)}else{return!!(b.compareDocumentPosition(a)&8)}};qq.toElement=function(){var a=document.createElement("div");return function(b){a.innerHTML=b;var c=a.firstChild;a.removeChild(c);return c}}();qq.css=function(a,b){if(b.opacity!=null){if(typeof a.style.opacity!="string"&&typeof a.filters!="undefined"){b.filter="alpha(opacity="+Math.round(100*b.opacity)+")"}}qq.extend(a.style,b)};qq.hasClass=function(a,b){var c=new RegExp("(^| )"+b+"( |$)");return c.test(a.className)};qq.addClass=function(a,b){if(!qq.hasClass(a,b)){a.className+=" "+b}};qq.removeClass=function(a,b){var c=new RegExp("(^| )"+b+"( |$)");a.className=a.className.replace(c," ").replace(/^\s+|\s+$/g,"")};qq.setText=function(a,b){a.innerText=b;a.textContent=b};qq.children=function(a){var b=[],c=a.firstChild;while(c){if(c.nodeType==1){b.push(c)}c=c.nextSibling}return b};qq.getByClass=function(a,b){if(a.querySelectorAll){return a.querySelectorAll("."+b)}var c=[];var d=a.getElementsByTagName("*");var e=d.length;for(var f=0;f<e;f++){if(qq.hasClass(d[f],b)){c.push(d[f])}}return c};qq.obj2url=function(a,b,c){var d=[],e="&",f=function(a,c){var e=b?/\[\]$/.test(b)?b:b+"["+c+"]":c;if(e!="undefined"&&c!="undefined"){d.push(typeof a==="object"?qq.obj2url(a,e,true):Object.prototype.toString.call(a)==="[object Function]"?encodeURIComponent(e)+"="+encodeURIComponent(a()):encodeURIComponent(e)+"="+encodeURIComponent(a))}};if(!c&&b){e=/\?/.test(b)?/\?$/.test(b)?"":"&":"?";d.push(b);d.push(qq.obj2url(a))}else if(Object.prototype.toString.call(a)==="[object Array]"&&typeof a!="undefined"){for(var g=0,h=a.length;g<h;++g){f(a[g],g)}}else if(typeof a!="undefined"&&a!==null&&typeof a==="object"){for(var g in a){f(a[g],g)}}else{d.push(encodeURIComponent(b)+"="+encodeURIComponent(a))}return d.join(e).replace(/^&/,"").replace(/%20/g,"+")};var qq=qq||{};qq.FileUploaderBasic=function(a){this._options={debug:false,action:"/server/upload",params:{},button:null,multiple:true,maxConnections:3,allowedExtensions:[],sizeLimit:0,minSizeLimit:0,onSubmit:function(a,b){},onProgress:function(a,b,c,d){},onComplete:function(a,b,c){},onCancel:function(a,b){},messages:{typeError:"{file} has invalid extension. Only {extensions} are allowed.",sizeError:"{file} is too large, maximum file size is {sizeLimit}.",minSizeError:"{file} is too small, minimum file size is {minSizeLimit}.",emptyError:"{file} is empty, please select files again without it.",onLeave:"The files are being uploaded, if you leave now the upload will be cancelled."},showMessage:function(a){alert(a)}};qq.extend(this._options,a);this._filesInProgress=0;this._handler=this._createUploadHandler();if(this._options.button){this._button=this._createUploadButton(this._options.button)}this._preventLeaveInProgress()};qq.FileUploaderBasic.prototype={setParams:function(a){this._options.params=a},getInProgress:function(){return this._filesInProgress},_createUploadButton:function(a){var b=this;return new qq.UploadButton({element:a,multiple:this._options.multiple&&qq.UploadHandlerXhr.isSupported(),onChange:function(a){b._onInputChange(a)}})},_createUploadHandler:function(){var a=this,b;if(qq.UploadHandlerXhr.isSupported()){b="UploadHandlerXhr"}else{b="UploadHandlerForm"}var c=new qq[b]({debug:this._options.debug,action:this._options.action,maxConnections:this._options.maxConnections,onProgress:function(b,c,d,e){a._onProgress(b,c,d,e);a._options.onProgress(b,c,d,e)},onComplete:function(b,c,d){a._onComplete(b,c,d);a._options.onComplete(b,c,d)},onCancel:function(b,c){a._onCancel(b,c);a._options.onCancel(b,c)}});return c},_preventLeaveInProgress:function(){var a=this;qq.attach(window,"beforeunload",function(b){if(!a._filesInProgress){return}var b=b||window.event;b.returnValue=a._options.messages.onLeave;return a._options.messages.onLeave})},_onSubmit:function(a,b){this._filesInProgress++},_onProgress:function(a,b,c,d){},_onComplete:function(a,b,c){this._filesInProgress--;if(c.error){this._options.showMessage(c.error)}},_onCancel:function(a,b){this._filesInProgress--},_onInputChange:function(a){if(this._handler instanceof qq.UploadHandlerXhr){this._uploadFileList(a.files)}else{if(this._validateFile(a)){this._uploadFile(a)}}this._button.reset()},_uploadFileList:function(a){for(var b=0;b<a.length;b++){if(!this._validateFile(a[b])){return}}for(var b=0;b<a.length;b++){this._uploadFile(a[b])}},_uploadFile:function(a){var b=this._handler.add(a);var c=this._handler.getName(b);if(this._options.onSubmit(b,c)!==false){this._onSubmit(b,c);this._handler.upload(b,this._options.params)}},_validateFile:function(a){var b,c;if(a.value){b=a.value.replace(/.*(\/|\\)/,"")}else{b=a.fileName!=null?a.fileName:a.name;c=a.fileSize!=null?a.fileSize:a.size}if(!this._isAllowedExtension(b)){this._error("typeError",b);return false}else if(c===0){this._error("emptyError",b);return false}else if(c&&this._options.sizeLimit&&c>this._options.sizeLimit){this._error("sizeError",b);return false}else if(c&&c<this._options.minSizeLimit){this._error("minSizeError",b);return false}return true},_error:function(a,b){function d(a,b){c=c.replace(a,b)}var c=this._options.messages[a];d("{file}",this._formatFileName(b));d("{extensions}",this._options.allowedExtensions.join(", "));d("{sizeLimit}",this._formatSize(this._options.sizeLimit));d("{minSizeLimit}",this._formatSize(this._options.minSizeLimit));this._options.showMessage(c)},_formatFileName:function(a){if(a.length>33){a=a.slice(0,19)+"..."+a.slice(-13)}return a},_isAllowedExtension:function(a){var b=-1!==a.indexOf(".")?a.replace(/.*[.]/,"").toLowerCase():"";var c=this._options.allowedExtensions;if(!c.length){return true}for(var d=0;d<c.length;d++){if(c[d].toLowerCase()==b){return true}}return false},_formatSize:function(a){var b=-1;do{a=a/1024;b++}while(a>99);return Math.max(a,.1).toFixed(1)+["kB","MB","GB","TB","PB","EB"][b]}};qq.FileUploader=function(a){qq.FileUploaderBasic.apply(this,arguments);qq.extend(this._options,{element:null,listElement:null,template:'<div class="qq-uploader">'+'<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>'+'<div class="qq-upload-button">Add Photo</div>'+'<ul class="qq-upload-list"></ul>'+"</div>",fileTemplate:"<li>"+'<span class="qq-upload-file"></span>'+'<span class="qq-upload-spinner"></span>'+'<span class="qq-upload-size"></span>'+'<a class="qq-upload-cancel" href="#">Cancel</a>'+'<span class="qq-upload-failed-text">Failed</span>'+"</li>",classes:{button:"qq-upload-button",drop:"qq-upload-drop-area",dropActive:"qq-upload-drop-area-active",list:"qq-upload-list",file:"qq-upload-file",spinner:"qq-upload-spinner",size:"qq-upload-size",cancel:"qq-upload-cancel",success:"qq-upload-success",fail:"qq-upload-fail"}});qq.extend(this._options,a);this._element=this._options.element;this._element.innerHTML=this._options.template;this._listElement=this._options.listElement||this._find(this._element,"list");this._classes=this._options.classes;this._button=this._createUploadButton(this._find(this._element,"button"));this._bindCancelEvent();this._setupDragDrop()};qq.extend(qq.FileUploader.prototype,qq.FileUploaderBasic.prototype);qq.extend(qq.FileUploader.prototype,{_find:function(a,b){var c=qq.getByClass(a,this._options.classes[b])[0];if(!c){throw new Error("element not found "+b)}return c},_setupDragDrop:function(){var a=this,b=this._find(this._element,"drop");var c=new qq.UploadDropZone({element:b,onEnter:function(c){qq.addClass(b,a._classes.dropActive);c.stopPropagation()},onLeave:function(a){a.stopPropagation()},onLeaveNotDescendants:function(c){qq.removeClass(b,a._classes.dropActive)},onDrop:function(c){b.style.display="none";qq.removeClass(b,a._classes.dropActive);a._uploadFileList(c.dataTransfer.files)}});b.style.display="none";qq.attach(document,"dragenter",function(a){if(!c._isValidFileDrag(a))return;b.style.display="block"});qq.attach(document,"dragleave",function(a){if(!c._isValidFileDrag(a))return;var d=document.elementFromPoint(a.clientX,a.clientY);if(!d||d.nodeName=="HTML"){b.style.display="none"}})},_onSubmit:function(a,b){qq.FileUploaderBasic.prototype._onSubmit.apply(this,arguments);this._addToList(a,b)},_onProgress:function(a,b,c,d){qq.FileUploaderBasic.prototype._onProgress.apply(this,arguments);var e=this._getItemByFileId(a);var f=this._find(e,"size");f.style.display="inline";var g;if(c!=d){g=Math.round(c/d*100)+"% from "+this._formatSize(d)}else{g=this._formatSize(d)}qq.setText(f,g)},_onComplete:function(a,b,c){qq.FileUploaderBasic.prototype._onComplete.apply(this,arguments);var d=this._getItemByFileId(a);qq.remove(this._find(d,"cancel"));qq.remove(this._find(d,"spinner"));if(c.success){qq.addClass(d,this._classes.success)}else{qq.addClass(d,this._classes.fail)}},_addToList:function(a,b){var c=qq.toElement(this._options.fileTemplate);c.qqFileId=a;var d=this._find(c,"file");qq.setText(d,this._formatFileName(b));this._find(c,"size").style.display="none";this._listElement.appendChild(c)},_getItemByFileId:function(a){var b=this._listElement.firstChild;while(b){if(b.qqFileId==a)return b;b=b.nextSibling}},_bindCancelEvent:function(){var a=this,b=this._listElement;qq.attach(b,"click",function(b){b=b||window.event;var c=b.target||b.srcElement;if(qq.hasClass(c,a._classes.cancel)){qq.preventDefault(b);var d=c.parentNode;a._handler.cancel(d.qqFileId);qq.remove(d)}})}});qq.UploadDropZone=function(a){this._options={element:null,onEnter:function(a){},onLeave:function(a){},onLeaveNotDescendants:function(a){},onDrop:function(a){}};qq.extend(this._options,a);this._element=this._options.element;this._disableDropOutside();this._attachEvents()};qq.UploadDropZone.prototype={_disableDropOutside:function(a){if(!qq.UploadDropZone.dropOutsideDisabled){qq.attach(document,"dragover",function(a){if(a.dataTransfer){a.dataTransfer.dropEffect="none";a.preventDefault()}});qq.UploadDropZone.dropOutsideDisabled=true}},_attachEvents:function(){var a=this;qq.attach(a._element,"dragover",function(b){if(!a._isValidFileDrag(b))return;var c=b.dataTransfer.effectAllowed;if(c=="move"||c=="linkMove"){b.dataTransfer.dropEffect="move"}else{b.dataTransfer.dropEffect="copy"}b.stopPropagation();b.preventDefault()});qq.attach(a._element,"dragenter",function(b){if(!a._isValidFileDrag(b))return;a._options.onEnter(b)});qq.attach(a._element,"dragleave",function(b){if(!a._isValidFileDrag(b))return;a._options.onLeave(b);var c=document.elementFromPoint(b.clientX,b.clientY);if(qq.contains(this,c))return;a._options.onLeaveNotDescendants(b)});qq.attach(a._element,"drop",function(b){if(!a._isValidFileDrag(b))return;b.preventDefault();a._options.onDrop(b)})},_isValidFileDrag:function(a){var b=a.dataTransfer,c=navigator.userAgent.indexOf("AppleWebKit")>-1;return b&&b.effectAllowed!="none"&&(b.files||!c&&b.types.contains&&b.types.contains("Files"))}};qq.UploadButton=function(a){this._options={element:null,multiple:false,name:"file",onChange:function(a){},hoverClass:"qq-upload-button-hover",focusClass:"qq-upload-button-focus"};qq.extend(this._options,a);this._element=this._options.element;qq.css(this._element,{position:"relative",overflow:"hidden",direction:"ltr"});this._input=this._createInput()};qq.UploadButton.prototype={getInput:function(){return this._input},reset:function(){if(this._input.parentNode){qq.remove(this._input)}qq.removeClass(this._element,this._options.focusClass);this._input=this._createInput()},_createInput:function(){var a=document.createElement("input");if(this._options.multiple){a.setAttribute("multiple","multiple")}a.setAttribute("type","file");a.setAttribute("name",this._options.name);qq.css(a,{position:"absolute",right:0,top:0,fontFamily:"Arial",fontSize:"118px",margin:0,padding:0,cursor:"pointer",opacity:0});this._element.appendChild(a);var b=this;qq.attach(a,"change",function(){b._options.onChange(a)});qq.attach(a,"mouseover",function(){qq.addClass(b._element,b._options.hoverClass)});qq.attach(a,"mouseout",function(){qq.removeClass(b._element,b._options.hoverClass)});qq.attach(a,"focus",function(){qq.addClass(b._element,b._options.focusClass)});qq.attach(a,"blur",function(){qq.removeClass(b._element,b._options.focusClass)});if(window.attachEvent){a.setAttribute("tabIndex","-1")}return a}};qq.UploadHandlerAbstract=function(a){this._options={debug:false,action:"/upload.php",maxConnections:999,onProgress:function(a,b,c,d){},onComplete:function(a,b,c){},onCancel:function(a,b){}};qq.extend(this._options,a);this._queue=[];this._params=[]};qq.UploadHandlerAbstract.prototype={log:function(a){if(this._options.debug&&window.console)console.log("[uploader] "+a)},add:function(a){},upload:function(a,b){var c=this._queue.push(a);var d={};qq.extend(d,b);this._params[a]=d;if(c<=this._options.maxConnections){this._upload(a,this._params[a])}},cancel:function(a){this._cancel(a);this._dequeue(a)},cancelAll:function(){for(var a=0;a<this._queue.length;a++){this._cancel(this._queue[a])}this._queue=[]},getName:function(a){},getSize:function(a){},getQueue:function(){return this._queue},_upload:function(a){},_cancel:function(a){},_dequeue:function(a){var b=qq.indexOf(this._queue,a);this._queue.splice(b,1);var c=this._options.maxConnections;if(this._queue.length>=c&&b<c){var d=this._queue[c-1];this._upload(d,this._params[d])}}};qq.UploadHandlerForm=function(a){qq.UploadHandlerAbstract.apply(this,arguments);this._inputs={}};qq.extend(qq.UploadHandlerForm.prototype,qq.UploadHandlerAbstract.prototype);qq.extend(qq.UploadHandlerForm.prototype,{add:function(a){a.setAttribute("name","qqfile");var b="qq-upload-handler-iframe"+qq.getUniqueId();this._inputs[b]=a;if(a.parentNode){qq.remove(a)}return b},getName:function(a){return this._inputs[a].value.replace(/.*(\/|\\)/,"")},_cancel:function(a){this._options.onCancel(a,this.getName(a));delete this._inputs[a];var b=document.getElementById(a);if(b){b.setAttribute("src","javascript:false;");qq.remove(b)}},_upload:function(a,b){var c=this._inputs[a];if(!c){throw new Error("file with passed id was not added, or already uploaded or cancelled")}var d=this.getName(a);var e=this._createIframe(a);var f=this._createForm(e,b);f.appendChild(c);var g=this;this._attachLoadEvent(e,function(){g.log("iframe loaded");var b=g._getIframeContentJSON(e);g._options.onComplete(a,d,b);g._dequeue(a);delete g._inputs[a];setTimeout(function(){qq.remove(e)},1)});f.submit();qq.remove(f);return a},_attachLoadEvent:function(a,b){qq.attach(a,"load",function(){if(!a.parentNode){return}if(a.contentDocument&&a.contentDocument.body&&a.contentDocument.body.innerHTML=="false"){return}b()})},_getIframeContentJSON:function(iframe){var doc=iframe.contentDocument?iframe.contentDocument:iframe.contentWindow.document,response;this.log("converting iframe's innerHTML to JSON");this.log("innerHTML = "+doc.body.innerHTML);try{response=eval("("+doc.body.innerHTML+")")}catch(err){response={}}return response},_createIframe:function(a){var b=qq.toElement('<iframe src="javascript:false;" name="'+a+'" />');b.setAttribute("id",a);b.style.display="none";document.body.appendChild(b);return b},_createForm:function(a,b){var c=qq.toElement('<form method="post" enctype="multipart/form-data"></form>');var d=qq.obj2url(b,this._options.action);c.setAttribute("action",d);c.setAttribute("target",a.name);c.style.display="none";document.body.appendChild(c);return c}});qq.UploadHandlerXhr=function(a){qq.UploadHandlerAbstract.apply(this,arguments);this._files=[];this._xhrs=[];this._loaded=[]};qq.UploadHandlerXhr.isSupported=function(){return false;};qq.extend(qq.UploadHandlerXhr.prototype,qq.UploadHandlerAbstract.prototype);qq.extend(qq.UploadHandlerXhr.prototype,{add:function(a){if(!(a instanceof File)){throw new Error("Passed obj in not a File (in qq.UploadHandlerXhr)")}return this._files.push(a)-1},getName:function(a){var b=this._files[a];return b.fileName!=null?b.fileName:b.name},getSize:function(a){var b=this._files[a];return b.fileSize!=null?b.fileSize:b.size},getLoaded:function(a){return this._loaded[a]||0},_upload:function(a,b){var c=this._files[a],d=this.getName(a),e=this.getSize(a);this._loaded[a]=0;var f=this._xhrs[a]=new XMLHttpRequest;var g=this;f.upload.onprogress=function(b){if(b.lengthComputable){g._loaded[a]=b.loaded;g._options.onProgress(a,d,b.loaded,b.total)}};f.onreadystatechange=function(){if(f.readyState==4){g._onComplete(a,f)}};b=b||{};b["qqfile"]=d;var h=qq.obj2url(b,this._options.action);f.open("POST",h,true);f.setRequestHeader("X-Requested-With","XMLHttpRequest");f.setRequestHeader("X-File-Name",encodeURIComponent(d));f.setRequestHeader("Content-Type","application/octet-stream");f.send(c)},_onComplete:function(id,xhr){if(!this._files[id])return;var name=this.getName(id);var size=this.getSize(id);this._options.onProgress(id,name,size,size);if(xhr.status==200){this.log("xhr - server response received");this.log("responseText = "+xhr.responseText);var response;try{response=eval("("+xhr.responseText+")")}catch(err){response={}}this._options.onComplete(id,name,response)}else{this._options.onComplete(id,name,{})}this._files[id]=null;this._xhrs[id]=null;this._dequeue(id)},_cancel:function(a){this._options.onCancel(a,this.getName(a));this._files[a]=null;if(this._xhrs[a]){this._xhrs[a].abort();this._xhrs[a]=null}}});

/*
 * FancyBox - jQuery Plugin
 * Simple and fancy lightbox alternative
 *
 * Examples and documentation at: http://fancybox.net
 * 
 * Copyright (c) 2008 - 2010 Janis Skarnelis
 * That said, it is hardly a one-person project. Many people have submitted bugs, code, and offered their advice freely. Their support is greatly appreciated.
 * 
 * Version: 1.3.4 (11/11/2010)
 * Requires: jQuery v1.3+
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

;(function(b){var m,t,u,f,D,j,E,n,z,A,q=0,e={},o=[],p=0,d={},l=[],G=null,v=new Image,J=/\.(jpg|gif|png|bmp|jpeg)(.*)?$/i,W=/[^\.]\.(swf)\s*$/i,K,L=1,y=0,s="",r,i,h=false,B=b.extend(b("<div/>")[0],{prop:0}),M=b.browser.msie&&b.browser.version<7&&!window.XMLHttpRequest,N=function(){t.hide();v.onerror=v.onload=null;G&&G.abort();m.empty()},O=function(){if(false===e.onError(o,q,e)){t.hide();h=false}else{e.titleShow=false;e.width="auto";e.height="auto";m.html('<p id="fancybox-error">The requested content cannot be loaded.<br />Please try again later.</p>');
F()}},I=function(){var a=o[q],c,g,k,C,P,w;N();e=b.extend({},b.fn.fancybox.defaults,typeof b(a).data("fancybox")=="undefined"?e:b(a).data("fancybox"));w=e.onStart(o,q,e);if(w===false)h=false;else{if(typeof w=="object")e=b.extend(e,w);k=e.title||(a.nodeName?b(a).attr("title"):a.title)||"";if(a.nodeName&&!e.orig)e.orig=b(a).children("img:first").length?b(a).children("img:first"):b(a);if(k===""&&e.orig&&e.titleFromAlt)k=e.orig.attr("alt");c=e.href||(a.nodeName?b(a).attr("href"):a.href)||null;if(/^(?:javascript)/i.test(c)||
c=="#")c=null;if(e.type){g=e.type;if(!c)c=e.content}else if(e.content)g="html";else if(c)g=c.match(J)?"image":c.match(W)?"swf":b(a).hasClass("iframe")?"iframe":c.indexOf("#")===0?"inline":"ajax";if(g){if(g=="inline"){a=c.substr(c.indexOf("#"));g=b(a).length>0?"inline":"ajax"}e.type=g;e.href=c;e.title=k;if(e.autoDimensions)if(e.type=="html"||e.type=="inline"||e.type=="ajax"){e.width="auto";e.height="auto"}else e.autoDimensions=false;if(e.modal){e.overlayShow=true;e.hideOnOverlayClick=false;e.hideOnContentClick=
false;e.enableEscapeButton=false;e.showCloseButton=false}e.padding=parseInt(e.padding,10);e.margin=parseInt(e.margin,10);m.css("padding",e.padding+e.margin);b(".fancybox-inline-tmp").unbind("fancybox-cancel").bind("fancybox-change",function(){b(this).replaceWith(j.children())});switch(g){case "html":m.html(e.content);F();break;case "inline":if(b(a).parent().is("#fancybox-content")===true){h=false;break}b('<div class="fancybox-inline-tmp" />').hide().insertBefore(b(a)).bind("fancybox-cleanup",function(){b(this).replaceWith(j.children())}).bind("fancybox-cancel",
function(){b(this).replaceWith(m.children())});b(a).appendTo(m);F();break;case "image":h=false;b.fancybox.showActivity();v=new Image;v.onerror=function(){O()};v.onload=function(){h=true;v.onerror=v.onload=null;e.width=v.width;e.height=v.height;b("<img />").attr({id:"fancybox-img",src:v.src,alt:e.title}).appendTo(m);Q()};v.src=c;break;case "swf":e.scrolling="no";C='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+e.width+'" height="'+e.height+'"><param name="movie" value="'+c+
'"></param>';P="";b.each(e.swf,function(x,H){C+='<param name="'+x+'" value="'+H+'"></param>';P+=" "+x+'="'+H+'"'});C+='<embed src="'+c+'" type="application/x-shockwave-flash" width="'+e.width+'" height="'+e.height+'"'+P+"></embed></object>";m.html(C);F();break;case "ajax":h=false;b.fancybox.showActivity();e.ajax.win=e.ajax.success;G=b.ajax(b.extend({},e.ajax,{url:c,data:e.ajax.data||{},error:function(x){x.status>0&&O()},success:function(x,H,R){if((typeof R=="object"?R:G).status==200){if(typeof e.ajax.win==
"function"){w=e.ajax.win(c,x,H,R);if(w===false){t.hide();return}else if(typeof w=="string"||typeof w=="object")x=w}m.html(x);F()}}}));break;case "iframe":Q()}}else O()}},F=function(){var a=e.width,c=e.height;a=a.toString().indexOf("%")>-1?parseInt((b(window).width()-e.margin*2)*parseFloat(a)/100,10)+"px":a=="auto"?"auto":a+"px";c=c.toString().indexOf("%")>-1?parseInt((b(window).height()-e.margin*2)*parseFloat(c)/100,10)+"px":c=="auto"?"auto":c+"px";m.wrapInner('<div style="width:'+a+";height:"+c+
";overflow: "+(e.scrolling=="auto"?"auto":e.scrolling=="yes"?"scroll":"hidden")+';position:relative;"></div>');e.width=m.width();e.height=m.height();Q()},Q=function(){var a,c;t.hide();if(f.is(":visible")&&false===d.onCleanup(l,p,d)){b.event.trigger("fancybox-cancel");h=false}else{h=true;b(j.add(u)).unbind();b(window).unbind("resize.fb scroll.fb");b(document).unbind("keydown.fb");f.is(":visible")&&d.titlePosition!=="outside"&&f.css("height",f.height());l=o;p=q;d=e;if(d.overlayShow){u.css({"background-color":d.overlayColor,
opacity:d.overlayOpacity,cursor:d.hideOnOverlayClick?"pointer":"auto",height:b(document).height()});if(!u.is(":visible")){M&&b("select:not(#fancybox-tmp select)").filter(function(){return this.style.visibility!=="hidden"}).css({visibility:"hidden"}).one("fancybox-cleanup",function(){this.style.visibility="inherit"});u.show()}}else u.hide();i=X();s=d.title||"";y=0;n.empty().removeAttr("style").removeClass();if(d.titleShow!==false){if(b.isFunction(d.titleFormat))a=d.titleFormat(s,l,p,d);else a=s&&s.length?
d.titlePosition=="float"?'<table id="fancybox-title-float-wrap" cellpadding="0" cellspacing="0"><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">'+s+'</td><td id="fancybox-title-float-right"></td></tr></table>':'<div id="fancybox-title-'+d.titlePosition+'">'+s+"</div>":false;s=a;if(!(!s||s==="")){n.addClass("fancybox-title-"+d.titlePosition).html(s).appendTo("body").show();switch(d.titlePosition){case "inside":n.css({width:i.width-d.padding*2,marginLeft:d.padding,marginRight:d.padding});
y=n.outerHeight(true);n.appendTo(D);i.height+=y;break;case "over":n.css({marginLeft:d.padding,width:i.width-d.padding*2,bottom:d.padding}).appendTo(D);break;case "float":n.css("left",parseInt((n.width()-i.width-40)/2,10)*-1).appendTo(f);break;default:n.css({width:i.width-d.padding*2,paddingLeft:d.padding,paddingRight:d.padding}).appendTo(f)}}}n.hide();if(f.is(":visible")){b(E.add(z).add(A)).hide();a=f.position();r={top:a.top,left:a.left,width:f.width(),height:f.height()};c=r.width==i.width&&r.height==
i.height;j.fadeTo(d.changeFade,0.3,function(){var g=function(){j.html(m.contents()).fadeTo(d.changeFade,1,S)};b.event.trigger("fancybox-change");j.empty().removeAttr("filter").css({"border-width":d.padding,width:i.width-d.padding*2,height:e.autoDimensions?"auto":i.height-y-d.padding*2});if(c)g();else{B.prop=0;b(B).animate({prop:1},{duration:d.changeSpeed,easing:d.easingChange,step:T,complete:g})}})}else{f.removeAttr("style");j.css("border-width",d.padding);if(d.transitionIn=="elastic"){r=V();j.html(m.contents());
f.show();if(d.opacity)i.opacity=0;B.prop=0;b(B).animate({prop:1},{duration:d.speedIn,easing:d.easingIn,step:T,complete:S})}else{d.titlePosition=="inside"&&y>0&&n.show();j.css({width:i.width-d.padding*2,height:e.autoDimensions?"auto":i.height-y-d.padding*2}).html(m.contents());f.css(i).fadeIn(d.transitionIn=="none"?0:d.speedIn,S)}}}},Y=function(){if(d.enableEscapeButton||d.enableKeyboardNav)b(document).bind("keydown.fb",function(a){if(a.keyCode==27&&d.enableEscapeButton){a.preventDefault();b.fancybox.close()}else if((a.keyCode==
37||a.keyCode==39)&&d.enableKeyboardNav&&a.target.tagName!=="INPUT"&&a.target.tagName!=="TEXTAREA"&&a.target.tagName!=="SELECT"){a.preventDefault();b.fancybox[a.keyCode==37?"prev":"next"]()}});if(d.showNavArrows){if(d.cyclic&&l.length>1||p!==0)z.show();if(d.cyclic&&l.length>1||p!=l.length-1)A.show()}else{z.hide();A.hide()}},S=function(){if(!b.support.opacity){j.get(0).style.removeAttribute("filter");f.get(0).style.removeAttribute("filter")}e.autoDimensions&&j.css("height","auto");f.css("height","auto");
s&&s.length&&n.show();d.showCloseButton&&E.show();Y();d.hideOnContentClick&&j.bind("click",b.fancybox.close);d.hideOnOverlayClick&&u.bind("click",b.fancybox.close);b(window).bind("resize.fb",b.fancybox.resize);d.centerOnScroll&&b(window).bind("scroll.fb",b.fancybox.center);if(d.type=="iframe")b('<iframe id="fancybox-frame" name="fancybox-frame'+(new Date).getTime()+'" frameborder="0" hspace="0" '+(b.browser.msie?'allowtransparency="true""':"")+' scrolling="'+e.scrolling+'" src="'+d.href+'"></iframe>').appendTo(j);
f.show();h=false;b.fancybox.center();d.onComplete(l,p,d);var a,c;if(l.length-1>p){a=l[p+1].href;if(typeof a!=="undefined"&&a.match(J)){c=new Image;c.src=a}}if(p>0){a=l[p-1].href;if(typeof a!=="undefined"&&a.match(J)){c=new Image;c.src=a}}},T=function(a){var c={width:parseInt(r.width+(i.width-r.width)*a,10),height:parseInt(r.height+(i.height-r.height)*a,10),top:parseInt(r.top+(i.top-r.top)*a,10),left:parseInt(r.left+(i.left-r.left)*a,10)};if(typeof i.opacity!=="undefined")c.opacity=a<0.3?0.3:a;f.css(c);
j.css({width:c.width-d.padding*2,height:c.height-y*a-d.padding*2})},U=function(){return[b(window).width()-d.margin*2,b(window).height()-d.margin*2,b(document).scrollLeft()+d.margin,b(document).scrollTop()+d.margin]},X=function(){var a=U(),c={},g=d.autoScale,k=d.padding*2;c.width=d.width.toString().indexOf("%")>-1?parseInt(a[0]*parseFloat(d.width)/100,10):d.width+k;c.height=d.height.toString().indexOf("%")>-1?parseInt(a[1]*parseFloat(d.height)/100,10):d.height+k;if(g&&(c.width>a[0]||c.height>a[1]))if(e.type==
"image"||e.type=="swf"){g=d.width/d.height;if(c.width>a[0]){c.width=a[0];c.height=parseInt((c.width-k)/g+k,10)}if(c.height>a[1]){c.height=a[1];c.width=parseInt((c.height-k)*g+k,10)}}else{c.width=Math.min(c.width,a[0]);c.height=Math.min(c.height,a[1])}c.top=parseInt(Math.max(a[3]-20,a[3]+(a[1]-c.height-40)*0.5),10);c.left=parseInt(Math.max(a[2]-20,a[2]+(a[0]-c.width-40)*0.5),10);return c},V=function(){var a=e.orig?b(e.orig):false,c={};if(a&&a.length){c=a.offset();c.top+=parseInt(a.css("paddingTop"),
10)||0;c.left+=parseInt(a.css("paddingLeft"),10)||0;c.top+=parseInt(a.css("border-top-width"),10)||0;c.left+=parseInt(a.css("border-left-width"),10)||0;c.width=a.width();c.height=a.height();c={width:c.width+d.padding*2,height:c.height+d.padding*2,top:c.top-d.padding-20,left:c.left-d.padding-20}}else{a=U();c={width:d.padding*2,height:d.padding*2,top:parseInt(a[3]+a[1]*0.5,10),left:parseInt(a[2]+a[0]*0.5,10)}}return c},Z=function(){if(t.is(":visible")){b("div",t).css("top",L*-40+"px");L=(L+1)%12}else clearInterval(K)};
b.fn.fancybox=function(a){if(!b(this).length)return this;b(this).data("fancybox",b.extend({},a,b.metadata?b(this).metadata():{})).unbind("click.fb").bind("click.fb",function(c){c.preventDefault();if(!h){h=true;b(this).blur();o=[];q=0;c=b(this).attr("rel")||"";if(!c||c==""||c==="nofollow")o.push(this);else{o=b("a[rel="+c+"], area[rel="+c+"]");q=o.index(this)}I()}});return this};b.fancybox=function(a,c){var g;if(!h){h=true;g=typeof c!=="undefined"?c:{};o=[];q=parseInt(g.index,10)||0;if(b.isArray(a)){for(var k=
0,C=a.length;k<C;k++)if(typeof a[k]=="object")b(a[k]).data("fancybox",b.extend({},g,a[k]));else a[k]=b({}).data("fancybox",b.extend({content:a[k]},g));o=jQuery.merge(o,a)}else{if(typeof a=="object")b(a).data("fancybox",b.extend({},g,a));else a=b({}).data("fancybox",b.extend({content:a},g));o.push(a)}if(q>o.length||q<0)q=0;I()}};b.fancybox.showActivity=function(){clearInterval(K);t.show();K=setInterval(Z,66)};b.fancybox.hideActivity=function(){t.hide()};b.fancybox.next=function(){return b.fancybox.pos(p+
1)};b.fancybox.prev=function(){return b.fancybox.pos(p-1)};b.fancybox.pos=function(a){if(!h){a=parseInt(a);o=l;if(a>-1&&a<l.length){q=a;I()}else if(d.cyclic&&l.length>1){q=a>=l.length?0:l.length-1;I()}}};b.fancybox.cancel=function(){if(!h){h=true;b.event.trigger("fancybox-cancel");N();e.onCancel(o,q,e);h=false}};b.fancybox.close=function(){function a(){u.fadeOut("fast");n.empty().hide();f.hide();b.event.trigger("fancybox-cleanup");j.empty();d.onClosed(l,p,d);l=e=[];p=q=0;d=e={};h=false}if(!(h||f.is(":hidden"))){h=
true;if(d&&false===d.onCleanup(l,p,d))h=false;else{N();b(E.add(z).add(A)).hide();b(j.add(u)).unbind();b(window).unbind("resize.fb scroll.fb");b(document).unbind("keydown.fb");j.find("iframe").attr("src",M&&/^https/i.test(window.location.href||"")?"javascript:void(false)":"about:blank");d.titlePosition!=="inside"&&n.empty();f.stop();if(d.transitionOut=="elastic"){r=V();var c=f.position();i={top:c.top,left:c.left,width:f.width(),height:f.height()};if(d.opacity)i.opacity=1;n.empty().hide();B.prop=1;
b(B).animate({prop:0},{duration:d.speedOut,easing:d.easingOut,step:T,complete:a})}else f.fadeOut(d.transitionOut=="none"?0:d.speedOut,a)}}};b.fancybox.resize=function(){u.is(":visible")&&u.css("height",b(document).height());b.fancybox.center(true)};b.fancybox.center=function(a){var c,g;if(!h){g=a===true?1:0;c=U();!g&&(f.width()>c[0]||f.height()>c[1])||f.stop().animate({top:parseInt(Math.max(c[3]-20,c[3]+(c[1]-j.height()-40)*0.5-d.padding)),left:parseInt(Math.max(c[2]-20,c[2]+(c[0]-j.width()-40)*0.5-
d.padding))},typeof a=="number"?a:200)}};b.fancybox.init=function(){if(!b("#fancybox-wrap").length){b("body").append(m=b('<div id="fancybox-tmp"></div>'),t=b('<div id="fancybox-loading"><div></div></div>'),u=b('<div id="fancybox-overlay"></div>'),f=b('<div id="fancybox-wrap"></div>'));D=b('<div id="fancybox-outer"></div>').appendTo(f);
D.append(j=b('<div id="fancybox-content"></div>'),E=b('<a id="fancybox-close"></a>'),n=b('<div id="fancybox-title"></div>'),z=b('<a href="javascript:;" id="fancybox-left"><span class="fancy-ico" id="fancybox-left-ico"></span></a>'),A=b('<a href="javascript:;" id="fancybox-right"><span class="fancy-ico" id="fancybox-right-ico"></span></a>'));E.click(b.fancybox.close);t.click(b.fancybox.cancel);z.click(function(a){a.preventDefault();b.fancybox.prev()});A.click(function(a){a.preventDefault();b.fancybox.next()});
b.fn.mousewheel&&f.bind("mousewheel.fb",function(a,c){if(h)a.preventDefault();else if(b(a.target).get(0).clientHeight==0||b(a.target).get(0).scrollHeight===b(a.target).get(0).clientHeight){a.preventDefault();b.fancybox[c>0?"prev":"next"]()}});b.support.opacity||f.addClass("fancybox-ie");if(M){t.addClass("fancybox-ie6");f.addClass("fancybox-ie6");b('<iframe id="fancybox-hide-sel-frame" src="'+(/^https/i.test(window.location.href||"")?"javascript:void(false)":"about:blank")+'" scrolling="no" border="0" frameborder="0" tabindex="-1"></iframe>').prependTo(D)}}};
b.fn.fancybox.defaults={padding:10,margin:40,opacity:false,modal:false,cyclic:false,scrolling:"auto",width:560,height:340,autoScale:true,autoDimensions:true,centerOnScroll:false,ajax:{},swf:{wmode:"transparent"},hideOnOverlayClick:true,hideOnContentClick:false,overlayShow:true,overlayOpacity:0.3,overlayColor:"#777",titleShow:false,titlePosition:"float",titleFormat:null,titleFromAlt:false,transitionIn:"fade",transitionOut:"fade",speedIn:200,speedOut:200,changeSpeed:200,changeFade:"fast",easingIn:"swing",
easingOut:"swing",showCloseButton:true,showNavArrows:true,enableEscapeButton:true,enableKeyboardNav:true,onStart:function(){},onCancel:function(){},onComplete:function(){},onCleanup:function(){},onClosed:function(){},onError:function(){}};b(document).ready(function(){b.fancybox.init()})})(jQuery);
	

/*
 * Superfish v1.4.8 - jQuery menu widget
 * Copyright (c) 2008 Joel Birch
 *
 * Dual licensed under the MIT and GPL licenses:
 * 	http://www.opensource.org/licenses/mit-license.php
 * 	http://www.gnu.org/licenses/gpl.html
 *
 * CHANGELOG: http://users.tpg.com.au/j_birch/plugins/superfish/changelog.txt
 */

;(function(jQuery){
jQuery.fn.superfish = function(op){

var sf = jQuery.fn.superfish,
c = sf.c,
$arrow = jQuery(['<span class="',c.arrowClass,'"> &#187;</span>'].join('')),
over = function(){
var $$ = jQuery(this), menu = getMenu($$);
clearTimeout(menu.sfTimer);
$$.showSuperfishUl().siblings().hideSuperfishUl();
},
out = function(){
var $$ = jQuery(this), menu = getMenu($$), o = sf.op;
clearTimeout(menu.sfTimer);
menu.sfTimer=setTimeout(function(){
o.retainPath=(jQuery.inArray($$[0],o.$path)>-1);
$$.hideSuperfishUl();
if (o.$path.length && $$.parents(['li.',o.hoverClass].join('')).length<1){over.call(o.$path);}
},o.delay);
},
getMenu = function($menu){
var menu = $menu.parents(['ul.',c.menuClass,':first'].join(''))[0];
sf.op = sf.o[menu.serial];
return menu;
},
addArrow = function($a){ $a.addClass(c.anchorClass).append($arrow.clone()); };

return this.each(function() {
var s = this.serial = sf.o.length;
var o = jQuery.extend({},sf.defaults,op);
o.$path = jQuery('li.'+o.pathClass,this).slice(0,o.pathLevels).each(function(){
jQuery(this).addClass([o.hoverClass,c.bcClass].join(' '))
.filter('li:has(ul)').removeClass(o.pathClass);
});
sf.o[s] = sf.op = o;

jQuery('li:has(ul)',this)[(jQuery.fn.hoverIntent && !o.disableHI) ? 'hoverIntent' : 'hover'](over,out).each(function() {
if (o.autoArrows) addArrow( jQuery('>a:first-child',this) );
})
.not('.'+c.bcClass)
.hideSuperfishUl();

var $a = jQuery('a',this);
$a.each(function(i){
var $li = $a.eq(i).parents('li');
$a.eq(i).focus(function(){over.call($li);}).blur(function(){out.call($li);});
});
o.onInit.call(this);

}).each(function() {
var menuClasses = [c.menuClass];
if (sf.op.dropShadows && !(jQuery.browser.msie && jQuery.browser.version < 7)) menuClasses.push(c.shadowClass);
jQuery(this).addClass(menuClasses.join(' '));
});
};

var sf = jQuery.fn.superfish;
sf.o = [];
sf.op = {};
sf.IE7fix = function(){
var o = sf.op;
if (jQuery.browser.msie && jQuery.browser.version > 6 && o.dropShadows && o.animation.opacity!=undefined)
this.toggleClass(sf.c.shadowClass+'-off');
};
sf.c = {
bcClass : 'sf-breadcrumb',
menuClass : 'sf-js-enabled',
anchorClass : 'sf-with-ul',
arrowClass : 'sf-sub-indicator',
shadowClass : 'sf-shadow'
};
sf.defaults = {
hoverClass : 'sfHover',
pathClass : 'overideThisToUse',
pathLevels : 1,
delay : 800,
animation : {opacity:'show'},
speed : 'normal',
autoArrows : true,
dropShadows : true,
disableHI : false, // true disables hoverIntent detection
onInit : function(){}, // callback functions
onBeforeShow: function(){},
onShow : function(){},
onHide : function(){}
};
jQuery.fn.extend({
hideSuperfishUl : function(){
var o = sf.op,
not = (o.retainPath===true) ? o.$path : '';
o.retainPath = false;
var $ul = jQuery(['li.',o.hoverClass].join(''),this).add(this).not(not).removeClass(o.hoverClass)
.find('>ul').hide().css('visibility','hidden');
o.onHide.call($ul);
return this;
},
showSuperfishUl : function(){
var o = sf.op,
sh = sf.c.shadowClass+'-off',
$ul = this.addClass(o.hoverClass)
.find('>ul:hidden').css('visibility','visible');
sf.IE7fix.call($ul);
o.onBeforeShow.call($ul);
$ul.animate(o.animation,o.speed,function(){ sf.IE7fix.call($ul); o.onShow.call($ul); });
return this;
}
});

})(jQuery);
	