jQuery(document).ready(function($) {
	$('.nm-save-settings').click(function(event) {
		event.preventDefault();
		$('#ajax-loader').show();

		var generate_text;
		var auto_tweet;

		if ($('#generate_text').is(":checked")){ generate_text = true; } else { generate_text = false; }
		if ($('#auto_tweet').is(":checked")){ auto_tweet = true; } else { auto_tweet = false; }

		var data = {
			action: 'nm_mc_front_save_settings',
			list_id: $('#nm_camp_list').val(),
			generate_text: generate_text,
			auto_tweet: auto_tweet, 
			auto_post: $('#auto_post').val()
		}

		$.post(ajaxurl, data, function(resp) {
			$('#ajax-loader').hide();
			alert('Settings Saved!');
		});
	});
});