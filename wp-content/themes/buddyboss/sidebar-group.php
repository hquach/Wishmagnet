<?php do_action( 'bp_before_sidebar' ) ?>

<div id="sidebar">
	<div class="padder">

	<?php do_action( 'bp_inside_before_sidebar' ) ?>

		<div class="no-search">
			<?php if ( !function_exists('dynamic_sidebar')
					|| !dynamic_sidebar('Group') ) : ?>
			<?php endif; ?>
		</div>
		
	<?php do_action( 'bp_inside_after_sidebar' ) ?>

	</div><!-- .padder -->
</div><!-- #sidebar -->

<?php do_action( 'bp_after_sidebar' ) ?>
