jQuery(document).ready(function($) {
	
	 var rotaryMemberShip = {
		 init: function() {
			$('.user-edit-php .datepicker').datepicker();
			var _custom_media = true,
			_orig_send_attachment = wp.media.editor.send.attachment;
			$('.uploader .button').on('click', function(e){ 
				var send_attachment_bkp = wp.media.editor.send.attachment;
				var button = $(this);
				var id = button.attr('id').replace('_button', '');
				_custom_media = true;
				wp.media.editor.send.attachment = function(props, attachment){
				if ( _custom_media ) {
					$("#"+id).val(attachment.url);
					} else {
					return _orig_send_attachment.apply( this, [props, attachment] );
					};
				}
				wp.media.editor.open(button);
					return false;
				});
			$('.add_media').on('click', function(){
				_custom_media = false;
			});
			$('input[name="rotary_dacdb[rotary_use_dacdb]"]').on("click", function(){
			  	 var value = ($(this).val());
				 rotaryMemberShip.formSettings(value)
			   	
			});
	 	},
		formSettings: function(value) {
			if ('no' == value) {
				$('.dacdb').closest('tr').hide();
				$('.nodacdb').show();
			}
			else {
				$('.dacdb').closest('tr').show();
				$('.nodacdb').hide();
			}	
		}
	 }
	 rotaryMemberShip.init();
	 rotaryMemberShip.formSettings($('input[name="rotary_dacdb[rotary_use_dacdb]"]:checked').val());
});
