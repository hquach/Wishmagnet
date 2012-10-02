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
        
function starttimer() {
  startstopTimer.toggle();    
  //startstopTimer.play();    
}

function startstopReset() {     
	startstopCurrent = 0;
	startstopTimer.stop().once();              
        //startstopTimer.stop();
        ajaxtoSave();
}

function ajaxtoSave(){    
     jQuery.ajax({
            url: Ajaxobj.ajax_url + 'admin-ajax.php',
            type : 'POST',
            data: ({
                action: 'saveWishTime',
                meditime: returnstr,
                postid: Ajaxobj.post_id
            }),
            success: function(resp) {           
                jQuery("#stats").html(resp);
            }
    });
}