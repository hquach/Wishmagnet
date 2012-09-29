<?php get_header(); ?>

	<div id="content" class="two_column">
		<div class="padder">

		<?php do_action( 'bp_before_404' ) ?>

		<div class="page 404">

			<h1 class="pagetitle"><?php _e( 'Page Not Found', 'buddypress' ) ?></h1>

			<div id="message" class="info">

				<p><?php _e( 'The page you were looking for was not found.', 'buddypress' ) ?>

			</div>

			<?php do_action( 'bp_404' ) ?>

		</div>

		<?php do_action( 'bp_after_404' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar-default.php' ), true ) ?>

<?php get_footer(); ?>