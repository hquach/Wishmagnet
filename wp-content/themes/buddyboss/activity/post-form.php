<?php

/**
 * BuddyPress - Activity Post Form
 *
 * @package BuddyPress
 * @subpackage BuddyBoss
 */

?>


<?php global $buddy_boss_wall, $bp;  ?>

<?php if (bp_is_group() || bp_is_home() || bp_is_member() || bp_is_user_activity() || 'activity' == bp_dtheme_page_on_front() || ( is_page() && $bp->current_component === 'activity' ) ): ?>
 	
	<?php if ( !is_user_logged_in() ) : ?>
	
		<div id="message">
			<p>You need to <a href="<?php echo site_url( 'wp-login.php' ) ?>">log in</a> <?php if ( bp_get_signup_allowed() ) : ?> or <?php printf( __( ' <a class="create-account" href="%s" title="Create an account">create an account</a>', 'buddypress' ), site_url( BP_REGISTER_SLUG . '/' ) ) ?><?php endif; ?> to post to this user's Wall.</p>
		</div>
	
	<?php elseif (!bp_is_home() && (!is_super_admin() && !buddyboss_is_admin()) && (bp_is_user() && (BUDDY_BOSS_WALL_ENABLED && !$buddy_boss_wall->is_friend($bp->displayed_user->id)) )):?>

		<div id="message" class="info">
			<p><?php	printf( __( "You and %s are not friends. Request friendship to post to their Wall.", 'buddypress' ), bp_get_displayed_user_fullname() ) ?></p>
		</div>
	
	<?php else:?>
	
		<?php if ( is_user_logged_in() ) : ?>
			
			<?php if ( isset( $_GET['r'] ) ) : ?>
				<div id="message" class="info">
					<p><?php printf( __( 'You are mentioning %s in a new update, this user will be sent a notification of your message.', 'buddypress' ), bp_get_mentioned_user_display_name( $_GET['r'] ) ) ?></p>
				</div>
			<?php endif; ?>
			
			<form action="<?php bp_activity_post_form_action() ?>" method="post" id="whats-new-form" name="whats-new-form" >

				<?php do_action( 'bp_before_activity_post_form' ) ?>
				
				<h5> 
					<?php if ( bp_is_group() ) : ?>
						
						<?php printf( __( "What's new in %s, %s?", 'buddypress' ), bp_get_group_name(), bp_get_user_firstname() );?>
					
					<?php elseif ( bp_is_page( BP_ACTIVITY_SLUG ) || bp_is_my_profile() && bp_is_user_activity() ): ?>
					
						<?php printf( __( "What's new, %s?", 'buddypress' ), bp_get_user_firstname() );?>
					
					<?php elseif( !bp_is_my_profile() && bp_is_user_activity() ): ?>
								
						<?php printf( __( "Write something to %s?", 'buddypress' ), bp_get_displayed_user_fullname() ) ;?>
											
					<?php else :?>
						
						<?php printf( __( "Add a photo, %s", 'buddypress' ), bp_get_user_firstname() );?>
						
					<?php endif; ?>		
				</h5>
	
				<div id="whats-new-icons">
					<?php if (BUDDY_BOSS_PICS_ENABLED): ?>
						<div id="whats-new-pic"></div><!-- #whats-new-pic -->
					<?php endif; ?>
					<div id="whats-new-pic-uploader"></div>
				</div><!-- #whats-new-icons -->
				
				<div id="whats-new-content">
				
					<div id="whats-new-textarea">
						<textarea name="whats-new" id="whats-new" value="" /><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_attr( $_GET['r'] ) ?> <?php endif; ?></textarea>
					</div>
			
					<div id="whats-new-options">
						<div id="whats-new-submit">
							<input type="submit" name="aw-whats-new-submit" id="aw-whats-new-submit" value="<?php _e( 'Post Update', 'buddypress' ); ?>" />
						</div>
			
						<?php if ( bp_is_active( 'groups' ) && !bp_is_my_profile() && !bp_is_group() && bp_is_member() ) : ?>
						
							<div id="whats-new-post-in-box">
							
								<?php _e( 'Post in', 'buddypress' ) ?>:
			
								<select id="whats-new-post-in" name="whats-new-post-in">
									<option selected="selected" value="0"><?php _e( 'My Profile', 'buddypress' ); ?></option>
			
									<?php if ( bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0' ) ) :
										while ( bp_groups() ) : bp_the_group(); ?>
			
											<option value="<?php bp_group_id(); ?>"><?php bp_group_name(); ?></option>
			
										<?php endwhile; endif; ?>
										
								</select>
							</div>
							<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />
						
						<?php elseif ( bp_is_group_home() ) : ?>
							<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />
							<input type="hidden" id="whats-new-post-in" name="whats-new-post-in" value="<?php bp_group_id(); ?>" />
						<?php endif; ?>
			
						<?php do_action( 'bp_activity_post_form_options' ) ?>
			
					</div><!-- #whats-new-options -->
					
					<div class="clearfix" id="whats-new-pic-preview">
						<div class="clearfix" id="whats-new-pic-preview-inner"></div>
					</div><!-- #whats-new-pic-preview -->
					
				</div><!-- #whats-new-content -->
	
				<?php wp_nonce_field( 'post_update', '_wpnonce_post_update' ); ?>
				<?php do_action( 'bp_after_activity_post_form' ) ?>
		
			</form><!-- #whats-new-form -->

		<?php else : ?>

			<div id="message" class="info">
				<p>You need to <a href="<?php echo site_url( 'wp-login.php' ) ?>">log in</a> <?php if ( bp_get_signup_allowed() ) : ?> or <?php printf( __( ' <a class="create-account" href="%s" title="Create an account">create an account</a>', 'buddypress' ), site_url( BP_REGISTER_SLUG . '/' ) ) ?><?php endif; ?> to post to this user's wall.</p>
			</div>
			
		<?php endif; ?>

	<?php endif; ?>
<?php endif; ?>
