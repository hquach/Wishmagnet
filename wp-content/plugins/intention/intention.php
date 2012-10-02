<?php
/*
 * Plugin Name: Intention
 * Description: This plugin will create the intention widget and start and end buttons for each post 
*/

function intention_install() {
  global $wpdb;
  
  $intention_tbl_name = $wpdb->prefix . 'intentions';
  
  $sql = "CREATE TABLE $intention_tbl_name (
    intention_id bigint(20) DEFAULT 0 NOT NULL ,
    meditater_id bigint(20) DEFAULT 0 NOT NULL ,
    total_mins bigint(20) DEFAULT 0 NOT NULL 
  );";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
  register_activation_hook(__FILE__,'intention_install');

function intention_register_widget() {
    require_once (ABSPATH . 'wp-content/plugins/intention/intention_widget.class.php');
    register_widget('SetIntentionWidget');
    register_widget('Recent_Intentions');
    register_widget('WishTimerWidget');
}
  add_action('widgets_init', 'intention_register_widget');
  
  
function saveWishTime(){
    global $wpdb;
    $intention_tbl_name = $wpdb->prefix . 'intentions';
    
    ob_clean();
    $meditime = $_POST['meditime'];
    $wish_id = $_POST['postid'];    
    $wisher_id = get_current_user_id();
    
    $totalpeople = get_total_meditators($wish_id);
    $totalseconds = get_total_seconds($wish_id);
    
    $newold = $wpdb->get_var( 
            $wpdb->prepare( "SELECT COUNT(*) FROM $intention_tbl_name WHERE intention_id=$wish_id AND meditater_id=$wisher_id" ) 
            );
    
    if($newold > 0){       
      $wish_total = $wpdb->get_var(
            $wpdb->prepare("SELECT total_mins FROM $intention_tbl_name WHERE intention_id=$wish_id AND meditater_id=$wisher_id")
          );
    }else { $wish_total = 0; }
    
     
        list($minutes, $seconds, $micros) = sscanf($meditime, "%d:%d:%d");
        //$time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
        $time_seconds = $minutes * 60 + $seconds;
        
         $wish_total += $time_seconds;
        
    if($newold > 0){            
        $wpdb->update($intention_tbl_name,
            array(
                'total_mins' => $wish_total
               ),
            array( 
                'intention_id' => $wish_id,
                'meditater_id' => $wisher_id
               ),
            array( '%s' ),
            array( '%s', '%s')
            );
    }else { 
       $wpdb->insert($intention_tbl_name, array('total_mins' => $wish_total , 'intention_id' => $wish_id , 'meditater_id' => $wisher_id), array('%s','%s','%s'));        
       $totalpeople++;
        }
        
        $totalseconds+= $time_seconds;        
        $forhuman = secns_to_human($totalseconds);
        
        $response = '<b>Total Meditated People: </b>'. $totalpeople .'<br />
            <b>Total Meditated Time: </b>'. $forhuman .'<br />'; 
        
        echo $response;        
    die();
}
add_action("wp_ajax_saveWishTime", "saveWishTime"); 

function secns_to_human($secs) {
        $units = array(
                "week"   => 7*24*3600,
                "day"    =>   24*3600,
                "hour"   =>      3600,
                "min"    =>        60,
                "sec"    =>         1,
        );
        
        if ( $secs == 0 ) return "0 secs";
        $s = "";

        foreach ( $units as $name => $divisor ) {
                if ( $quot = intval($secs / $divisor) ) {
                        $s .= "$quot $name";
                        $s .= (abs($quot) > 1 ? "s" : "") . ", ";
                        $secs -= $quot * $divisor;
                }
        }

        return substr($s, 0, -2);
}

function get_total_meditators($wishid){
    global $wpdb;
    $stars_tbl_name = $wpdb->prefix . 'intentions';   
    
    if(isset($wishid) && $wishid > 0) {
      $blackquery = "SELECT COUNT(DISTINCT(meditater_id)) FROM $stars_tbl_name WHERE intention_id=$wishid";
      $black_star = $wpdb->get_var($blackquery);
      return $black_star;
      
    }else return 0;    
}

function get_total_seconds($wishid){
    global $wpdb;
    $stars_tbl_name = $wpdb->prefix . 'intentions';
    
    if(isset($wishid) && $wishid > 0) {
        $greenquery = "SELECT SUM(total_mins) FROM $stars_tbl_name WHERE intention_id=$wishid";
        $green_star = $wpdb->get_var($greenquery);
        return $green_star;
        
    }else return 0; 
}
?>
