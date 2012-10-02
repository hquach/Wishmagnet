			</div> <!-- #container -->
                        
                        <div class="wish-feed">
			   <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Intentions Feed') ) : ?>
			   <?php endif; ?>
	                </div>
			
			<div id="push"></div>
		
		</div><!-- #wrapper -->

		<?php do_action( 'bp_after_container' ) ?>
		<?php do_action( 'bp_before_footer' ) ?>
	
			<div id="footer">
			
				<div id="colophon">
						    	
		    	<div id="footer-links">
			    	
			    	<ul class="footer-menu">
							<?php if ( has_nav_menu( 'secondary-menu' ) ) { ?>
								<?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'secondary-menu', 'items_wrap' => '%3$s' ) ); ?>
							<?php } else { ?>
								<?php wp_list_pages( 'title_li=&depth=3' . bp_dtheme_page_on_front() ); ?>
							<?php	} ?>
						</ul>
			    	
		    	</div>
		    	
		    	<div id="credits">
			    	<p>Copyright &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?> &nbsp;&middot;&nbsp; <a href="http://www.buddyboss.com/" target="_blank">BuddyPress Themes by BuddyBoss</a> &nbsp;&middot;&nbsp; <?php wp_loginout( $redirect ); ?></p>
		    	</div>
	
				<?php do_action( 'bp_footer' ) ?>
				
				</div>
				
			</div><!-- #footer -->
		
		<?php do_action( 'bp_after_footer' ) ?>
	
		<!-- required to load the BuddyBar -->
		<?php wp_footer(); ?>
		
		<!-- append buddyboss_wall log if needed -->
		<?php if (BUDDY_BOSS_WALL_DEBUG):?>		
			<div class="buddyboss_log">
			<?php buddy_boss_dump_log();?>
			</div> 
		<?php endif;?>

	</body>

</html>