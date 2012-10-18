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
    register_widget('WhosMeditatingWidget');
    register_widget('WhoMeditatedWidget');
}
  add_action('widgets_init', 'intention_register_widget');
  
function intention_profile_menu() {
    global $wpdb, $bp;
    $displayed_user_id = $bp->displayed_user->id;
    $posts_table = $wpdb->prefix."posts";   
    $intentions_table = $wpdb->prefix."intentions";
    
    $sql = "SELECT COUNT(*) as intention_count FROM $posts_table WHERE post_author = %d AND post_status='publish' AND post_type='post'";
			$sql = $wpdb->prepare( $sql, $displayed_user_id );                        
    $intentions_cnt = $wpdb->get_var( $sql );
    
    $meditonsql = "SELECT $posts_table.* 
                    FROM $posts_table LEFT JOIN $intentions_table ON $posts_table.ID=$intentions_table.intention_id 
                    WHERE $intentions_table.meditater_id = %d AND $posts_table.post_status='publish' AND $posts_table.post_type='post' 
                    GROUP BY $posts_table.ID ORDER BY $posts_table.post_date DESC";  
          
                        $meditonsql = $wpdb->prepare( $meditonsql, $displayed_user_id );                       
    $wpdb->get_results( $meditonsql );
    $mediton_cnt = $wpdb->num_rows;
    
    $meditotalsql = "SELECT SUM(total_mins) FROM $intentions_table WHERE meditater_id = %d";
                        $meditotalsql = $wpdb->prepare( $meditotalsql, $displayed_user_id ); 
    $meditime_sum = secns_to_human($wpdb->get_var( $meditotalsql ));    
    
			bp_core_new_nav_item( array(
				'name' => sprintf( 'Intentions <span>%d</span>', $intentions_cnt),
				'slug' => 'intentions',
				'position' => 40,
				'screen_function' => 'intentions_user_grid',
                                'default_subnav_slug' => 'my-wishes'
			));   
                                               
                        $parent_url = $bp->displayed_user->domain . 'intentions/';
                        
			bp_core_new_subnav_item( array(
				'name' => sprintf( 'My Intentions <span class="cnt">%d</span>', $intentions_cnt),
				'slug' => 'my-wishes',
				'parent_slug' => 'intentions',
                                'parent_url' => $parent_url,
				'screen_function' => 'intentions_user_grid',
				'position' => 10
                            
			));                        
                        bp_core_new_subnav_item( array(
				'name' => sprintf( 'Intentions meditated on <span class="cnt">%d</span>', $mediton_cnt),
				'slug' => 'mediton',
				'parent_slug' => 'intentions',	
                                'parent_url' => $parent_url,
				'screen_function' => 'intentions_user_grid',
				'position' => 20
                            
			));                        
                        bp_core_new_subnav_item( array(
				'name' => sprintf( 'Total Meditated Time <span class="cnt">%s</span>', $meditime_sum),
				'slug' => 'meditime',
				'parent_slug' => 'intentions',
                                'parent_url' => $parent_url,
				'screen_function' => 'intentions_user_grid',
				'position' => 30
			));                                                      
}
add_action( 'bp_setup_nav', 'intention_profile_menu' ); 

    function intentions_user_grid_title() {              
    }

    function intentions_user_grid_content() {	
    }

    function intentions_user_grid() {
                add_action( 'bp_template_title', 'intentions_user_grid_title' );
		add_action( 'bp_template_content', 'intentions_user_grid_content' );			
                bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/intentions' ) );
    }


  
function listIntentions() {
    global $wpdb;
    $intention_tbl_name = $wpdb->prefix . 'intentions';
    $realtime_tbl_name = $wpdb->prefix . 'realtime_medit';
    
    ob_clean();    
    $curoption = $_POST['curoption'];
    
    switch ($curoption) {
        case 'current':
          $querystr = "SELECT ".$wpdb->posts.".*, count(".$realtime_tbl_name.".meditater_id) as current_medit_count 
                FROM ".$wpdb->posts." LEFT JOIN ".$realtime_tbl_name." ON ".$wpdb->posts.".ID = ".$realtime_tbl_name.".intention_id
                WHERE ".$wpdb->posts.".post_status = 'publish' AND ".$wpdb->posts.".post_type = 'post' 
                GROUP BY ".$wpdb->posts.".ID
                ORDER BY current_medit_count DESC, post_date DESC LIMIT 10";                                               
            break;
        
        case 'past':
          $querystr = "SELECT ".$wpdb->posts.".*, count(".$intention_tbl_name.".meditater_id) as past_medit_count 
                FROM ".$wpdb->posts." LEFT JOIN ".$intention_tbl_name." ON ".$wpdb->posts.".ID = ".$intention_tbl_name.".intention_id
                WHERE ".$wpdb->posts.".post_status = 'publish' AND ".$wpdb->posts.".post_type = 'post' 
                GROUP BY ".$wpdb->posts.".ID
                ORDER BY past_medit_count DESC, post_date DESC LIMIT 10";             
            break;
        
        case 'time':
          $querystr = "SELECT ".$wpdb->posts.".*, SUM(".$intention_tbl_name.".total_mins) as coll_total_mins 
                FROM ".$wpdb->posts." LEFT JOIN ".$intention_tbl_name." ON ".$wpdb->posts.".ID = ".$intention_tbl_name.".intention_id
                WHERE ".$wpdb->posts.".post_status = 'publish' AND ".$wpdb->posts.".post_type = 'post' 
                GROUP BY ".$wpdb->posts.".ID
                ORDER BY coll_total_mins DESC, post_date DESC LIMIT 10";                
            break;        
        

        default:
          $querystr = "SELECT ".$wpdb->posts.".* 
                FROM ".$wpdb->posts."
                WHERE ".$wpdb->posts.".post_status = 'publish' AND ".$wpdb->posts.".post_type = 'post'                 
                ORDER BY post_date DESC LIMIT 10";                         
            break;
    }           
            $myposts = $wpdb->get_results($querystr, OBJECT_K);
            
            $response = '<ul class="wish_list">';
            
                foreach( $myposts as $post ) :	setup_postdata($post);
                   
                     $real_star = get_real_meditators($post->ID);
                     $black_star = get_total_meditators($post->ID);
                     $green_star = get_total_seconds($post->ID);                     
                     $convert = secns_to_human($green_star);                                  
                
         $response .= '<li>
                        <span class="for_wishes">
                            <span class="real_star">'. $real_star .' people</span>
                            <span class="black_star">'. $black_star .' people</span>
                            <span class="green_star">'. $convert .'</span>                          
                        </span>
                        <div class="wishes_info">
                            <a href="'. get_permalink($post->ID) .'" title="'. get_the_title($post->ID) .'">'. get_the_title($post->ID) .'</a>
                          <span> set by '. get_the_author_meta('display_name') .'</span>
                        </div>
                    </li>';                
        
                 endforeach;

            $response .= '</ul> 
                          <div class="stars_footer">
                            <ul>
                              <li id="or2">Total people currently meditating</li>
                              <li id="wh2" > Total people meditated</li>
                              <li id="red2"> Total collective meditation time</li>
                            </ul>
                          </div>';
            
    ob_end_clean();
    echo $response;
    die(1);    
}  
add_action("wp_ajax_listIntentions", "listIntentions");
add_action( "wp_ajax_nopriv_listIntentions", "listIntentions" );
  
function saveWishTime(){
    global $wpdb;
    $intention_tbl_name = $wpdb->prefix . 'intentions';
    $realtime_tbl_name = $wpdb->prefix . 'realtime_medit';
    
    ob_clean();
    $meditime = $_POST['meditime'];
    $wish_id = $_POST['postid'];    
    $wisher_id = get_current_user_id();    
                
    $realpeople = get_real_meditators($wish_id);
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
        
        if (is_current_medit($wish_id, $wisher_id)) {
            $wpdb->delete($realtime_tbl_name, array(
                'intention_id' => $wish_id,
                'meditater_id' => $wisher_id
            ));
                $realpeople--;
        }
        
/* See Who's meditating right now */
  if($realpeople > 0) {
      $realbutton = '<button id="my-first" onclick="openPopup1();">see who</button>
                <div id="first_to_pop_up">              
                    <div class="close"></div>                
                    <div class="avatar-block">';
      
          $meditIDs = $wpdb->get_col( 
                  $wpdb->prepare("SELECT DISTINCT(meditater_id) FROM $realtime_tbl_name WHERE intention_id = $wish_id"));
          
          foreach ( $meditIDs as $userID ) :
            $realbutton .= '<div class="item-avatar">            
                <a href="'.bp_core_get_user_domain( $userID ).'" 
                    title="'.bp_core_get_user_displayname( $userID ).'">
                    '.bp_core_fetch_avatar ( array( 'item_id' => $userID ) ).' 
                    '. bp_core_get_user_displayname( $userID ).'</a>              
            </div>';               
          endforeach;
      
     $realbutton .= '</div></div>';
  }else $realbutton = '';     
  
/* See Who already meditated */
  if($totalpeople > 0) {
      $totalbutton = '<button id="my-button" onclick="openPopup2();">see who</button>
                <div id="element_to_pop_up">              
                    <div class="close"></div>                
                    <div class="avatar-block">';
      
          $totmeditIDs = $wpdb->get_col( 
                  $wpdb->prepare("SELECT DISTINCT(meditater_id) FROM $intention_tbl_name WHERE intention_id = $wish_id"));
          
          foreach ( $totmeditIDs as $totuserID ) : 
            $totalbutton .= '<div class="item-avatar">            
                <a href="'. bp_core_get_user_domain( $totuserID ) .' 
                    title="'. bp_core_get_user_displayname( $totuserID ) .'">
                    '. bp_core_fetch_avatar ( array( 'item_id' => $totuserID ) ) .' 
                    '. bp_core_get_user_displayname( $totuserID ) .'</a>              
            </div>';               
          endforeach;     
      
     $totalbutton .= '</div></div>';            
  }else $totalbutton = ''; 
  
        $response = '<span class="autowidth">  
           <ul>
               <li id="orange"><b>Currently Meditating People:</b> 
                   <span class="stat-num"><span id="realtime_stat">'. $realpeople .' '. $realbutton .'</span></span>
               </li>
               <li id="white"><b>Total Meditated People:</b> 
                   <span class="stat-num">'. $totalpeople .' '. $totalbutton .'</span>
               </li>
               <li id="red"><b>Total Collective Meditated Time:</b> 
                   <span class="stat-num">'. $forhuman .'</span>
               </li>
           </ul>                                  
        </span>';    
                        
        ob_end_clean();                           
        echo $response;   
 
    die(1);
}
add_action("wp_ajax_saveWishTime", "saveWishTime"); 


function saveRealTime(){
    global $wpdb;
    $realtime_tbl_name = $wpdb->prefix . 'realtime_medit';
    
    ob_clean();
    $wish_id = $_POST['postid'];    
    $wisher_id = get_current_user_id();
    
    $realpeople = get_real_meditators($wish_id);
    
    if(!is_current_medit($wish_id, $wisher_id)) {        
       $wpdb->insert($realtime_tbl_name, array('intention_id' => $wish_id , 'meditater_id' => $wisher_id), array('%s','%s'));        
       $realpeople++;        
    }
    
/* See Who's meditating right now */
  if($realpeople > 0) {
      $realbutton = '<button id="my-first" onclick="openPopup1();">see who</button>
                <div id="first_to_pop_up">              
                    <div class="close"></div>                
                    <div class="avatar-block">';
      
          $meditIDs = $wpdb->get_col( 
                  $wpdb->prepare("SELECT DISTINCT(meditater_id) FROM $realtime_tbl_name WHERE intention_id = $wish_id"));
          
          foreach ( $meditIDs as $userID ) :
            $realbutton .= '<div class="item-avatar">            
                <a href="'.bp_core_get_user_domain( $userID ).'" 
                    title="'.bp_core_get_user_displayname( $userID ).'">
                    '.bp_core_fetch_avatar ( array( 'item_id' => $userID ) ).' 
                    '. bp_core_get_user_displayname( $userID ).'</a>              
            </div>';               
          endforeach;
      
     $realbutton .= '</div></div>';
  }else $realbutton = '';  
  
    ob_end_clean();            
    echo $realpeople.' '.$realbutton;                                       
    die(1);
}
add_action("wp_ajax_saveRealTime", "saveRealTime");

function deleteRealTime()
{
    global $wpdb;
    $realtime_tbl_name = $wpdb->prefix . 'realtime_medit';
    
    $wisher_id = wp_get_current_user()->data->ID;
    
    if(is_stuck_medit($wisher_id)) {
      $wpdb->delete($realtime_tbl_name, array('meditater_id' => $wisher_id));                     
    }
}
add_action('wp_logout', 'deleteRealTime');

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

function is_current_medit($wishid,$wisher) {
    global $wpdb;
    $real_tbl_name = $wpdb->prefix . 'realtime_medit'; 
    
    if(isset($wishid) && isset($wisher)) {      
      $is_medit = $wpdb->get_var( 
            $wpdb->prepare( "SELECT COUNT(*) FROM $real_tbl_name WHERE intention_id=$wishid AND meditater_id=$wisher" ) 
            );
     
      if ($is_medit == 0) return false;
      else return true;      
    }    
}

function is_stuck_medit($wisher) {
    global $wpdb;    
    $real_tbl_name = $wpdb->prefix . 'realtime_medit'; 
       
    if(isset($wisher)) {      
      $is_stuck = $wpdb->get_var( 
            $wpdb->prepare( "SELECT COUNT(*) FROM $real_tbl_name WHERE meditater_id=$wisher" ) 
            );
     
      if ($is_stuck == 0) return false;
      else return true;      
    }    
    
}

function get_real_meditators($wishid){
    global $wpdb;
    $real_tbl_name = $wpdb->prefix . 'realtime_medit'; 
    $intention_tbl_name = $wpdb->prefix . 'intentions'; 
    
    if(isset($wishid) && $wishid > 0) {
      $realquery = "SELECT COUNT(DISTINCT(meditater_id)) FROM $real_tbl_name WHERE intention_id=$wishid";
      $real_star = $wpdb->get_var($realquery);
      return $real_star;
      
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
