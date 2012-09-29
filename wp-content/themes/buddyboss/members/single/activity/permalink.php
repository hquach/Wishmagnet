<?php get_header() ?>

	<div id="content" <?php if ( is_active_sidebar('profile') ) : ?>class="two_column"<?php endif; ?>>
		<div class="padder">

			<div id="item-header">
				<?php locate_template( array( 'members/single/member-header.php' ), true ) ?>
			</div><!-- #item-header -->

			<div id="item-nav">				
				<div class="item-list-tabs no-ajax" id="object-nav">
					<ul>
						<?php bp_get_displayed_user_nav() ?>

						<?php do_action( 'bp_member_options_nav' ) ?>
					</ul>
				</div>		
			</div><!-- #item-nav -->

			<div id="item-body">
				<div class="activity no-ajax">
					<?php if ( bp_has_activities( 'display_comments=threaded&include=' . bp_current_action() ) ) : ?>
				
						<ul id="activity-stream" class="activity-list item-list">
						<?php while ( bp_activities() ) : bp_the_activity(); ?>
				
							<?php locate_template( array( 'activity/entry.php' ), true ) ?>
				
						<?php endwhile; ?>
						</ul>
				
					<?php endif; ?>
				</div>
			</div><!-- #item-body -->

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php if ( is_active_sidebar('profile') ) : ?>
		<?php locate_template( array( 'sidebar-profile.php' ), true ) ?>
	<?php endif; ?>

<?php get_footer() ?>