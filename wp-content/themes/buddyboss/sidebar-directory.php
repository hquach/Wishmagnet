<?php do_action( 'bp_before_sidebar' ) ?>

<div id="sidebar" class="dir-sidebar">
	<div class="padder">

	<?php do_action( 'bp_inside_before_sidebar' ) ?>		
		<?php if ( function_exists('dynamic_sidebar') ) : ?>
		
			
				<!-- Members Sidebar -->
				
				<?php if ( bp_is_page( BP_MEMBERS_SLUG ) || bp_is_member() ) : ?>
						
					<?php if (buddyboss_is_plugin_active('bp-profile-search/bps-main.php')): ?>					
						<div class="widget no-search">
							<h3 class="widgettitle">Search Members</h3>
							<?php do_action('bp_profile_search_form');?>
						</div>
					<?php endif; ?>
					
					<?php dynamic_sidebar('members'); ?>
				
				<?php endif; ?>
				
				
				<!-- Groups Sidebar -->
				
				<?php if ( bp_is_active( 'groups' ) ) : ?>

					<?php if ( bp_is_page( BP_GROUPS_SLUG ) ) : ?>
							<?php dynamic_sidebar('groups'); ?>
					<?php endif; ?>
					
					<!-- Forums Sidebar -->
					
					<?php if ( bp_is_active( 'forums' ) && ( function_exists( 'bp_forums_is_installed_correctly' ) && !(int) bp_get_option( 'bp-disable-forum-directory' ) ) && bp_forums_is_installed_correctly() ) : ?>
						<?php if ( bp_is_page( BP_FORUMS_SLUG ) ) : ?>
								<?php dynamic_sidebar('forums'); ?>
						<?php endif; ?>
					<?php endif; ?>
				
				<?php endif; ?>
				
				
				<!-- Blogs Sidebar -->
							
				<?php if ( bp_is_active( 'blogs' ) && bp_core_is_multisite() ) : ?>
					<?php if ( bp_is_page( BP_BLOGS_SLUG ) ) : ?>
							<?php dynamic_sidebar('blogs'); ?>
					<?php endif; ?>
				<?php endif; ?>
				
				
				<!-- Activity Sidebar -->
				
				<?php if ( 'activity' != bp_dtheme_page_on_front() && bp_is_active( 'activity' ) ) : ?>
					<?php if ( bp_is_page( BP_ACTIVITY_SLUG ) ) : ?>
							<div class="no-search">
							<?php dynamic_sidebar('activity'); ?>
							</div>
					<?php endif; ?>
				<?php endif; ?>
				
		
		<?php endif; ?>		
	<?php do_action( 'bp_inside_after_sidebar' ) ?>

	</div><!-- .padder -->
</div><!-- #sidebar -->

<?php do_action( 'bp_after_sidebar' ) ?>
