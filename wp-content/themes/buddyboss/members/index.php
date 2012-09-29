<?php

/**
 * BuddyPress - Members Directory
 *
 * @package BuddyPress
 * @subpackage BuddyBoss
 */

?>

<?php get_header( 'buddypress' ); ?>

<?php do_action( 'bp_before_directory_members_content' ) ?>

	<?php if ( is_active_sidebar('members') || buddyboss_is_plugin_active('bp-profile-search/bps-main.php') ) : ?>
		<div id="content" class="two_column">
	<?php else: ?>	
		<div id="content">		
		<div id="members-dir-search" class="dir-search">
			<?php bp_directory_members_search_form();?>
		</div><!-- #members-dir-search -->
	<?php endif; ?>
	
		<div class="padder">

		<form action="" method="post" id="members-directory-form" class="dir-form">
			
			<h1><?php the_title(); ?></h1>
			
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<div class="entry-directory">
					<?php the_content(); ?>
				</div>
			<?php endwhile; endif; ?>

			<div class="item-list-tabs" role="navigation">
				<ul>
					<li class="selected" id="members-all"><a href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_members_root_slug() ); ?>"><?php printf( __( 'All Members <span>%s</span>', 'buddypress' ), bp_get_total_member_count() ); ?></a></li>

					<?php if ( is_user_logged_in() && bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>

						<li id="members-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/' ?>"><?php printf( __( 'My Friends <span>%s</span>', 'buddypress' ), bp_get_total_friend_count( bp_loggedin_user_id() ) ); ?></a></li>

					<?php endif; ?>

					<?php do_action( 'bp_members_directory_member_types' ); ?>
					
					<?php do_action( 'bp_members_directory_member_sub_types' ); ?>

					<li id="members-order-select" class="last filter">

						<label for="members-order-by"><?php _e( 'Order By:', 'buddypress' ); ?></label>
						<select id="members-order-by">
							<option value="active"><?php _e( 'Last Active', 'buddypress' ); ?></option>
							<option value="newest"><?php _e( 'Newest Registered', 'buddypress' ); ?></option>

							<?php if ( bp_is_active( 'xprofile' ) ) : ?>

								<option value="alphabetical"><?php _e( 'Alphabetical', 'buddypress' ); ?></option>

							<?php endif; ?>

							<?php do_action( 'bp_members_directory_order_options' ); ?>

						</select>
					</li>

				</ul>
			</div><!-- .item-list-tabs -->


			<div id="members-dir-list" class="members dir-list">

				<?php locate_template( array( 'members/members-loop.php' ), true ); ?>

			</div><!-- #members-dir-list -->

			<?php do_action( 'bp_directory_members_content' ); ?>

			<?php wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); ?>

		</form><!-- #members-directory-form -->

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php do_action( 'bp_after_directory_members_content' ); ?>

	<?php locate_template( array( 'sidebar-directory.php' ), true ) ?>

<?php get_footer() ?>