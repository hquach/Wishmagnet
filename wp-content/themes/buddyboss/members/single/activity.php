<?php

/**
 * BuddyPress - Users Activity
 *
 * @package BuddyPress
 * @subpackage BuddyBoss
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
	
		<?php bp_get_options_nav() ?>

		<li id="activity-filter-select" class="last">
			<label for="activity-filter-by"><?php _e( 'Show:', 'buddypress' ); ?></label> 
			<select id="activity-filter-by">
				<option value="-1"><?php _e( 'Everything', 'buddypress' ) ?></option>
				<option value="activity_update"><?php _e( 'Updates', 'buddypress' ) ?></option>

				<?php
				if ( !bp_is_current_action( 'groups' ) ) :
					if ( bp_is_active( 'blogs' ) ) : ?>

						<option value="new_blog_post"><?php _e( 'Posts', 'buddypress' ) ?></option>
						<option value="new_blog_comment"><?php _e( 'Comments', 'buddypress' ) ?></option>

					<?php
					endif;

					if ( bp_is_active( 'friends' ) ) : ?>

						<option value="friendship_accepted,friendship_created"><?php _e( 'Friendships', 'buddypress' ) ?></option>

					<?php endif;

				endif;

				if ( bp_is_active( 'forums' ) ) : ?>

					<option value="new_forum_topic"><?php _e( 'Forum Topics', 'buddypress' ) ?></option>
					<option value="new_forum_post"><?php _e( 'Forum Replies', 'buddypress' ) ?></option>

				<?php endif;

				if ( bp_is_active( 'groups' ) ) : ?>

					<option value="created_group"><?php _e( 'New Groups', 'buddypress' ) ?></option>
					<option value="joined_group"><?php _e( 'Group Memberships', 'buddypress' ) ?></option>

				<?php endif;

				do_action( 'bp_member_activity_filter_options' ); ?>

			</select>
		</li>		
				
	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bp_before_member_activity_post_form' ) ?>

<?php if (BUDDY_BOSS_WALL_ENABLED && ( '' == bp_current_action() || 'just-me' == bp_current_action() ) || !BUDDY_BOSS_WALL_ENABLED && bp_is_my_profile() ) : ?>
	
	<?php locate_template( array( 'activity/post-form.php'), true ) ?>

<?php endif; ?>

<?php do_action( 'bp_after_member_activity_post_form' ) ?>
<?php do_action( 'bp_before_member_activity_content' ) ?>


<?php global $buddy_boss_wall;
 
// if wall is enabled use custom template
if (BUDDY_BOSS_WALL_ENABLED && ( '' == bp_current_action() || 'just-me' == bp_current_action() )):
$buddy_boss_wall->filter_qs  = TRUE; ?>

<div class="activity">
	<?php locate_template( array( 'activity/activity-wall-loop.php' ), true ) ?>
</div><!-- .activity --> 

<?php else:?> 

<div class="activity">
	<?php locate_template( array( 'activity/activity-loop.php' ), true ) ?>
</div><!-- .activity --> 

<?php endif;?>


<?php do_action( 'bp_after_member_activity_content' ) ?>
