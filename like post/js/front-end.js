jQuery(document).ready(function(){
	var total_like;
	//When Create On Like Button
	jQuery(document).on('click', '.lp_like_btn', function(){

		var current_post_id = jQuery(this).data('post-id');
		var current = jQuery(this);
		total_like = current.data('total-like');

		if(getCookie('lp_'+current_post_id) == '' ){
			setCookie('lp_'+current_post_id,'cvalue',365)
			jQuery.ajax({
				url: lp_front_ajax.ajaxurl,
				type: 'POST',
				data: {
					action: 'increment_like',
					post_id: current_post_id
				},
				success: function(result){
					jQuery(current).addClass("lp_liked").text("LIKED");
					jQuery(current).parents('.li_like').find('.lp_like_count .text-wrapper p').text( result );
				}
			});
		}
	});
});


function setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
