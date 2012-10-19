<?php get_header( 'buddypress' ); ?>

        <div id="content" <?php if ( is_active_sidebar('profile') ) : ?>class="two_column"<?php endif; ?>>
		<div class="padder">

			<?php do_action( 'bp_before_member_plugin_template' ); ?>

			<div id="item-header">

				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
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
				<div class="item-list-tabs no-ajax" id="object-nav">
					<ul>
						<?php bp_get_displayed_user_nav() ?>
						
						<?php if ( has_nav_menu( 'profile-menu' ) ) : ?>
								<?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'profile-menu', 'items_wrap' => '%3$s' ) ); ?>
						<?php endif; ?>

						<?php do_action( 'bp_member_options_nav' ) ?>
					</ul>
				</div>	                                                                                               
                        </div><!-- #item-nav -->
                        
                                <div id="item-body" role="main">
                                    <div class="item-list-tabs no-ajax" id="subnav">
                                        <ul>
                                            <?php bp_get_options_nav(); ?>                                            
                                        </ul>
                                    </div>
                                    
                                     <div class="members intentions"> 
                                    

<?php        

    if ( bp_is_current_action( 'mediton' ) ) :
	   locate_template( array( 'members/single/intentions/mediton.php' ), true );
    
    elseif ( bp_is_current_action( 'meditime' ) ) :
           locate_template( array( 'members/single/intentions/meditime.php' ), true );
    
    elseif ( bp_is_current_action( 'wishes' ) ) :
           locate_template( array( 'members/single/intentions/intentions-loop.php' ), true );    
    
    else :                    
           locate_template( array( 'members/single/intentions/summary.php' ), true );
?>
                                    
    <?php endif; ?>
                                     </div><!-- .intentions --> 
                                                                     
                                </div><!-- #item-body -->
               </div><!-- .padder -->
	</div><!-- #content -->
        
	<?php if ( is_active_sidebar('profile') ) : ?>
		<?php locate_template( array( 'sidebar-profile.php' ), true ) ?>
	<?php endif; ?>

<?php get_footer() ?>        
