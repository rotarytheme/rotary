//addintional js
jQuery(document).ready(function($) {
    var bug_div = $('#acf-speaker_program_content');
    var bug_el = bug_div.find('textarea');
    var t_val;
    if( bug_el.attr('class') != '' ){
        bug_el.change(t_require_bug); t_require_bug();
    }
    function t_require_bug(){
        t_val = get_textfield_content();
        console.log( t_val );
        if( t_val != '' ){
            bug_div.removeClass('required').addClass('require_bug');
        } else {
            bug_div.removeClass('require_bug').addClass('required');
        }
    }
    function get_textfield_content(){
        return bug_el.val();
        /*if (jQuery("#wp-content-wrap").hasClass("tmce-active")){
         return tinyMCE.activeEditor.getContent();
         }else{
         return bug_div.val();
         }*/
    }
});