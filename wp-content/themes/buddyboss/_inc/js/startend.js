var startstopTimer, returnstr='' , startstopCurrent = 0; 

startstopTimer = jQuery.timer(function() {
		var min = parseInt(startstopCurrent/6000);
		var sec = parseInt(startstopCurrent/100)-(min*60);
		var micro = startstopCurrent-(sec*100)-(min*6000);
		var output = "00"; if(min > 0) {output = min;}                
		jQuery('.startstoptime').html(output+" : "+ sec +" : "+micro);
                returnstr = output+":"+ sec +":"+micro;
		startstopCurrent+=7;
	}, 70, false);                              
  
function askConfirm() {        
        return "You have to press Stop, otherwise your meditation won't be calculated!";           
}   

function starttimer() {
  startstopTimer.toggle();    
  //startstopTimer.play(); 
  realtimetoSave(); 
  window.onbeforeunload = askConfirm;      
}

function startstopReset() {     
	startstopCurrent = 0;
	startstopTimer.stop().once();              
        //startstopTimer.stop();
        ajaxtoSave();
        window.onbeforeunload = null;
}

function ajaxtoSave(){    
     jQuery.ajax({
            url: Ajaxobj.ajax_url + 'admin-ajax.php',
            type : 'POST',
            data: ({
                action: 'saveWishTime',
                meditime: returnstr,
                postid: Ajaxobj.post_id,
                frontendcall: 'intention'
            }),
            success: function(resp) {           
                jQuery("#specialstats").html(resp);
            }
    });
}

function realtimetoSave(){    
     jQuery.ajax({
            url: Ajaxobj.ajax_url + 'admin-ajax.php',
            type : 'POST',
            data: ({
                action: 'saveRealTime',          
                postid: Ajaxobj.post_id           
            }),
            success: function(resp) {           
                jQuery("#realtime_stat").html(resp);
            }
    });
}