<?php

class SetIntentionWidget extends WP_Widget {
    
  function SetIntentionWidget() {
    $widget_ops = array(
        'classname' => 'SetIntentionWidget',
        'description' => 'Field to Set User Intention'
    );  
    
    $this->WP_Widget('SetIntentionWidget', 'Setting Intention', $widget_ops);
  }
  
  function form($instance){
    $defaults = array( 'texttoshow' => 'set your intention..' );
    $instance = wp_parse_args( (array) $instance, $defaults ); 
  ?>
    <p>
      <label for="<?php echo $this->get_field_id('texttoshow'); ?>">Text To Show in the field: 
        <input type="text" name="<?php echo $this->get_field_name('texttoshow'); ?>" id="<?php echo $this->get_field_id('texttoshow'); ?>" value="<?php echo attribute_escape( $instance['texttoshow'] ); ?>" class="widefat" />
      </label>
    </p>
  <?php      
  }
  
  function update($new_instance, $old_instance){
    $instance = $old_instance;
    $instance['texttoshow'] = $new_instance['texttoshow'];
    
    return $instance;      
  }
  
  function widget($args, $instance){
    extract($args);
	
    $texttoshow = $instance['texttoshow'];
    
    if(isset($_POST['set_intention'])){
           
      if ( !is_user_logged_in() ) {             
        ?>
            <div id="bpopup">
                <script type="text/javascript">openPopup();</script>
                <div class="close"></div>
                <form name="loginform" id="form_loginform" action="http://wishmagnet.dev/wp-login.php" method="post">
                    <div>
                        <label>User:</label>
                        <input type="text" name="log" id="user_login" class="user_input" tabindex="20" />
                    </div>
                    <div>
                        <label>Pass:</label>
                        <input type="password" name="pwd" id="user_pass" class="pass_input" tabindex="21" />
                    </div>
                    <p id="forgotText"><a href="http://wishmagnet.dev/wp-login.php?action=lostpassword" rel="nofollow">Forgot?</a>
                                          <a href="http://wishmagnet.dev/wp-login.php?action=register">Register</a></p>
                                          <input type="hidden" name="redirect_to" value="/">
                    <input type="submit" name="wp-submit" id="wp-submit" value="Login" tabindex="23" />
                    
                </form>
                <span class="fbLoginButton">
                    <script type="text/javascript">//<!--
                    document.write('<fb:login-button scope="" v="2" size="small" onlogin="jfb_js_login_callback();">Login with Facebook</fb:login-button>');    //--></script>                    
                </span>
            </div>    
        <?php                            
      }else if((!empty($_POST['myintention'])) && ($_POST['myintention'] != $_POST['def_texttoshow'])) {   
          global $wpdb;
          
          $wisher_id = get_current_user_id();
          $the_wish = array(
            'post_title'    => wp_strip_all_tags( $_POST['myintention'] ),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => $wisher_id
            );
          
        $new_post_title = wp_strip_all_tags( $_POST['myintention'] );
        
        $checkquery = $wpdb->prepare(
            'SELECT ID FROM ' . $wpdb->posts . '
                WHERE post_title = %s AND post_author= %d',                
                $new_post_title, $wisher_id); 
               
        $wpdb->query( $checkquery );
        
         if ($wpdb->num_rows == 0) {
          wp_insert_post( $the_wish );          
           echo '<p class="pinky">Your intention has been added successfully</p>'; 
         }
      }    
    }    
    
    $out ='<form method="post" action="'.$_SERVER["REQUEST_URI"].'">
            <input type="text" name="myintention" id="myintention" value="'.$texttoshow.'" 
                onfocus=\'if(this.value == "'.$texttoshow.'"){this.value = "";}\' onblur=\'if (this.value == "") {this.value = "'.$texttoshow.'";}\' />
            <input type="submit" name="set_intention" id="set_intention" value="Set!" /> 
            <input type="hidden" name="def_texttoshow" value="'.$texttoshow.'" />
          </form>';
  
        
    echo $before_widget; 
    echo $out;
    echo $after_widget;
  }
  
}

class WishTimerWidget extends WP_Widget {
    
  function WishTimerWidget() {
    $widget_ops = array(
        'classname' => 'WishTimerWidget',
        'description' => 'Start and Stop buttons for each Intention'
    );  
    
    $this->WP_Widget('WishTimerWidget', 'Wish Timer', $widget_ops);
  }
  
  function form($instance){
    $defaults = array( 'title' => 'Start Meditating' );
    $wish = wp_parse_args( (array) $wish, $defaults ); 
  ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">Title: 
        <input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo attribute_escape( $wish['title'] ); ?>" class="widefat" />
      </label>
    </p>
  <?php      
  }
  
  function update($new_instance, $old_instance){
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    
    return $instance;      
  }
  
  function widget($args, $instance){    
    extract($args);
	
    $title = $instance['title'];
                
    echo $before_widget;
    
    global $post_id; 
    if(isset($post_id) && $post_id > 0) {
        
      if ( is_user_logged_in() ) {              
    ?>
    
    <div class="wish-timer"> 
    <?php
    wp_enqueue_script("startend", get_template_directory_uri() . '/_inc/js/startend.js');
    wp_localize_script("startend", 'Ajaxobj', array('ajax_url' => admin_url(), 'post_id' => $post_id));
        
       if ( $title ) echo $before_title . $title . $after_title; 
       else echo '<h4>Start Meditating:</h4>'; ?> 
        
            <span class='startstoptime'><b>00 : 00</b></span>
            <div class="timer">
              <input type='button' value='Start/Pause' onclick='starttimer();' />
              <input type='button' value='Stop' onclick='startstopReset();' />        
            </div>
    </div>
    
    <?php       
      }
     
      //Stats:                     
                     $black_star = get_total_meditators($post_id);
                     $green_star = get_total_seconds($post_id);                     
                     $convert = secns_to_human($green_star);                        
                     ?>
    
       <div class="wish-stats" id="specialstats">
            <b>Total Meditated People: </b><span class="stat-num"><?php echo $black_star; ?></span><br />
            <b>Total Meditated Time: </b><span class="stat-num"><?php echo $convert; ?></span><br />                     
       </div>
      
   <?php   
    }
    echo $after_widget;
  }
  
}

class Recent_Intentions extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_recent_intentions', 'description' => __( "The most recent intentions") );
		parent::__construct('recent-intentions', __('Recent Intentions'), $widget_ops);
		$this->alt_option_name = 'widget_recent_intentions';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_intentions', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Intentions') : $instance['title'], $instance, $this->id_base);
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
 			$number = 10;

		$r = new WP_Query( apply_filters( 'widget_posts_args', array( 'posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) ) );
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>               
    
		  <ul class="wish_list">
		  <?php  while ($r->have_posts()) : $r->the_post(); ?>
                    
                   <?php 
                     $black_star = get_total_meditators(get_the_ID());
                     $green_star = get_total_seconds(get_the_ID());                     
                     $convert = secns_to_human($green_star);                    
                   ?>
		
                    <li>
                        <span class="for_wishes">
                            <span class="black_star"><?php echo $black_star; ?> people</span>
                            <span class="green_star"><?php echo $convert; ?></span>                          
                        </span>
                        <div class="wishes_info">
                          <a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if(get_the_title()) the_title(); else the_ID(); ?></a>
                          <span><?php printf( __( 'set by %s', 'buddypress' ), get_the_author_meta('display_name') ) ?></span>
                        </div>
                    </li>
                    
		<?php endwhile; ?>
		</ul>
    
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_intentions', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_intentions', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of intentions to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
      
}
?>
