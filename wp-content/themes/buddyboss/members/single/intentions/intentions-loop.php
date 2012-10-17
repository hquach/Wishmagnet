<?php     
    global $bp; 
    $author_query = 'author='.$bp->displayed_user->id;
?>
<?php query_posts($author_query); ?>

			<?php if ( have_posts() ) : ?>		

				<?php while (have_posts()) : the_post(); ?>
					
                                          <?php 
                                            $real_star = get_real_meditators(get_the_ID());
                                            $black_star = get_total_meditators(get_the_ID());
                                            $green_star = get_total_seconds(get_the_ID());                     
                                            $convert = secns_to_human($green_star);                    
                                          ?>

					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                                            
                                                    <span class="author_wishes">
                                                        <span class="real_star"><?php echo $real_star; ?> people</span>
                                                        <span class="black_star"><?php echo $black_star; ?> people</span>
                                                        <span class="green_star"><?php echo $convert; ?></span>                          
                                                    </span>                                            
	
						<div class="post-content">
							<h1 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
		
							<p class="date"><?php the_date('M j, Y') ?> at <?php the_time() ?>                                                             
                                                            <?php if ('open' == $post->comment_status): ?><span class="comments">&middot; <?php comments_popup_link( __( 'Leave a Comment &#187;', 'buddypress' ), __( '1 Comment &#187;', 'buddypress' ), __( '% Comments &#187;', 'buddypress' ) ); ?></span><?php else : ?>&middot; <?php _e('Comments are closed.', 'buddypress'); ?><?php endif; ?></p>
	
							<div class="entry">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail');?></a>
						
								<?php the_excerpt( __( 'Read the rest of this entry &rarr;', 'buddypress' ) ); ?>
	
								<?php wp_link_pages(array('before' => __( '<p><strong>Pages:</strong> ', 'buddypress' ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
							</div>
							
						</div>
	
					</div>
					
				<?php endwhile; ?>

				<?php bp_dtheme_content_nav( 'nav-below' ); ?>

			<?php else : ?>

                                <div id="message" class="info">
                                    <p><?php _e( "Sorry, the user doesn't have any intentions.", 'buddypress' ); ?></p>
                                </div>

			<?php endif; ?>

<?php wp_reset_query(); ?>
