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
			this.setUpTabs();
			$('#morecomments').on('click', this.showMoreComments);
			$('#lesscomments').on('click', this.hideMoreComments);
			$('#newcomment').on('click', this.showCommentForm);
			$('#speakertabs').on('click', '.prevnext a', this.loadPrevNext);
			$('#wpas-reset input').on('click', this.resetForm);
			$('#search-toggle').on('click', this.toggleSearch);
			$('#speaker-archive-table tbody').on('mouseenter mouseleave', 'tr', this.hoverRow);
			$('#speaker-archive-table tbody').on('click', 'tr', this.selectRow);
			$('.fancybox').fancybox({
				padding: 3,
				nextEffect: 'fade',
				prevEffect: 'fade',
				nextSpeed: 500,
				prevSpeed: 500
			});
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
				$($sidebar).hover(

				function() {
					var position = $(this).position();
					$(this).append($('.widgetedit').css('left', position.left + 'px').show());
				}, function() {
					$('.widgetedit').hide();
				});
			}
			//if the user is an admin, he/she can edit theme customizations from the front-end	
			if ($('.headeredit').length) {
				$('#branding h1, #branding #meetingaddress').hover(

				function() {
					var position = $(this).position();
					var editLeft = position.left;
					if ($(this).text() === $('#branding h1').text()) {
						editLeft += 300;
					}
					$(this).append($('.headeredit').css('left', editLeft + 'px').show());
				}, function() {
					$('.headeredit').hide();
				});
			}
			if ($('.speakerdatedit').length) {
				$('.home-upcoming-programs-speaker-date').hover(

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