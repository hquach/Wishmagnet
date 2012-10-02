<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>
		<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />

		<?php do_action( 'bp_head' ) ?>

		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ) ?>" />		
		<link rel="shortcut icon" href="<?php bloginfo('stylesheet_directory'); ?>/_inc/images/favicon.ico" type="image/x-icon">
		
		<?php
			if ( is_singular() && bp_is_blog_page() && get_option( 'thread_comments' ) )
				wp_enqueue_script( 'comment-reply' );
			
			wp_head(); 
		?>
	</head>

	<body <?php body_class() ?> id="bp-default">
	
	<!--[if lte IE 6]><script src="<?php bloginfo('template_directory'); ?>/_inc/js/ie6/warning.js"></script><script>window.onload=function(){e("<?php bloginfo('stylesheet_directory'); ?>/_inc/js/ie6/")}</script><![endif]-->

		<?php do_action( 'bp_before_header' ) ?>
		
		<div id="wrapper">

			<div id="header">
			
				<div class="padder">
	
						<?php if (get_option("buddy_boss_custom_logo", FALSE)!= FALSE) {
							$logo = get_option("buddy_boss_custom_logo");
							
							} else {
										$logo = get_bloginfo("template_directory")."/_inc/images/logo.jpg";
							} ?>
							
						<div id="logo">
								<a href="<?php echo site_url() ?>" title="<?php _e( 'Home', 'buddypress' )
					
						 ?>"><img src="<?php echo $logo ?>"/></a>
						</div>

							
						<ul class="sf-menu">
							<?php if ( has_nav_menu( 'primary-menu' ) ) { ?>
								<?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'primary-menu', 'items_wrap' => '%3$s' ) ); ?>
							<?php } else { ?>
								<?php wp_list_pages( 'title_li=&depth=3' . bp_dtheme_page_on_front() ); ?>
							<?php	} ?>
						</ul>
                                                                              
		 	<div class="wish-create">
			  <?php if ( !function_exists('dynamic_sidebar')
					|| !dynamic_sidebar('Intention') ) : ?>
			  <?php endif; ?>
		        </div>
							
																
				</div><!-- .padder -->
					
				<?php do_action( 'bp_header' ) ?>
	
			</div><!-- #header -->
	
			<?php do_action( 'bp_after_header' ) ?>                       
			<?php do_action( 'bp_before_container' ) ?>
	
			<div id="container">
			