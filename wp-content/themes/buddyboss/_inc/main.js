function openPopup(){
  jQuery('#bpopup').bPopup({modalClose: true}); 
   
   jQuery('.close').live('click', function(){
//        jQuery('#bpopup').html('');
        closePopup('bpopup');
    })   
   
}
function closePopup(id) {
    jQuery('#'+id).addClass('bModal __bPopup1');
    jQuery('#'+id).click();
    jQuery('#'+id).removeClass('bModal __bPopup1');
}

