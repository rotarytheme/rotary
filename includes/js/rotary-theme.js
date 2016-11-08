
jQuery(document).ready(function($) {
	var rotaryTheme = {
		init: function() {
			
			//add extra class so we know if there are child items
			$('ul.sub-menu').parent().addClass('dropdown');
			//add class to upcoming programs edit so we can target it
			$('#home-upcoming-programs .post-edit-link').addClass('speakerdatedit');
			$('.single-rotary_projects .meta .post-edit-link').removeClass('rotarybutton-largewhite');
			this.setUpArchives();
			this.setUpSlideShow();
			this.setUpAnnouncements();
			this.setUpAnnouncementsSlideshow();
			this.setUpEdits();
			this.setUpDatePicker();
			this.setUpDatatables();
			//this.setUpTabs();
			this.checkOpen();
			this.setupMaps();
			this.layoutProjects();
			
			$('#committeewidget, #projectwidget').on('change', this.showCommittee);
			$('#committeeselect').on('change', this.newAnnouncement);
			$('#morecomments').on('click', this.showMoreComments);
			$('#lesscomments').on('click', this.hideMoreComments);
			$('#showregistrationform, #cancelregistrationform').on('click', this.toggleForm);
			$('#newcomment, #newcommentproject').on('click', this.showCommentForm);
			$('#speakertabs').on('click', '.prevnext a', this.loadPrevNext);
			$('#wpas-reset input').on('click', this.resetForm);
			$('#search-toggle').on('click', this.toggleSearch);
			$('#speaker-archive-table tbody').on('mouseenter mouseleave', 'tr', this.hoverRow);
			$('#speaker-archive-table tbody').on('click', 'tr', this.selectRow);
			$('.projecticons').on('mouseenter mouseleave', '.icon', this.hoverIcons);
			$('.logged-in .projecticons').on('click', '.imgoing', this.toggleParticpant);
			// Announcement Feature
			$('.editannouncementbutton').on('click', this.editAnnouncement );
			$('.deleteannouncementbutton').on('click', this.deleteAnnouncement );
			$('select.hyperlink').on('change', this.selectDropdown );
			$( '#accordion-section-static_front_page').on( 'click', this.showpageonfront );
			$('.fancybox, .gallery-item a').fancybox({
				padding: 3,
				nextEffect: 'fade',
				prevEffect: 'fade',
				nextSpeed: 500,
				prevSpeed: 500
			});
		},
		
		selectDropdown: function() {
			window.location.href = (this.options[this.selectedIndex].value);
		},
		layoutProjects: function() {
			$('#projectblogrollcontainer').masonry({
				itemSelector: '.connectedprojectscontainer',
				gutter: 20
			});
		},
		setupMaps: function() {
			$('.acf-map').each(function() {
				rotaryTheme.renderMap($(this));
			});
		},

		renderMap: function($el) {
			var $markers = $el.find('.marker');
			var mapZoom = 16;
			if ( $el.hasClass( 'longterm') ) {
				mapZoom = 8;
			}
			var args = {
				zoom: mapZoom,
				center: new google.maps.LatLng($markers.attr('data-lat'), $markers.attr('data-lng')),
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			// create map
			var map = new google.maps.Map($el[0], args);
			// add a markers reference
			map.markers = [];
			// add markers
			$markers.each(function() {
				rotaryTheme.add_marker($(this), map);
			});
			// center map
			//rotaryTheme.center_map(map);
		},
		add_marker: function($marker, map) {
			var latlng = new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng'));
			// create marker
			var marker = new google.maps.Marker({
				position: latlng,
				map: map
			});
			// add to array
			map.markers.push(marker);
			// create info window
/*var infowindow = new google.maps.InfoWindow({
					content		: '<a href="http://maps.google.com/maps?daddr={'+$marker.attr('data-address')+'}" target="_blank">Meeting Directions</a>'
				});			
				// show info window when marker is clicked
				google.maps.event.addListener(marker, 'click', function() { 
					infowindow.open( map, marker );
				});*/
		},
		center_map: function(map) {
			var bounds = new google.maps.LatLngBounds();
			// loop through all markers and create bounds
			$.each(map.markers, function(i, marker) {
				var latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());
				bounds.extend(latlng);
			});
			// only 1 marker?
			if (map.markers.length == 1) {
				// set center of map
				map.setCenter(bounds.getCenter());
				map.setZoom(16);
			} else {
				// fit to bounds
				map.fitBounds(bounds);
			}
		},
		getUrlParameter: function(sParam) {
			var sPageURL = window.location.search.substring(1);
			var sURLVariables = sPageURL.split('&');
			for (var i = 0; i < sURLVariables.length; i++) {
				var sParameterName = sURLVariables[i].split('=');
				if (sParameterName[0] == sParam) {
					return sParameterName[1];
				}
			}
		},
		checkOpen: function() {
			if ($('.single-rotary-committees').length) {
				var open = this.getUrlParameter('open');
				if ('open' === open) {
					$('#respond').toggle();
					$('#comment').focus();
				}
			}
		},
		showCommittee: function() {
			var committee = $(this).val();
			if (committee.length > 0) {
				window.location.href = committee;
				//window.open( committee, '_announcement' );
			}
		},
		showMoreComments: function(e) {
			e.preventDefault();
			$('article.committee-announcement,article.project-announcement').show();
			$(this).hide();
			$('#lesscomments').show();
		},
		hideMoreComments: function(e) {
			e.preventDefault();
			$('article.committee-announcement:not(:first),article.project-announcement:not(:first)').hide();
			$(this).hide();
			$('#morecomments').show();
		},
		showCommentForm: function(e) {
			e.preventDefault();
			$('#respond').toggle();
			$("#announcement_title_input").focus();
			//window.location.href = '#respond';
    		rotaryTheme.initAnnouncement();
		},
		editAnnouncement: function(e) {
			var comment_id = $(this).data('comment-id');
			var redirect = window.location.href;
			var endurl = redirect.indexOf('#');
			if ( 0 < endurl ) {
				redirect = redirect.substring(0, endurl);
			}
			if ( 'null' != comment_id) {
				$('#ajax-loader').show();
				$('.editannouncementbutton').css("visibility","hidden");
				$('#committeeselect, #announcements-mailchimpcampaign').css("display", "none");
				$.ajax ( {url: rotaryparticipants.ajaxURL
			    	,data: { action: 'edit_announcement'
			    			,comment_id: comment_id 
			    			,redirect_to: redirect
			    			}
				    ,dataType: 'html'
			    	,success: function( html ) {
			    		$('#comment-' + comment_id ).html( html );
			    		$("#ajax-edit-announcement-form").attr("action", rotaryparticipants.templateURL + '/includes/ajax/save-announcement.php'); // hack the default submission action if this is editing
			    		rotaryTheme.initAnnouncement();
			    		rotaryTheme.setUpDatePicker();
			    		tinymce.execCommand('mceRemoveEditor',true,"comment");
			    		tinymce.execCommand('mceAddEditor',true,"comment");
			    		tinymce.init({ selector: "comment" });
			    		$('#ajax-loader').hide();
			    		}
					});
				}
		},
		deleteAnnouncement: function(e) { 
			var comment_id = $(this).data('comment-id');
			var redirect = window.location.href;
			var endurl = redirect.indexOf('#');
			if ( 0 < endurl ) {
				redirect = redirect.substring(0, endurl);
			}
			if ( 'null' != comment_id) {
				$('#ajax-loader').show();
				$.ajax ( {url: rotaryparticipants.ajaxURL
			    	,data: { action: 'delete_announcement'
			    			,comment_id: comment_id 
			    			,redirect_to: redirect
			    			}
				    ,dataType: 'json'
			    	,success: function( data ) {
			    		$('#ajax-loader').hide();
			    		if( data.error ) { alert( data.error ); }
			    		else {
			    			$( '#comment-' + comment_id ).remove();
			    		}
			    		}
					});
				}
		},
		initAnnouncement: function(e) {
			if ( $('#call_to_action').checked ) {	$( '#call_to_action_links' ).show(); }
			$('#announcement_expiry_date_input').datepicker({
				    altField: "#announcement_expiry_date",
				    altFormat: "yy-mm-dd"
			});
			$('#call_to_action').click (function () {
				if (this.checked) {
					$( '#call_to_action_links' ).show();
				} else {
					$( '#call_to_action_links' ).hide();
				}
			});
			
			$( 'input[name=call_to_action_link]:radio' ).change(function() {
				if ($( '#call_to_action_link_2').prop("checked")) {
					$( '#other_link_text' ).show();
				} else {
					$( '#other_link_text' ).hide();
				}
			});
    		$('#announcement_title_input').focus();
		},
		newAnnouncement: function(e) {
			e.preventDefault();   
			var post_id = $(this).val();
			var redirect = window.location.href;
			var endurl = redirect.indexOf('#');
			if ( 0 < endurl ) {
				redirect = redirect.substring(0, endurl);
			}
			if (post_id.length > 0) {
				$('#ajax-loader').show();
				$('.editannouncementbutton').css("visibility","hidden");
				$('#committeeselect, #announcements-mailchimpcampaign').css("display", "none");
				$.ajax ( {url: rotaryparticipants.ajaxURL
			    	,data: { action: 'new_announcement'
			    			,post_id: post_id 
			    			,redirect_to: redirect
			    			}
				    ,dataType: 'html'
			    	,success: function( html ) {
			    		$( '#new_announcement_div' ).html( html );
			    		rotaryTheme.initAnnouncement();
			    		rotaryTheme.setUpDatePicker();
			    		tinymce.execCommand('mceRemoveEditor',true,"comment");
			    		tinymce.execCommand('mceAddEditor',true,"comment");
			    	    tinyMCE.init({selector: "comment"});
			    		$('#ajax-loader').hide();
			    	   // try { quicktags( tinyMCEPreInit.qtInit['qt_comment_toolbar'] ); } catch(e){}
			    		}
				});
			}
		},
		toggleForm: function(e) {
			e.preventDefault();
			$('#gravityform, #showregistrationform, #cancelregistrationform, #rotaryform_wrapper').toggle();
		},
		toggleSearch: function(e) {
			e.preventDefault();
			$(this).toggleClass('collapsed');
			$('#wp-advanced-search').toggle();
		},
		resetForm: function() {
			var form = $(this).closest('form');
			//	$('#wp-advanced-search')[0].reset();
			//	alert($('#wp-advanced-search')[0]);
			$(':input', form).each(function() {
				var type = this.type;
				var tag = this.tagName.toLowerCase(); // normalize case
				// it's ok to reset the value attr of text inputs,
				// password inputs, and textareas
				if (type === 'text' || type === 'password' || tag === 'textarea' || type === 'tel' || type === 'email') {
					this.value = "";
				}
				// checkboxes and radios need to have their checked state cleared
				// but should *not* have their 'value' changed
				else if (type === 'checkbox' || type === 'radio') {
					this.checked = false;
				}
				// select elements need to have their 'selectedIndex' property set to -1
				// (this works for both single and multiple select elements)
				else if (tag === 'select') {
					this.selectedIndex = 0;
				}
			});
			form.submit();
		},
		hoverRow: function() {
			$(this).toggleClass('selected');
		},
		hoverIcons: function() {
			$(this).prev('.hovertext').toggleClass('hide');
		},
		toggleParticpant: function() {
			var $el = $(this);
			var goingText = "I'm not going<br />Click to<br />change RSVP";
			var participate = ''; 
			var $rotaryTables = $('#rotaryprojects').dataTable();
			if ($el.hasClass( 'going' )) {
				participate = 'going';
			}
			$.ajax({
				url: rotaryparticipants.ajaxURL,
				type: 'get',
				data: {action: 'toggleparticipants', nonce: rotaryparticipants.rotaryNonce, participate: participate, postid: $el.data('postid')},
				dataType : 'json',
				success: function(response, textStatus, jqXHR) {
				if (200 == jqXHR.status && 'success' == textStatus) {
					if ('success' == response.status) {
						if ('yes' === response.message) {
							goingText = "I'm going<br />Click to<br />change RSVP";
						}
						if ( $rotaryTables.length ) {
							$rotaryTables.fnReloadAjax();
						}
						
						$el.toggleClass('going').toggleClass('notgoing');
						var $prevel = $el.prev('.imgoingtext');
						$prevel.html(goingText);
						$prevel.toggleClass('going').toggleClass('notgoing');
					}	
				}
			}	
			
			});
		},
		selectRow: function() {
			var curLink = $(this).find('.speakerlink a');
			window.location.href = curLink.attr('href');
		},
		setUpDatatables: function() {
			$('#speaker-archive-table').dataTable({
				'iDisplayLength': 50,
				'aoColumnDefs': [{
					'sClass': 'hide speakerlink',
					'aTargets': [0]
				}, {
					'sClass': 'speakerDate',
					'aTargets': [1]
				}, {
					'sClass': 'speakerTitle',
					'aTargets': [2]
				}]
			});
		},
		setUpTabs: function() {
			if ($('#speakertabs').length) {
				$('#speakertabs').tabs({
					beforeLoad: function(event, ui) {
						ui.panel.html('<p>Loading...</p>');
						ui.jqXHR.error(function() {
							ui.panel.html("Couldn't load this tab. We'll try to fix this as soon as possible. ");
						});
					}
				});
			}
		},
		setUpSlideShow: function() {
			if ($('#slideshow').length) {
				$('#slideshow').cycle({
					slideExpr: '.slide',
					fx: 'scrollHorz',
					height: '313px',
					speed: '1000',
					timeout: 10000,
					delay: -2000,
					pager: '#navsection'
				});
				$('#playpause').click(function(e) {
					e.preventDefault();
					$('#slideshow').cycle('toggle');
					$('#playpause').toggleClass('pause');
					$('#playpause').toggleClass('play');
				});
				$('#slideshow').touchwipe({
					wipeLeft: function() {
						$('#slideshow').cycle('next');
					},
					wipeRight: function() {
						$('#slideshow').cycle('prev');
					}
				});
			}
		},
		loadPrevNext: function(e) {
			e.preventDefault();
			$('#ui-tabs-1').load(this.href);
		},
		setUpAnnouncements: function() {
			if ($('#announcements-carousel').length) {
				$('#announcements-carousel').cycle({
					slideExpr: '.carousel-announcement',
					fx: 'scrollHorz',
					height: '313px',
					speed: '1000',
					timeout: 10000,
					delay: -2000,
					pager: '#announcement-carousel-controls'
				});
			}
		},
		setUpAnnouncementsSlideshow: function() {
			if ($('#announcements-slideshow').length) {
				$('#announcements-slideshow').cycle({
					slideExpr: '.slideshow-announcement',
					fx: 'fade',
					height: '600px',
					speed: '1000',
					timeout: 5500,
					delay: -500,
					//pager: '#announcement-slideshow-controls'
				});
			}
		},
		setUpArchives: function() {
			//see if we are on the archive page and then open archive menu
			$('.monthlist li a').removeClass('current');
			$('li.rotary-adv-archive-year a.icon').click(function(e) {
				e.preventDefault();
				if ($(this).hasClass('more')) {
					$(this).removeClass('more');
					$(this).addClass('less');
				} else {
					$(this).removeClass('less');
					$(this).addClass('more');
				}
				$(this).parent().children('ul').slideToggle('fast');
			});
			if ($('.archive .pagetitle').length) {
				var titleText = $('.archive .pagetitle').text();
				var afterColon = $.trim(titleText.substr(titleText.indexOf(":") + 1));
				if (afterColon == parseInt(afterColon, 10)) {
					var $year = $('.rotary-adv-archive-year .icon:contains("' + afterColon + '")');
					//console.log($year.parent('li').text());
					//$year.parent('li').children(' .monthlist li a:contains("All Months")').addClass('current');
					$year.next('.monthlist').children('li:first').children('a').addClass('current');
					$year.click();
				} else {
					var $archiveLink = $('.monthlist li a:contains("' + afterColon + '")');
					$archiveLink.addClass('current').closest('.rotary-adv-archive-year').children('.icon').click();
				}
			}
		},
		setUpDatePicker: function() {
			if (!Modernizr.inputtypes.date) {
				if ($('.page-template-tmpl-speaker-archive-php input[type="date"]').length) {
					$('.page-template-tmpl-speaker-archive-php input[type=date]').datepicker();
				}
			}
			$( '#announcement_expiry_date_input').datepicker({
				minDate: "+1d",
				maxDate: "+2m",
				defaultDate: "+7d",
				altField: "#announcement_expiry_date",
				altFormat: "yy-mm-dd",
				format: "m/d/yy",
				autoSize: true
			});
		},
		setUpEdits: function() {
			//if the user is an admin, he/she can edit widgets so we will allow direct access from font-end
			if ($('.widgetedit').length) {
				var $sidebar;
				if ($('body').hasClass('home')) {
					$sidebar = $('.home aside > ul > li h3');
				} else {
					$sidebar = $('#secondary > ul > li h3');
				}
				$($sidebar).hoverIntent(

				function() {
					var position = $(this).position();
					$(this).append($('.widgetedit').css('left', position.left + 'px').show());
				}, function() {
					$('.widgetedit').hide();
				});
			}
			//if the user is an admin, he/she can edit theme customizations from the front-end	
			if ($('.headeredit').length) {
				$('#branding h1, #branding #meetingaddress').hoverIntent({
				sensitivity: 1, // number = sensitivity threshold (must be 1 or higher)    
				interval: 10,  // number = milliseconds for onMouseOver polling interval    
				timeout: 1200,   // number = milliseconds delay before onMouseOut 
				over: function() {
					var position = $(this).position();
					var editLeft = position.left;
					if ($(this).text() === $('#branding h1').text()) {
						editLeft += 300;
					}
					$('.headeredit').css('left', editLeft + 'px').show();
				}, 
				out: function(event) {
					//this is the original element the event handler was assigned to
					event.stopPropagation();
					event.preventDefault();
					$('.headeredit').css('left', '70%').hide();
				}	
				});
			}
			if ($('.speakerdatedit').length) {
				$('.home-upcoming-programs-speaker-date').hoverIntent(

				function() {
					var position = $(this).position();
					var editLeft = position.left;
					//$(this).append($('.speakerdatedit').css('left', editLeft + 'px').show());
					$(this).find('.speakerdatedit').show();
				}, function() {
					$(this).find('.speakerdatedit').hide();
				});
			}
		}
	};
	rotaryTheme.init();
});