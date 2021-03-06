<?php do_action( 'bp_before_member_header' ) ?>

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