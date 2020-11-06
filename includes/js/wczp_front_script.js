jQuery(document).ready(function(){
    jQuery("body").on('click', '.wczpbtn', function(){
        var postcode = jQuery('.wczpcheck').val();
        jQuery('.wczpc_maindiv').append('<div class="wczpc_spinner"><img src="'+ object_name +'/includes/images/ZKZx.gif"></div>');
        jQuery('.wczpc_maindiv').addClass('wczpc_loader');
        jQuery.ajax({
            type: "post",
            dataType: 'json',
            url: ajax_postajax.ajaxurl,
            data: { 
                    action:   "WCZP_check_location",
                    postcode: postcode,
                 },
            success: function(msg){
                console.log(msg);
                jQuery(".wczpc_spinner").remove();
                jQuery('.wczpc_maindiv').removeClass('wczpc_loader');
                if(msg.totalrec == 1){
                    jQuery('.wczp_checkcode').show();
                    jQuery('.wczp_cookie_check_div').hide();
                    
                    var date = '';
                    if(msg.showdate == "on") {
                        date = "delivery date : "+msg.deliverydate;
                    }
                    jQuery('.response_pin').html('Available at ' +postcode+ "<br>"+date);   
                }else{
                    jQuery('.wczp_checkcode').show();
                    jQuery('.wczp_cookie_check_div').hide();
                    jQuery('.response_pin').html("Oops! We are not currently servicing your area.");
                }
            }
        });
    });


    jQuery("body").on('click', '.wczpcheckbtn', function(){
        jQuery('.wczp_cookie_check_div').show();
        jQuery('.wczp_checkcode').hide(); 
    });  


    jQuery("body").on('click', '.wczpinzipsubmit', function(){
         var popup_postcode = jQuery('.wczpopuppinzip').val();
         jQuery.ajax({
            type: "POST",
            url: ajax_postajax.ajaxurl,
            dataType: 'json',
            data: { 
                    action:"WCZP_popup_check_zip_code",
                    popup_postcode: popup_postcode,
                 },
            success: function(msg){

                if (msg.popup_pincode == ''){
                     jQuery('.modal-body').append("<p class='err'>Enter valid Zipcode</p>");
                }else{
                    location.reload(); 
                    jQuery('#myModal').hide();
               
                }
            }
        });
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

jQuery(document).ready(function() {
    var usernamea=getCookie("usernamea");

    if (usernamea != "popusetp") {
        jQuery("#myModal").show();
        //alert(usernamea);
        setCookie("usernamea", "popusetp", 30);
    }

    jQuery("body").on('click', '.close', function(e){

        e.preventDefault();

        jQuery('#myModal').hide();

    });
    jQuery('.wczpbtn').click(function() {
            jQuery('.response_pin').animate(
                { deg: 360 },
                {
                    duration: 1200,
                    step: function(now) {
                    jQuery(this).css({ transform: 'rotate(' + now + 'deg)' });
                }
            }
          );
    });
});

