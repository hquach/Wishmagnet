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
}
  add_action('widgets_init', 'intention_register_widget'); 
  
/*function register_intention_scripts() {
//    wp_register_script('bpopup', get_template_directory_uri() . '/_inc/bpopup.js' );
//    wp_enqueue_script('bpopup');
//}
//  add_action('wp_head', 'register_intention_scripts');
 * 
 */
  
  
?>
