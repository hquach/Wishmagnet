<?php do_action( 'bp_before_member_header' ) ?>

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

<div id="item-header-content">

	<h2><a href="<?php bp_displayed_user_link() ?>"><?php bp_displayed_user_fullname() ?></a></h2>
	
	<?php do_action( 'bp_before_member_header_meta' ) ?>

	<div id="item-meta">

		<div id="item-buttons">

			<?php do_action( 'bp_member_header_actions' ); ?>

		</div><!-- #item-buttons -->

		<?php
		 /***
		  * If you'd like to show specific profile fields here use:
		  * bp_profile_field_data( 'field=About Me' ); -- Pass the name of the field
		  */
		?>

		<?php do_action( 'bp_profile_header_meta' ) ?>

	</div><!-- #item-meta -->

</div><!-- #item-header-content -->

<?php do_action( 'bp_after_member_header' ) ?>

<?php do_action( 'template_notices' ) ?>