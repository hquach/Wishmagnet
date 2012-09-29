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
          $wisher_id = get_current_user_id();
          $the_wish = array(
            'post_title'    => wp_strip_all_tags( $_POST['myintention'] ),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => $wisher_id
            );
          
          wp_insert_post( $the_wish );
          //redirect to show a message
          //header("Location: http://www.example.com/widgets/" . urlencode($_POST['id']));

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
?>
