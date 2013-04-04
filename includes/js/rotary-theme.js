jQuery(document).ready(function($) {
	//add extra class so we know if there are child items
	$('ul.sub-menu').parent().addClass('dropdown');
	//see if we are on the archive page and then open archive menu
	$('.monthlist li a').removeClass('current');
	$('li.rotary-adv-archive-year a.icon').click(function() {
		if ($(this).hasClass('more')) {
			$(this).removeClass('more');
			$(this).addClass('less');
		} else {
			$(this).removeClass('less');
			$(this).addClass('more');
		}
		$(this).parent().children('ul').slideToggle('fast');
		return false;
	});	
	if ($('.archive .pagetitle').length) {
		var titleText = $('.archive .pagetitle').text();
		var afterColon = $.trim(titleText.substr(titleText.indexOf(":") + 1)); 
		if (afterColon == parseInt(afterColon)) {
			$year = $('.rotary-adv-archive-year .icon:contains("' + afterColon + '")');
			//console.log($year.parent('li').text());
			//$year.parent('li').children(' .monthlist li a:contains("All Months")').addClass('current');
			
			$year.next('.monthlist').children('li:first').children('a').addClass('current');
			$year.click();
		}
		else {
			$archiveLink = $('.monthlist li a:contains("' + afterColon + '")');
			$archiveLink.addClass('current').closest('.rotary-adv-archive-year').children('.icon').click();
		}

	}
	//append the b tag in the sidebar to hold the ribbon immge
	$('.home aside > ul >li h3').after('<b></b>');
	//slideshow
	if ( $('#slideshow').length ) {
		$('#slideshow').cycle({
	 		slideExpr: '.slide',	
     		fx: 'scrollHorz',
			height:'313px',
     		speed: '1000',
     		timeout: 10000,
     		delay: -2000,
			pager:'#navsection'
		});	
		$('#playpause').click(function(e) { 
			e.preventDefault();
    		$('#slideshow').cycle('toggle'); 
            $('#playpause').toggleClass('pause');
			$('#playpause').toggleClass('play');
		});
		$("#slideshow").touchwipe({
        	wipeLeft: function() {
                $("#slideshow").cycle("next");
          	},
          	wipeRight: function() {
                $("#slideshow").cycle("prev");
          	}
    	});

	}
	//if the user is an admin, he/she can edit widgets so we will allow direct access from font-end
	if ($('.widgetedit').length ) {
		if ($('body').hasClass('home')) {
			$sidebar = $('.home aside > ul > li h3');
		}
		else {	
			$sidebar = $('#secondary > ul > li h3');
		}
        

		$($sidebar).hover(
  			function () {
				var position= $(this).position();
    			$(this).append($('.widgetedit').css('left', position.left+'px').show());
  					}, 
  			function () {
    			$('.widgetedit').hide();
  		});
	}
	//if the user is an admin, he/she can edit theme customizations from the front-end	
    if ($('.headeredit').length ) {

		$('#branding h1, #branding #meetingaddress').hover(
  			function () {
				var position= $(this).position();
    			$(this).append($('.headeredit').css('left', position.left+'px').show());
  					}, 
  			function () {
    			$('.headeredit').hide();
  		});
		
	}
});