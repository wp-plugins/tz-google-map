jQuery(document).ready(function($) {
    upload_image();

    var $click = true;

    jQuery('.tzgooglemap_button').live('click',function(){

        var $parent = jQuery(this).parent().prev();
        var $count = $parent.find('li:last').attr('rel');
        var $count2 = parseInt($count) + 1;

        jQuery.ajax({
            url: tzgooglemap_array.admin_ajax,
            type: "POST",
            data: ({ action:'add_tzgooglemap', count:$count2 }),
            beforeSend: function() {
                jQuery('.tzgooglemap-box li .tz-googlemap-content').slideUp();
                upload_image();
            },
            success: function( data, textStatus, jqXHR ){
                var $ajax_response = jQuery( data );
                $parent.append($ajax_response);
                $parent.find('li:last-child').addClass('last');
            },
            error: function( jqXHR, textStatus, errorThrown ){

            },
            complete: function( jqXHR, textStatus ){
            }
        });
    })
    jQuery('.tz-googlemap-header').live('click', function () {
        jQuery(this).parent().find('.tz-googlemap-content').slideToggle();
        jQuery(this).toggleClass('google-header-active');
    })

    jQuery('.tzgooglemap_remove').live('click',function(){
        jQuery(this).parent().parent().parent().remove();
    })


});

function upload_image(){
    jQuery(document).on("click", ".upload_image_button", function() {

        jQuery.data(document.body, 'prevElement', jQuery(this).prev());

        window.send_to_editor = function(html) {
            var imgurl = jQuery('img',html).attr('src');

            var inputText = jQuery.data(document.body, 'prevElement');

            if(inputText != undefined && inputText != '')
            {

                inputText.val(imgurl);
            }

            tb_remove();
        };

        tb_show('', 'media-upload.php?type=image&TB_iframe=true');
        return false;
    });
}