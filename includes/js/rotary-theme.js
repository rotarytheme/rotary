jQuery(document).ready(function($) {
	var rotaryTheme = {
		init: function() {
			//add extra class so we know if there are child items
			$('ul.sub-menu').parent().addClass('dropdown');
			//append the b tag in the sidebar to hold the ribbon immge
			$('.home aside > ul >li h3').after('<b></b>');
			//add class to upcoming programs edit so we can target it
			$('#home-upcoming-programs .post-edit-link').addClass('speakerdatedit');
			this.setUpArchives();
			this.setUpSlideShow();
			this.setUpEdits();
			this.setUpDatePicker();
			this.setUpDatatables();
			//this.setUpTabs();
			this.checkOpen();
			this.setupMaps();
			this.layoutProjects();
			$('#committeeselect, #committeewidget, #projectwidget').on('change', this.showCommittee);
			$('#morecomments').on('click', this.showMoreComments);
			$('#lesscomments').on('click', this.hideMoreComments);
			$('#newcomment, #newcommentproject').on('click', this.showCommentForm);
			$('#speakertabs').on('click', '.prevnext a', this.loadPrevNext);
			$('#wpas-reset input').on('click', this.resetForm);
			$('#search-toggle').on('click', this.toggleSearch);
			$('#speaker-archive-table tbody').on('mouseenter mouseleave', 'tr', this.hoverRow);
			$('#speaker-archive-table tbody').on('click', 'tr', this.selectRow);
			$('.projecticons').on('mouseenter mouseleave', '.icon', this.hoverIcons);
			$('.logged-in .projecticons').on('click', '.imgoing', this.toggleParticpant);
			$('.fancybox, .gallery-item a').fancybox({
				padding: 3,
				nextEffect: 'fade',
				prevEffect: 'fade',
				nextSpeed: 500,
				prevSpeed: 500
			});
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
			}
		},
		showMoreComments: function(e) {
			e.preventDefault();
			$('.committeecomment').show();
			$(this).hide();
			$('#lesscomments').show();
		},
		hideMoreComments: function(e) {
			e.preventDefault();
			$('.committeecomment:not(:first)').hide();
			$(this).hide();
			$('#morecomments').show();
		},
		showCommentForm: function(e) {
			e.preventDefault();
			$('#respond').toggle();
			$("#comment").focus();
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