<?php
/**
 * File to handle all API requests
 * Accepts POST method
 *
 * Each request will be identified by TAG
 * Response will be JSON data
 */

if(isset($_REQUEST['tag']) && $_REQUEST['tag']!='') {
    
  $tag = $_REQUEST['tag'];  
  $response = array("tag" => $tag, "success" => 0, "error" => 0);
  
  include('./wp-load.php');
  
  if($tag == "login") {
      
    $username = $_REQUEST["username"];
    $password = $_REQUEST["password"];     
    
        $creds = array();
        $creds['user_login'] = $username;
        $creds['user_password'] = $password;
        $creds['remember'] = false;
        
        $user = wp_signon( $creds, false );
                
        if ( is_wp_error($user) ){
            $response["error"] = 1;
            $response["error_msg"] = $user->get_error_message();           
            
        }else {            
            $response["success"] = 1;
            $response["user"]["id"] = $user->ID;
            $response["user"]["name"] = $user->display_name;
            $response["user"]["email"] = $user->user_email;
            $response["user"]["registered_at"] = $user->user_registered;
            /*$response["user"]["status"] = $user->user_status; */			                        
            
        } 
        
        echo json_encode($response);
                
  }else if($tag == "register") {
      require_once(ABSPATH . WPINC . '/registration.php');
      
        $username = $_REQUEST['username'];
        $email = $_REQUEST['email'];
        $password = $_REQUEST['password'];              
        
        if( username_exists( $username ) ) {
               $response["error"] = 2;
               $response["error_msg"] = "This username is already registered.";              
            
        }else if( email_exists($email) ) {
               $response["error"] = 3;
               $response["error_msg"] = "This email address is already registered.";             
            
        }else {
              $user_id = wp_create_user( $username, $password, $email ); 
              $response["success"] = 1;
              $response["user"]["id"] = $user_id;                            
        }
        
        echo json_encode($response);                                                                             
      
  }else if($tag == "userinfo") {
      $userid = $_REQUEST['userid'];
      $user = get_userdata( $userid ); 
      
      if($user == false) {
            $response["error"] = 4;
            $response["error_msg"] = "User does not exist.";           
          
      }else {          
            $response["success"] = 1;
            $response["user"]["id"] = $user->ID;
            $response["user"]["name"] = $user->display_name;
            $response["user"]["email"] = $user->user_email;
            $response["user"]["registered_at"] = $user->user_registered;
            /*$response["user"]["status"] = $user->user_status;*/
      }
      
      echo json_encode($response); 
      
      
  }else if($tag == "userwishes") {
      
      $userid = $_REQUEST['userid'];
      $total = count_user_posts( $userid );         
      $posts = get_posts(array('author'=>$userid));
      
      foreach ($posts as $post) {
          $post->meditators = get_total_meditators($post->ID);
          $post->totalmedittime = secns_to_human( get_total_seconds($post->ID) );          
      }      
                             
            $response["success"] = 1;
            $response["authorid"] = $userid;
            $response["total"] = $total;
            $response["posts"] = $posts;              
            
            echo json_encode($response); 
      
  } else if($tag == "wishes") {
      
      $number = (int)$_REQUEST['number']; 
      
      $args = array( 'orderby' => 'post_date', 'order' => 'DESC', 'post_type' => 'post', 'post_status' => 'publish');                     
      
      if(isset($number) && $number!='') {
          $args['numberposts'] = $number;
      }else {
          $args['numberposts'] = 50;
      }
                 
          $posts = get_posts( $args ); 
      
          foreach ($posts as $post) {
               $post->meditators = get_total_meditators($post->ID);
               $post->totalmedittime = secns_to_human( get_total_seconds($post->ID) );          
          }             
          
            $response["success"] = 1;       
            $response["posts"] = $posts;                    
               
           echo json_encode($response);
      
  } else {
        echo "Invalid Request";
  }
  
} else {
    echo "Access Denied";
}
?>