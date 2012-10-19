<?php
    global $wpdb, $bp, $wp_query;     
    $intentions_table = $wpdb->prefix . 'intentions';
    $displayed_user_id = $bp->displayed_user->id;     
          
    $meditonsql = "SELECT $wpdb->posts.* 
                    FROM $wpdb->posts LEFT JOIN $intentions_table ON $wpdb->posts.ID=$intentions_table.intention_id 
                    WHERE $intentions_table.meditater_id = %d AND $wpdb->posts.post_status='publish' AND $wpdb->posts.post_type='post' 
                    GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC";
    
    $meditonsql = $wpdb->prepare( $meditonsql, $displayed_user_id );
    $totalusermeditons = $wpdb->get_results( $meditonsql, OBJECT_K );
    
    $per_page = intval(get_query_var('posts_per_page'));
    $wp_query->found_posts = count($totalusermeditons); 
    
    $wp_query->max_num_pages = ceil($wp_query->found_posts / $per_page);
    $on_page = intval(get_query_var('paged'));

    if($on_page == 0){ $on_page = 1; }
    $offset = ($on_page-1) * $per_page;
    
    $wp_query->request = $meditonsql . " LIMIT $per_page OFFSET $offset";
    $usermeditons = $wpdb->get_results($wp_query->request, OBJECT_K);
    
    if($usermeditons) :
              foreach( $usermeditons as $post ) : setup_postdata($post); ?>

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
        
        <?php endforeach; ?>

        <?php bp_dtheme_content_nav( 'nav-below' ); ?>
        
    <?php else : ?>
                                <div id="message" class="info">
                                    <p><?php _e( "Sorry, the user didn't meditate on any intention yet.", 'buddypress' ); ?></p>
                                </div>
   <?php endif; ?>
