<?php
   if ( isset( $_POST['update_Wish'] ) ) {
    
      $wish_id = isset($_POST['wish_id']) ? $_POST['wish_id']: $post_id;
      $wisher_id = isset($_POST['wisher_id']) ? $_POST['wisher_id']: get_current_user_id();
      
          $the_wish = array(
            'ID'            => $wish_id,  
            'post_title'    => wp_strip_all_tags( $_POST['intention_title'] ),
            'post_content'  => wp_strip_all_tags( $_POST['intention_description'] ),
            'post_status'   => 'publish',
            'post_author'   => $wisher_id
            ); 
          
          wp_update_post( $the_wish );      
          
          $redirect_link = get_author_posts_url( $wisher_id , get_the_author_meta('display_name', $wisher_id) );
          header("Location: ".$redirect_link);
          exit();
          
  }else if ( isset( $_POST['cancel_Wish'] ) ) {
          $wisher_id = isset($_POST['wisher_id']) ? $_POST['wisher_id']: get_current_user_id();
          
          $redirect_link = get_author_posts_url( $wisher_id , get_the_author_meta('display_name', $wisher_id) );
          header("Location: ".$redirect_link);
          exit();  
          
  }else if ( isset( $_POST['delete_Wish'] ) ) {
        global $wpdb;
        $intention_tbl = $wpdb->prefix ."intentions";
        
        $wish_id = isset($_POST['wish_id']) ? $_POST['wish_id']: $post_id;
        $wisher_id = isset($_POST['wisher_id']) ? $_POST['wisher_id']: get_current_user_id();
        
         wp_delete_post( $wish_id, true );
        
        $wpdb->query(" DELETE FROM ".$intention_tbl." WHERE intention_id = ".$wish_id );
        
          $redirect_link = get_author_posts_url( $wisher_id , get_the_author_meta('display_name', $wisher_id) );
          header("Location: ".$redirect_link);
          exit();         
  }
?>
  
<?php get_header(); ?>

	<div id="content" class="two_column">
		<div class="padder">
                                      
			<?php do_action( 'bp_before_blog_single_post' ) ?>
	
			<div class="page" id="blog-single" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                              <?php $post_id = get_the_ID(); ?>  
  
<?php
  switch($_GET["action"]) {
    
    case 'edit':                       
        if ( empty( $post_id ) ) {
		wp_redirect( home_url() );
		exit();
	}       
        
	$post_type_object = get_post_type_object( get_post_type() );        
	if ( !$post_type_object )
		return;                          
        
        if( get_the_author_ID() != $current_user->ID && !is_super_admin()) { 
            echo '<p><b>Sorry, you are not the author of this wish o edit it!</b><br />
                Edit your own Wish ..</b></p>';
            return;
        } 
?>                                   
        <div class="wrap">
          <h3>Edit Your Wish</h3>
          <form name="wishedit" method="post">
            <input type="hidden" name="wish_id" value="<?php echo esc_attr($post_id); ?>" /> 
            <input type="hidden" name="wisher_id" value="<?php echo esc_attr(the_author_ID()); ?>" />
            <p>	
                <label for="intention_title">The Wish:</label><br />
                <input type="text" name="intention_title" id="intention_title" value="<?php echo esc_attr( htmlspecialchars( the_title() ) ); ?>" />                
            </p>
            <p>	
                <label for="intention_description">More Details about your wish:</label><br />
                <textarea name="intention_description" id="intention_description" cols="60" rows="5" /><?php echo strip_tags(get_the_content() ); ?></textarea>
            </p>            
            <input type="submit" name="update_Wish" value="Update" />	  
          </form> 
        </div>                            
<?php        
    break;

    case 'trash':           
        if ( empty( $post_id ) ) {
		wp_redirect( home_url() );
		exit();
	} 
        
        if( get_the_author_ID() != $current_user->ID && !is_super_admin() ) {
            echo '<p><b>Sorry, you are not the author of this wish o delete it.<br />
                Delete your own Wish ..</b></p>';
            return;
        } 
?> 
        <div class="wrap">
          <h3>Delete Your Wish</h3>
          <form name="wishdelete" method="post">
            <input type="hidden" name="wish_id" value="<?php echo esc_attr($post_id); ?>" /> 
            <input type="hidden" name="wisher_id" value="<?php echo esc_attr(the_author_ID()); ?>" />            
            <p>Are you sure you want to delete <b>"<?php the_title(); ?>"</b> wish?</p>
            <input type="submit" name="cancel_Wish" value="No" />
            <input type="submit" name="delete_Wish" value="Yes" />            
          </form> 
        </div>  
<?php                            
    break;
    default:
?>                                                    				
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<div class="author-box">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), '50' ); ?>
							<p><?php printf( _x( 'by %s', 'Post written by...', 'buddypress' ), str_replace( '<a href=', '<a rel="author" href=', bp_core_get_userlink( $post->post_author ) ) ); ?></p>
						</div>
	
						<div class="post-content">
							<h1 class="posttitle"><?php the_title(); ?></h1>
		
							<p class="date"><?php the_date('M j, Y') ?> at <?php the_time() ?> 
                                                            
                               <?php if (get_post_type() == "blog" ) : ?>
                                                            <?php _e( 'in', 'buddypress' ) ?> <?php the_category(', ') ?> 
                                <?php endif; ?>  
                                                            
                                                                <?php printf( __( 'by %s', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?>  
                                                            
                               <?php if (get_post_type() != "blog" ) : ?>
                                                              <?php edit_post_link( __( 'Edit this wish', 'buddypress' ), '&middot; <span class="edit-link">', '</span> &middot; ' ); ?>
                                                              <?php echo get_delete_post_link(get_the_ID()); ?>
                               <?php endif; ?>
                                                            
                                                        </p>

							<div class="entry">
                                                               <?php the_content( __( 'Read the rest of this entry &rarr;', 'buddypress' ) ); ?>
                                                            
                              <?php if (get_post_type() != "blog" ) : ?>									
                                                            	
                                                                <?php the_widget('WishTimerWidget'); ?>								
                                                            
			     <?php endif; ?>	
                                                                <?php wp_link_pages( array( 'before' => '<div class="page-link"><p>' . __( 'Pages: ', 'buddypress' ), 'after' => '</p></div>', 'next_or_number' => 'number' ) ); ?>
								   <span class="tags"><?php the_tags( __( 'Tags: ', 'buddypress' ), ', ', ''); ?></span>
							</div>
															
						</div>
						
						<div class="item-options">
							<div class="alignleft"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'buddypress' ) . '</span> %title' ); ?></div>
							<div class="alignright"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'buddypress' ) . '</span>' ); ?></div>
						</div>
	
					</div>
                            
                                 <?php  comments_template(); ?>
					
<?php 
    break; 
  } 
?>
			
			<?php endwhile; else: ?>

				<p><?php _e( 'Sorry, no posts matched your criteria.', 'buddypress' ) ?></p>

			<?php endif; ?>

		</div>

		<?php do_action( 'bp_after_blog_single_post' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar-default.php' ), true ) ?>

<?php get_footer() ?>