<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

	<head profile="http://gmpg.org/xfn/11">

		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

		<title><?php bp_page_title() ?></title>

		<?php do_action( 'bp_head' ) ?>

		<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->

		<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/_inc/css/registration.css" type="text/css" media="screen" />
		
		<link rel="shortcut icon" href="<?php bloginfo('stylesheet_directory'); ?>/_inc/images/favicon.ico" type="image/x-icon">
		
		<?php wp_head(); ?>

	</head>

	<body <?php body_class() ?>>
	
	<!--[if lte IE 6]><script src="<?php bloginfo('stylesheet_directory'); ?>/_inc/js/ie6/warning.js"></script><script>window.onload=function(){e("<?php bloginfo('stylesheet_directory'); ?>/_inc/js/ie6/")}</script><![endif]-->
	
		<p id="backtoblog">
			<a href="<?php echo site_url() ?>" title="Are you lost?">&larr; Back to <?php bloginfo('name'); ?></a>
		</p>
		
		<?php if (get_option("buddy_boss_custom_logo", FALSE)!= FALSE)
			{
				$logo = get_option("buddy_boss_custom_logo");
				
			}
				else 
					{
						$logo = get_bloginfo("template_directory")."/_inc/images/logo.jpg";
					}
			?>
			<div id="logo">
				<img src="<?php echo $logo ?>"/>
			</div>