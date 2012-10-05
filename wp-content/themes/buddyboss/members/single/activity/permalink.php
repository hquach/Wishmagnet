<div class="activity no-ajax">
					<?php if ( bp_has_activities( 'display_comments=threaded&include=' . bp_current_action() ) ) : ?>
				
						<ul id="activity-stream" class="activity-list item-list">
						<?php while ( bp_activities() ) : bp_the_activity(); ?>
				
							<?php /*locate_template( array( 'activity/entry.php' ), true )*/ ?>
<?php

global $buddy_boss_wall,$bp;

?>

<?php do_action( 'bp_before_activity_entry' ); ?>

<li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">

	<div class="activity-content">

		<div class="activity-header">
			<?php bp_activity_action(); ?>
		</div>

		<?php if ( 'activity_comment' == bp_get_activity_type() ) : ?>

			<div class="activity-inreplyto">
				<strong><?php _e( 'In reply to: ', 'buddypress' ); ?></strong><?php bp_activity_parent_content(); ?> <a href="<?php bp_activity_thread_permalink(); ?>" class="view" title="<?php _e( 'View Thread / Permalink', 'buddypress' ); ?>"><?php _e( 'View', 'buddypress' ); ?></a>
			</div>

		<?php endif; ?>

		<?php if ( bp_activity_has_content() ) : ?>
			<div class="activity-inner">
				<?php bp_activity_content_body(); ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'bp_activity_entry_content' ); ?>

		<?php if ( is_user_logged_in() ) : ?>

			<div class="activity-meta">

				<?php echo bp_core_time_since( bp_get_activity_date_recorded() ) ?>										

				<!-- Delete -->
		
				<?php $owner = (bp_get_activity_user_id() == $bp->loggedin_user->id); ?>
					 
				<?php if ( (is_super_admin() || ($bp->current_action!="just-me" && $bp->is_item_admin) ||  $owner)) : ?>
				
					&middot; <a href="<?php echo wp_nonce_url( $bp->root_domain . '/' . $bp->activity->slug . '/delete/' . bp_get_activity_id() . '?cid=' . $comment_id, 'bp_activity_delete_link' ) ?>" class="delete acomment-delete confirm"><?php _e( 'Delete', 'buddypress' ) ?></a>
				
				<?php endif; ?>
				

				<?php do_action( 'bp_activity_entry_meta' ); ?>

			</div>

		<?php endif; ?>

	</div>

</li>

<?php do_action( 'bp_after_activity_entry' ); ?>                                                        
				
						<?php endwhile; ?>
						</ul>
				
					<?php endif; ?>
 </div>		 


