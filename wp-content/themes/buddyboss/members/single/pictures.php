<?php

/**
 * BuddyPress - Users Photos
 *
 * @package BuddyBoss
 */

?>

<?php get_header( 'buddypress' ); ?>

	<div id="content" <?php if ( is_active_sidebar('profile') ) : ?>class="two_column"<?php endif; ?>>
		<div class="padder">

			<?php do_action( 'bp_before_member_plugin_template' ); ?>

			<div id="item-header">

				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

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
				<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
					<ul>

						<?php bp_get_displayed_user_nav(); ?>
						
						<?php if ( has_nav_menu( 'profile-menu' ) ) : ?>
								<?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'profile-menu', 'items_wrap' => '%3$s' ) ); ?>
						<?php endif; ?>

						<?php do_action( 'bp_member_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->
			
			<div id="item-body" role="main">

				<?php do_action( 'bp_before_member_body' ); ?>
				
				<div class="item-list-tabs no-ajax" id="subnav">
					<ul>

						<?php bp_get_options_nav(); ?>

						<?php do_action( 'bp_member_plugin_options_nav' ); ?>

					</ul>
				</div><!-- .item-list-tabs -->
				
				<?php locate_template( array( 'activity/post-form.php'), true ) ?>
				
				<?php if ( buddyboss_has_pics() ) : ?>
					<ul class="gallery has-sidebar" id="buddyboss-pics-grid">
					<?php while ( buddyboss_has_pics() ) : buddyboss_the_pic(); ?>
					
						<?php
							$image = get_buddyboss_pic_image();
							$tn = get_buddyboss_pic_tn();
							if ( is_array( $image ) && !empty( $image ) && is_array( $tn ) && !empty( $tn ) ):
						?>
							<li class="gallery-item">
								<div>
									<a rel="gal_item" href="<?php echo get_buddyboss_pic_ajaxlink(); ?>">
										<img src="<?php echo $tn[0]; ?>" width="<?php echo $tn[1]; ?>" height="<?php echo $tn[2]; ?>" />
									</a>
								</div>
							</li>
						<?php endif; ?>

					<?php endwhile; ?>
					</ul>
					
					<?php buddyboss_pics_pagination(); ?>
				<?php else: ?>
				
					<div class="info" id="message"><p>There were no photos found.</p></div>
					
				<?php endif; ?>

				<?php do_action( 'bp_after_member_body' ); ?>
				
				<div id="is-buddyboss-pics-grid" data="<?php global $bp; echo $bp->loggedin_user->domain . BUDDY_BOSS_PICS_SLUG . '/'; ?>"></div>
				
			</div><!-- #item-body -->

			<?php do_action( 'bp_after_member_plugin_template' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php if ( is_active_sidebar('profile') ) : ?>
		<?php locate_template( array( 'sidebar-profile.php' ), true ) ?>
	<?php endif; ?>

<?php get_footer() ?>
