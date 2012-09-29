<?php do_action( 'bp_before_sidebar' ) ?>

<div id="sidebar" class="left_column">
	<div class="padder">

	<?php do_action( 'bp_inside_before_sidebar' ) ?>

	<?php if ( is_user_logged_in() ) : ?>

		<?php do_action( 'bp_before_sidebar_me' ) ?>

		<div id="sidebar-me" class="widget">
						
			<h3 class="widgettitle"><?php _e( 'Hi' , 'buddyboss' ); ?> <?php echo bp_get_user_firstname() ?>!</h3>
			
				<a href="<?php echo bp_loggedin_user_domain() ?>">
					<?php bp_loggedin_user_avatar( 'type=full' ) ?>
				</a>
	
				<ul id="quicklinks">
					<li id="icon-profile"><a href="<?php echo bp_loggedin_user_domain() ?>"><?php _e( 'View My Profile' , 'buddyboss' ); ?></a></li>
					<li id="icon-edit"><a href="<?php echo bp_loggedin_user_domain() ?>profile/edit"><?php _e( 'Edit My Profile' , 'buddyboss' ); ?></a></li>
					<li id="icon-avatar"><a href="<?php echo bp_loggedin_user_domain() ?>profile/change-avatar"><?php _e( 'Change My Avatar' , 'buddyboss' ); ?></a></li>
					<li id="icon-search"><a class="last" href="<?php echo site_url() ?>/<?php echo BP_MEMBERS_SLUG ?>/"><?php _e( 'Browse Members' , 'buddyboss' ); ?></a></li>
				</ul>

			<?php do_action( 'bp_sidebar_me' ) ?>
		</div>

		<?php do_action( 'bp_after_sidebar_me' ) ?>

		<?php if ( function_exists( 'bp_message_get_notices' ) ) : ?>
			<?php bp_message_get_notices(); /* Site wide notices to all users */ ?>
		<?php endif; ?>

	<?php else : ?>

		<?php do_action( 'bp_before_sidebar_login_form' ) ?>
		
		<div id="login-box" class="widget">
	
			<h3 class="widgettitle"><?php _e( 'Log In' , 'buddypress' ); ?><?php if ( bp_get_signup_allowed() ) : ?><?php printf( __( ' or <a href="%s" title="Create an account">Join &rarr;</a>', 'buddypress' ), site_url( BP_REGISTER_SLUG . '/' ) ) ?><?php endif; ?></h3>
	
			<form name="login-form" id="sidebar-login-form" class="standard-form" action="<?php echo site_url( 'wp-login.php', 'login_post' ) ?>" method="post">
				<label><?php _e( 'Username', 'buddypress' ) ?><br />
				<input type="text" name="log" id="sidebar-user-login" class="input" value="<?php echo esc_attr(stripslashes($user_login)); ?>" /></label>
	
				<label><?php _e( 'Password', 'buddypress' ) ?><br />
				<input type="password" name="pwd" id="sidebar-user-pass" class="input" value="" /></label>
	
				<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="sidebar-rememberme" value="forever" /> <?php _e( 'Remember Me', 'buddypress' ) ?></label></p>
	
				<?php do_action( 'bp_sidebar_login_form' ) ?>
				<input type="submit" name="wp-submit" id="sidebar-wp-submit" value="<?php _e( 'Log In' , 'buddypress' ); ?>" tabindex="100" />
				<input type="hidden" name="testcookie" value="1" />
			</form>
		
		</div>

		<?php do_action( 'bp_after_sidebar_login_form' ) ?>
		
	<?php endif; ?>

	<?php if ( !function_exists('dynamic_sidebar')
			|| !dynamic_sidebar('Homepage Left') ) : ?>
	<?php endif; ?>

	<?php do_action( 'bp_inside_after_sidebar' ) ?>

	</div><!-- .padder -->
</div><!-- #sidebar -->

<?php do_action( 'bp_after_sidebar' ) ?>
