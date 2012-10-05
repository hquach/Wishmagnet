<?php

/**
 * BuddyPress - Users Home
 *
 * @package BuddyPress
 * @subpackage BuddyBoss
 */
 
?>

<?php get_header( 'buddypress' ); ?>

	<div id="content" <?php if ( is_active_sidebar('profile') ) : ?>class="two_column"<?php endif; ?>>
		<div class="padder">

			<?php do_action( 'bp_before_member_home_content' ) ?>

			<div id="item-header">
				<?php locate_template( array( 'members/single/member-header.php' ), true ) ?>
			</div><!-- #item-header -->

			<div id="item-nav">
                                <div id="item-header-avatar">

                                        <a href="<?php bp_user_link() ?>">
                                                <?php bp_displayed_user_avatar( 'type=full' ) ?>
                                        </a>

                                        <span class="activity">		
                                                <?php if ( is_user_logged_in() && bp_is_my_profile() ) : ?>
                                                                <a href="<?php echo bp_loggedin_user_domain() ?>profile/edit">Edit My Profile</a>		
                                                <?php else: ?>
                                                                <?php bp_last_activity( bp_displayed_user_id() ) ?>		
                                                <?php endif; ?>	
                                        </span>

                                </div><!-- #item-header-avatar -->
				<div class="item-list-tabs no-ajax" id="object-nav">
					<ul>
						<?php bp_get_displayed_user_nav() ?>
						
						<?php if ( has_nav_menu( 'profile-menu' ) ) : ?>
								<?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'profile-menu', 'items_wrap' => '%3$s' ) ); ?>
						<?php endif; ?>

						<?php do_action( 'bp_member_options_nav' ) ?>
					</ul>
				</div>		
			</div><!-- #item-nav -->

			<div id="item-body">

				<?php do_action( 'bp_before_member_body' );                                                               
				
				if ( bp_is_user_activity() || !bp_current_component() ) :
					locate_template( array( 'members/single/activity.php' ), true );

				 elseif ( bp_is_user_blogs() ) :
					locate_template( array( 'members/single/blogs.php'    ), true );

				elseif ( bp_is_user_friends() ) :
					locate_template( array( 'members/single/friends.php'  ), true );

				elseif ( bp_is_user_groups() ) :
					locate_template( array( 'members/single/groups.php'   ), true );

				elseif ( bp_is_user_messages() ) :
					locate_template( array( 'members/single/messages.php' ), true );

				elseif ( bp_is_user_profile() ) :
					locate_template( array( 'members/single/profile.php'  ), true );

				elseif ( bp_is_user_forums() ) :
					locate_template( array( 'members/single/forums.php'  ), true );

				// If nothing sticks, load a generic template
				else :
					locate_template( array( 'members/single/front.php'    ), true );

				endif;

				do_action( 'bp_after_member_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_member_home_content' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->
	
	<?php if ( is_active_sidebar('profile') ) : ?>
		<?php locate_template( array( 'sidebar-profile.php' ), true ) ?>
	<?php endif; ?>

<?php get_footer() ?>