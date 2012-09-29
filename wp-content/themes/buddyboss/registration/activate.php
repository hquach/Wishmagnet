<?php include(TEMPLATEPATH . '/registration/header.php'); ?>

<div id="login">

		<?php do_action( 'bp_before_activation_page' ) ?>

		<div id="form-container">

			<?php if ( bp_account_was_activated() ) : ?>

				<h1>Success!</h1>
				<div id="register-progress-bar" class="step-3">Step 3 of 3</div>
				
				<?php do_action( 'template_notices' ) ?>

				<?php do_action( 'bp_before_activate_content' ) ?>

				<?php if ( isset( $_GET['e'] ) ) : ?>
					<p class="instructions"><?php _e( 'Your account was activated successfully! Your account details have been sent to you in a separate email.', 'buddypress' ) ?></p>
				<?php else : ?>
					<p class="instructions"><?php _e( 'Your account was activated successfully! You can now log in with the username and password you provided when you signed up.', 'buddypress' ) ?></p>
					
					<div id="login-box">
				
						<form name="login-form" id="sidebar-login-form" class="standard-form register-section" action="<?php echo site_url( 'wp-login.php', 'login_post' ) ?>" method="post">
							<label><?php _e( 'Username', 'buddypress' ) ?><br />
							<input type="text" name="log" id="sidebar-user-login" class="input" value="<?php echo esc_attr(stripslashes($user_login)); ?>" /></label>
				
							<label><?php _e( 'Password', 'buddypress' ) ?><br />
							<input type="password" name="pwd" id="sidebar-user-pass" class="input" value="" /></label>
				
							<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="sidebar-rememberme" value="forever" /> <?php _e( 'Remember Me', 'buddypress' ) ?></label></p>
				
							<?php do_action( 'bp_sidebar_login_form' ) ?>
							<p class="submit">
								<input type="submit" name="wp-submit" id="sidebar-wp-submit" value="<?php _e('Log In'); ?>" tabindex="100" />
							</p>
							<input type="hidden" name="testcookie" value="1" />
						</form>
					
					</div>
					
				<?php endif; ?>

			<?php else : ?>

				<h1><?php _e( 'Activate your Account', 'buddypress' ) ?></h1>

				<?php do_action( 'bp_before_activate_content' ) ?>

				<p class="instructions"><?php _e( 'Please provide a valid activation key.', 'buddypress' ) ?></p>
				
				<form action="" method="get" class="standard-form register-section" id="activation-form">

					<label for="key"><?php _e( 'Activation Key:', 'buddypress' ) ?></label>
					<input type="text" name="key" id="key" value="" />

					<p class="submit">
						<input type="submit" name="submit" value="<?php _e( 'Activate', 'buddypress' ) ?> &rarr;" />
					</p>

				</form>
							
				
				<p id="nav">
					<?php if ( bp_get_signup_allowed() ) : ?><?php printf( __( '<a href="%s" title="Create an account">Register</a> | ', 'buddypress' ), site_url( BP_REGISTER_SLUG . '/' ) ) ?><?php endif; ?><?php wp_loginout( $redirect ); ?>
				</p>

			<?php endif; ?>

			<?php do_action( 'bp_after_activate_content' ) ?>

		</div><!-- #form-container -->

		<?php do_action( 'bp_after_activation_page' ) ?>
	
		<?php wp_footer(); ?>
	</body>
</html>
