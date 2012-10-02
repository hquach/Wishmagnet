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
    $wisher_id = get_current_user_id();
    
    //$wish_id = get_the_ID();
    $wish_id = get_the_ID();
    
    $wish_query = "SELECT total_mins FROM ".$intention_tbl_name." WHERE intention_id=".$wish_id." AND meditator_id=".$wisher_id;
    echo $wish_query;
    //$pre_mins = $wpdb->get_row($wish_query);
    //$new_value = $pre_mins->total_mins;
    die();
}
add_action("wp_ajax_saveWishTime", "saveWishTime");  
  
/*function register_intention_scripts() {
//    wp_register_script('bpopup', get_template_directory_uri() . '/_inc/bpopup.js' );
//    wp_enqueue_script('bpopup');
//}
//  add_action('wp_head', 'register_intention_scripts');
 * 
 */
  
  
?>
