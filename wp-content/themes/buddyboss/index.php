<?php get_header() ?>

	<div id="content" class="two_column">
		<div class="padder">

		<?php do_action( 'bp_before_blog_home' ) ?>

		<div class="page" id="blog-latest">

			<!-- Display page title and content -->
			
				<?php
				if ( 'page' == get_option('show_on_front') && get_option('page_for_posts') && is_home() ) : the_post();
					$page_for_posts_id = get_option('page_for_posts');
					setup_postdata(get_page($page_for_posts_id));
				?>
					<div class="post">
						<div class="entry">
							<h1 class="pagetitle"><?php wp_title(''); ?></h1>
							<?php the_content(); ?>
						</div>
					</div>
				
				<?php rewind_posts(); endif; ?>
							
			<!-- Display blog posts -->
			
				<?php if ( have_posts() ) : ?>
	
					<?php while (have_posts()) : the_post(); ?>
	
						<?php do_action( 'bp_before_blog_post' ) ?>
	
						<div class="post" id="post-<?php the_ID(); ?>">
	
							<div class="author-box">
								<?php echo get_avatar( get_the_author_meta( 'user_email' ), '50' ); ?>
								<p><?php printf( __( 'by %s', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?></p>
							</div>
		
							<div class="post-content">
								<h1 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
			
								<p class="date"><?php the_date('M j, Y') ?> at <?php the_time() ?> <?php _e( 'in', 'buddypress' ) ?> <?php the_category(', ') ?> <?php printf( __( 'by %s', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?> <?php if ('open' == $post->comment_status): ?><span class="comments">&middot; <?php comments_popup_link( __( 'Leave a Comment &#187;', 'buddypress' ), __( '1 Comment &#187;', 'buddypress' ), __( '% Comments &#187;', 'buddypress' ) ); ?></span><?php else : ?>&middot; <?php _e('Comments are closed.', 'buddypress'); ?><?php endif; ?></p>
		
								<div class="entry">
									<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail');?></a>
							
									<?php the_excerpt( __( 'Read the rest of this entry &rarr;', 'buddypress' ) ); ?>
		
									<?php wp_link_pages(array('before' => __( '<p><strong>Pages:</strong> ', 'buddypress' ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
								</div>
								
							</div>
		
						</div>
	
						<?php do_action( 'bp_after_blog_post' ) ?>
	
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

		<?php do_action( 'bp_after_blog_home' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar-default.php' ), true ) ?>

<?php get_footer() ?>
