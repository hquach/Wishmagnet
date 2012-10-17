var currentselected='';

jQuery('#intentions-orderby-select').change(function() {
    currentselected = jQuery(this).attr('value');
     ajaxforSelect();      
});


function ajaxforSelect(){      
     jQuery.ajax({
            url: Recentobj.ajax_url + 'admin-ajax.php',
            type : 'POST',
            data: ({
                action: 'listIntentions',
                curoption: currentselected           
            }),
            success: function(resp) {           
                jQuery("#intentions-list").html(resp);
            }
    });     
}                