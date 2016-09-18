jQuery(document).ready(function($) {
	$('#speaker-mailchimpcampaign').on('click', '#speaker-sendemailtest', function(event) {
		event.preventDefault();
		$('#ajax-loader').show();
		var data = {
			action: 'nm_front_camp',
			postid: $('.nmid').data('nmid'),
			announcements: $( '#announcements-array').val(),
			hash: $( '#announcements-hash').val(),
			sendtype: 'test',
			campaigntype: 'speaker'
		}
		$.post(nmOptions.ajaxurl, data, function(resp) {
			$('#ajax-loader').hide();
		});
	});
	$('#speaker-mailchimpcampaign').on('click', '#speaker-sendemailblast', function(event) {
		event.preventDefault();
		$('#ajax-loader').show();
		var data = {
			action: 'nm_front_camp',
			postid: $('.nmid').data('nmid'),
			announcements: $( '#announcements-array').val(),
			hash: $( '#announcements-hash').val(),
			sendtype: 'send',
			campaigntype: 'speaker'
		}
		$.post(nmOptions.ajaxurl, data, function(resp) {
			$('#ajax-loader').hide();
		});
	});
	
	
	$('#announcements-mailchimpcampaign').on('click', '#announcements-sendemailtest', function(event) {
		event.preventDefault();
		$('#ajax-loader').show();
		var data = {
			action: 'nm_front_camp',
			announcements: $( '#announcements-array').val(),
			hash: $( '#announcements-hash').val(),
			sendtype: 'test',
			campaigntype: 'announcements'
		}
		$.post(nmOptions.ajaxurl, data, function(resp) {
			$('#ajax-loader').hide();
			alert(resp);
		});
	});
	$('#announcements-mailchimpcampaign').on('click', '#announcements-sendemailblast', function(event) {
		event.preventDefault();
		$('#ajax-loader').show();
		var data = {
			action: 'nm_front_camp',
			announcements: $( '#announcements-array').val(),
			hash: $( '#announcements-hash').val(),
			sendtype: 'send',
			campaigntype: 'announcements'
		}
		$.post(nmOptions.ajaxurl, data, function(resp) {
			$('#ajax-loader').hide();
		});
	});
});
