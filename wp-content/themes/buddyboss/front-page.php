<?php get_header() ?>

	<?php locate_template( array( 'sidebar-home-left.php' ), true ) ?>
							
		<!-- When homepage is using blog posts -->
		<?php if ( is_home() ) : ?>		
				
				<?php do_action( 'bp_before_blog_page' ) ?>	
				
				<div id="content" <?php if ( is_active_sidebar('home-right') ) : ?>class="three_column"<?php else : ?> class="two_column_left"<?php endif; ?>>
				<div class="padder">
					<div class="page" id="blog-latest">
					
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                                            
                                          <?php 
                                            $black_star = get_total_meditators(get_the_ID());
                                            $green_star = get_total_seconds(get_the_ID());                     
                                            $convert = secns_to_human($green_star);                    
                                          ?>
		
						<div class="post" id="post-<?php the_ID(); ?>">
                                                    
                                                    <span class="for_wishes">
                                                        <span class="black_star"><?php echo $black_star; ?> people</span>
                                                        <span class="green_star"><?php echo $convert; ?></span>                          
                                                    </span>
                                                    <div class="post_desc">
						
									<h1 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
				
									<p class="date"><?php the_date('M j, Y') ?> at <?php the_time() ?> <?php _e( 'in', 'buddypress' ) ?> <?php the_category(', ') ?> <?php printf( __( 'by %s', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?> <?php if ('open' == $post->comment_status): ?><span class="comments">&middot; <?php comments_popup_link( __( 'Leave a Comment &#187;', 'buddypress' ), __( '1 Comment &#187;', 'buddypress' ), __( '% Comments &#187;', 'buddypress' ) ); ?></span><?php else : ?>&middot; <?php _e('Comments are closed.', 'buddypress'); ?><?php endif; ?></p>
			
									<p class="entry">
										<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail');?></a>
								
										<?php the_excerpt( __( 'Read the rest of this entry &rarr;', 'buddypress' ) ); ?>
			
										<?php wp_link_pages(array('before' => __( '<p><strong>Pages:</strong> ', 'buddypress' ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
									</p>
                                                        </div>
										
						</div>
							
						<?php endwhile; ?>

							<div class="navigation">
			
								<div class="alignleft"><?php next_posts_link( __( '&larr; Previous Entries', 'buddypress' ) ) ?></div>
								<div class="alignright"><?php previous_posts_link( __( 'Next Entries &rarr;', 'buddypress' ) ) ?></div>
			
							</div>
			
						<?php else : ?>
			
							<h2 class="center"><?php _e( 'Not Found', 'buddypress' ) ?></h2>
							<p class="center"><?php _e( 'Sorry, but you are looking for something that isn\'t here.', 'buddypress' ) ?></p>
			
							<?php locate_template( array( 'searchform.php' ), true ) ?>
			
						<?php endif; ?>

						
						</div><!-- .page -->
					</div><!-- .padder -->
					</div><!-- #content -->
					
				<?php do_action( 'bp_after_blog_page' ) ?>
				<?php locate_template( array( 'sidebar-home-right.php' ), true ) ?>							
		
		<!-- When homepage is using a regular Page -->	
		<?php else : ?>

			<div id="content" <?php if ( is_active_sidebar('home-right') ) : ?>class="three_column"<?php else : ?> class="two_column_left"<?php endif; ?>>
			<div class="padder">
				<div class="page">
				
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				
					<h1 class="pagetitle"><?php the_title(); ?></h1>
	
					<div class="post" id="post-<?php the_ID(); ?>">
	
						<div class="entry">
	
							<?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'buddypress' ) ); ?>
	
							<?php wp_link_pages( array( 'before' => __( '<p><strong>Pages:</strong> ', 'buddypress' ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
	
						</div>
	
					</div>
	
				<?php endwhile; endif; ?>	
				
				</div><!-- .page -->	
			</div><!-- .padder -->
			</div><!-- #content -->
			
			<?php locate_template( array( 'sidebar-home-right.php' ), true ) ?>							
			
	<?php endif; ?>

<?php get_footer(); ?>
