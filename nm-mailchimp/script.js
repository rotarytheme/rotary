jQuery(document).ready(function($) {
	$('#mailchimpcampaign').on('click', '#sendemailtest', function(event) {
		event.preventDefault();
		$('#ajax-loader').show();
		var data = {
			action: 'nm_front_camp',
			postid: $('.nmid').data('nmid'),
			content: $('article.status-publish #blogcontent').html(),
			sidebar: $('aside#speaker-sidebar').html(),
			sendtype: 'test'
		}
		$.post(nmOptions.ajaxurl, data, function(resp) {
			alert(resp);
			$('#ajax-loader').hide();
		});
	});
	$('#mailchimpcampaign').on('click', '#sendemailblast', function(event) {
		event.preventDefault();
		$('#ajax-loader').show();
		var data = {
			action: 'nm_front_camp',
			postid: $('.nmid').data('nmid'),
			content: $('article.status-publish #blogcontent').html(),
			sidebar: $('aside#speaker-sidebar').html(),
			sendtype: 'send'
		}
		$.post(nmOptions.ajaxurl, data, function(resp) {
			alert(resp);
			$('#ajax-loader').hide();
		});
	});
});