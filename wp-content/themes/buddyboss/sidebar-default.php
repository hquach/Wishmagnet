<!--<div id="post-dir-search" class="dir-search">
	<?php //get_search_form(); ?>
</div> -->

<?php do_action( 'bp_before_sidebar' ) ?>

<div id="sidebar">
	<div class="padder">

	<?php do_action( 'bp_inside_before_sidebar' ) ?>

		<?php if ( !function_exists('dynamic_sidebar')
				|| !dynamic_sidebar('Sidebar') ) : ?>
		<?php endif; ?>
		
	<?php do_action( 'bp_inside_after_sidebar' ) ?>

	</div><!-- .padder -->
</div><!-- #sidebar -->

<?php do_action( 'bp_after_sidebar' ) ?>
