<?php get_header() ?>

	<div id="content" class="two_column">
		<div class="padder">

		<?php do_action( 'bp_before_blog_search' ) ?>

		<div class="page" id="blog-search" role="main">

			<h1 class="pagetitle">Search Results for "<?php the_search_query(); ?>"</h1>

			<?php if (have_posts()) : ?>

				<?php bp_dtheme_content_nav( 'nav-above' ); ?>

				<?php while (have_posts()) : the_post(); ?>

					<?php do_action( 'bp_before_blog_post' ) ?>

					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<div class="author-box">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), '50' ); ?>
							<p><?php printf( _x( 'by %s', 'Post written by...', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?></p>
						</div>
	
						<div class="post-content">
							<h1 class="posttitle"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ) ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
		
							<p class="date"><?php the_date('M j, Y') ?> at <?php the_time() ?> <?php _e( 'in', 'buddypress' ) ?> <?php the_category(', ') ?> <?php printf( __( 'by %s', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ) ?> <?php if ('open' == $post->comment_status): ?><span class="comments">&middot; <?php comments_popup_link( __( 'Leave a Comment &#187;', 'buddypress' ), __( '1 Comment &#187;', 'buddypress' ), __( '% Comments &#187;', 'buddypress' ) ); ?></span><?php else : ?>&middot; <?php _e('Comments are closed.', 'buddypress'); ?><?php endif; ?></p>
	
							<div class="entry">
								<?php the_excerpt(); ?>
	
								<?php wp_link_pages(array('before' => __( '<p><strong>Pages:</strong> ', 'buddypress' ), 'after' => '</p>', 'next_or_number' => 'number')); ?>
							</div>
							
						</div>
	
					</div>

					<?php do_action( 'bp_after_blog_post' ) ?>

				<?php endwhile; ?>

				<?php bp_dtheme_content_nav( 'nav-below' ); ?>

			<?php else : ?>

				<h4><?php _e( 'No posts found. Try a different search?', 'buddypress' ) ?></h4>
				<?php get_search_form() ?>

			<?php endif; ?>

		</div>

		<?php do_action( 'bp_after_blog_search' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php locate_template( array( 'sidebar-default.php' ), true ) ?>

<?php get_footer() ?>
