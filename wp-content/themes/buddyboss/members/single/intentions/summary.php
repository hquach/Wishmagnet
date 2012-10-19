<?php
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
?>
    <div id="intention-summary">
      <ul>
        <li> 
            <div>Total Intentions Set: </div>
            <span><?php echo $intentions_cnt; ?></span>
        </li><li>
            <div>Total Intentions Meditated On: </div>
            <span><?php echo $mediton_cnt; ?></span>
        </li><li>
            <div>Total Meditated Time: </div>
            <span><?php echo $meditime_sum; ?></span>
        </li></ul>
    </div>    
