function openPopup(){
  jQuery('#bpopup').bPopup({modalClose: true}); 
   
   jQuery('.close').live('click', function(){
//        jQuery('#bpopup').html('');
        closePopup('bpopup');
    })   
   
}

function openPopup1(){  
  jQuery('#first_to_pop_up').bPopup();
   
   jQuery('.close').live('click', function(){
        closePopup('first_to_pop_up');
    })   
   
}

function openPopup2(){
  jQuery('#element_to_pop_up').bPopup();
   
   jQuery('.close').live('click', function(){       
        closePopup('element_to_pop_up');
    })   
   
}

function closePopup(id) {
    jQuery('#'+id).addClass('bModal __bPopup1');
    jQuery('#'+id).click();
    jQuery('#'+id).removeClass('bModal __bPopup1');
}




