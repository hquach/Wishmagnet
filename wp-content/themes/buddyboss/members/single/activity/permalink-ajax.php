<?php if (!did_action('get_header')) do_action('get_header'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>
		
		<?php
			do_action( 'bp_head' );
			wp_head();
		?>
	</head>
	
	<body <?php body_class() ?> id="bp-default">
			
		<div id="content" class="activity buddyboss-activity-ajax">
			<div class="padder">
	
				<div id="item-body">
						<?php if ( bp_has_activities( 'display_comments=threaded&include=' . bp_current_action() ) ) : ?>
					
							<ul id="activity-stream" class="activity-list item-list">
							<?php while ( bp_activities() ) : bp_the_activity(); ?>
					
								<?php locate_template( array( 'activity/entry.php' ), true ) ?>
					
							<?php endwhile; ?>
							</ul>
					
						<?php endif; ?>
				</div><!-- #item-body -->
	
			</div><!-- .padder -->
		</div><!-- #content -->
	</body>
</html>