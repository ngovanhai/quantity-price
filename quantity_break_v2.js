var rootlinkQuantity = "https://apps.omegatheme.com/group-price-attribute"
if(typeof $ == 'undefined'){ 
    javascript: (function(e, s) {
        e.src = s;
        e.onload = function() {
            $ = jQuery.noConflict(); 
            $.getScript(`${rootlinkQuantity}/client/app.js?v=20`) 
        };
        document.head.appendChild(e);
    })(document.createElement('script'), 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js')

 
  
}else{ 
    $.getScript(`${rootlinkQuantity}/client/app.js?v=20`) 
}
 