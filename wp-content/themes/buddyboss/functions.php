<?php
/**
 * BuddyBoss theme functions and definitions. Largely taken from bp-default.
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress and BuddyPress to change core functionality.
 *
 * The first function, bp_dtheme_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails and navigation menus, and
 * for BuddyPress, action buttons and javascript localisation.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development, http://codex.wordpress.org/Child_Themes
 * and http://codex.buddypress.org/theme-development/building-a-buddypress-child-theme/), you can override
 * certain functions (those wrapped in a function_exists() call) by defining them first in your
 * child theme's functions.php file. The child theme's functions.php file is included before the
 * parent theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 */

if( function_exists( 'load_theme_textdomain' ) ) {
load_theme_textdomain('buddyboss', TEMPLATEPATH . '/languages/');
}

if ( !function_exists( 'bp_is_active' ) )
	return;

// If BuddyPress is not activated, switch back to the default WP theme
if ( !defined( 'BP_VERSION' ) )
	switch_theme( WP_DEFAULT_THEME, WP_DEFAULT_THEME );

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 591;

add_action( 'init', 'blockusers_init' );
function blockusers_init() {  

    if ( is_admin()) {         
        if(!isset($_REQUEST['action']) && ! current_user_can( 'administrator' ) ) {           
            wp_redirect( home_url() );
            exit;
        }
    }
}

if ( !function_exists( 'bp_dtheme_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress and BuddyPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override bp_dtheme_setup() in a child theme, add your own bp_dtheme_setup to your child theme's
 * functions.php file.
 *
 * @global object $bp Global BuddyPress settings object
 * @since BuddyPress 1.5
 */
function bp_dtheme_setup() {
	global $bp;

	// Load the AJAX functions for the theme
	require( TEMPLATEPATH . '/_inc/ajax.php' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary-menu' => __( 'Primary Menu' ),
		'secondary-menu' => __( 'Footer Menu' ),
		'profile-menu' => __( 'Profile Sidebar' ),
		'group-menu' => __( 'Group Sidebar' ),
	) );

	if ( !is_admin() ) {
		// Register buttons for the relevant component templates
		// Friends button
		if ( bp_is_active( 'friends' ) )
			add_action( 'bp_member_header_actions',    'bp_add_friend_button' );

		// Activity button
		if ( bp_is_active( 'activity' ) )
			add_action( 'bp_member_header_actions',    'bp_send_public_message_button' );

		// Messages button
		if ( bp_is_active( 'messages' ) )
			add_action( 'bp_member_header_actions',    'bp_send_private_message_button' );

		// Group buttons
		if ( bp_is_active( 'groups' ) ) {
			add_action( 'bp_group_header_actions',     'bp_group_join_button' );
			add_action( 'bp_group_header_actions',     'bp_group_new_topic_button' );
			add_action( 'bp_directory_groups_actions', 'bp_group_join_button' );
		}

		// Blog button
		if ( bp_is_active( 'blogs' ) )
			add_action( 'bp_directory_blogs_actions',  'bp_blogs_visit_blog_button' );
	}
}
add_action( 'after_setup_theme', 'bp_dtheme_setup' );
endif;

if ( !function_exists( 'bp_dtheme_enqueue_scripts' ) ) :
/**
 * Enqueue theme javascript safely
 *
 * @see http://codex.wordpress.org/Function_Reference/wp_enqueue_script
 * @since BuddyPress 1.5
 */
function bp_dtheme_enqueue_scripts() {
	
	// Bump this when changes are made to bust cache
	$version = '20110729';
	
	// Enqueue the global JS - Ajax will not work without it
	wp_enqueue_script( 'dtheme-ajax-js', get_template_directory_uri() . '/_inc/global.js', array( 'jquery' ), $version );
        
        
	// Add words that we need to use in JS to the end of the page so they can be translated and still used.
	$params = array(
		'my_favs'           => __( 'My Favorites', 'buddypress' ),
		'accepted'          => __( 'Accepted', 'buddypress' ),
		'rejected'          => __( 'Rejected', 'buddypress' ),
		'show_all_comments' => __( 'Show all comments for this thread', 'buddypress' ),
		'show_all'          => __( 'Show all', 'buddypress' ),
		'comments'          => __( 'comments', 'buddypress' ),
		'close'             => __( 'Close', 'buddypress' ),
		'view'              => __( 'View', 'buddypress' )
	);

	wp_localize_script( 'dtheme-ajax-js', 'BP_DTheme', $params );
        
        // Enqueue the main JS
        wp_enqueue_script( 'bpopup', get_template_directory_uri() . '/_inc/js/bpopup.js', array( 'jquery' ), $version );
        wp_enqueue_script( 'timer', get_template_directory_uri() . '/_inc/js/timer.js', array( 'jquery' ), $version );
	wp_enqueue_script( 'main', get_template_directory_uri() . '/_inc/main.js', array( 'jquery' ), $version );        
}
add_action( 'wp_enqueue_scripts', 'bp_dtheme_enqueue_scripts' );
endif;


if ( !function_exists( 'bp_dtheme_widgets_init' ) ) :
/**
 * Register widgetised areas, including one sidebar and four widget-ready columns in the footer.
 *
 * To override bp_dtheme_widgets_init() in a child theme, remove the action hook and add your own
 * function tied to the init hook.
 *
 * @since BuddyPress 1.5
 */
function bp_dtheme_widgets_init() {
	// Register the widget columns
	// Area 1, located in the pages and posts right column.
	register_sidebar( array(
			'name'          => 'Sidebar',
			'id'          	=> 'sidebar',
			'description'   => 'The Pages and Posts widget area. Right column is always present.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
	
	// Area 2, located in the homepage left column.
	register_sidebar( array(
			'name'          => 'Homepage Left',
			'id' 						=> 'home-left',
			'description'   => 'The Homepage Left widget area. Left column is always present.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
		
	// Area 3, located in the homepage right column.
	register_sidebar( array(
			'name'          => 'Homepage Right',
			'id' 						=> 'home-right',
			'description'   => 'The Homepage Right widget area. Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
		
	// Area 4, located in the Members Directory right column. Right column only appears if widgets are added.
	register_sidebar( array(
			'name'          => 'Members Directory',
			'id'          	=> 'members',
			'description'   => 'The Members Directory widget area. Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
		
	// Area 5, located in the Groups Directory right column. Right column only appears if widgets are added.
	register_sidebar( array(
			'name'          => 'Groups Directory',
			'id'          	=> 'groups',
			'description'   => 'The Groups Directory widget area. Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
		
	// Area 6, located in the Forums Directory right column. Right column only appears if widgets are added.
	register_sidebar( array(
			'name'          => 'Forums Directory',
			'id'          	=> 'forums',
			'description'   => 'The Forums Directory widget area. Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
		
	// Area 7, located in the Members Directory right column. Right column only appears if widgets are added.
	register_sidebar( array(
			'name'          => 'Blogs Directory',
			'id'          	=> 'blogs',
			'description'   => 'The Blogs Directory widget area (only for Multisite). Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
		
	// Area 8, located in the Activity Directory right column. Right column only appears if widgets are added.
	register_sidebar( array(
			'name'          => 'Activity Directory',
			'id'          	=> 'activity',
			'description'   => 'The Activity Directory widget area. Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
		
	// Area 9, located in the Individual Profile right column. Right column only appears if widgets are added.
	register_sidebar( array(
			'name'          => 'Profile',
			'id'          	=> 'profile',
			'description'   => 'The Individual Profile widget area. Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
	// Area 10, located in the Individual Group right column. Right column only appears if widgets are added.
	register_sidebar( array(
			'name'          => 'Group',
			'id'          	=> 'group',
			'description'   => 'The Individual Group widget area. Right column only appears if widgets are added.',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
        
        //Custom Area 11, that will be shown always in the header section
        register_sidebar( array(
			'name'          => 'Intention',
			'id'          	=> 'intention',
			'description'   => 'Header Widget Area to be shown in all pages',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
        
        //Custom Area 12, that will be shown always in the footer section
        register_sidebar( array(
			'name'          => 'Intentions Feed',
			'id'          	=> 'intention-feed',
			'description'   => 'Footer Widget Area to be shown in all pages',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widgettitle">',
			'after_title'   => '</h3>'
		) );
        
        //Custom Area 13, that will show whos meditating right now
        register_sidebar( array(
			'name'          => 'Current Mediators',
			'id'          	=> 'current-mediators',
			'description'   => 'Widget to show current mediators',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => ''
		) );
        
        //Custom Area 14, that will show whos meditating right now
        register_sidebar( array(
			'name'          => 'Past Mediators',
			'id'          	=> 'past-mediators',
			'description'   => 'Widget to show already meditated users',
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => ''
		) );        
}
add_action( 'widgets_init', 'bp_dtheme_widgets_init' );
endif;

if ( !function_exists( 'bp_dtheme_blog_comments' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own bp_dtheme_blog_comments(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @param mixed $comment Comment record from database
 * @param array $args Arguments from wp_list_comments() call
 * @param int $depth Comment nesting level
 * @see wp_list_comments()
 * @since BuddyPress 1.2
 */
function bp_dtheme_blog_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( 'pingback' == $comment->comment_type )
		return false;

	if ( 1 == $depth )
		$avatar_size = 50;
	else
		$avatar_size = 40;
	?>

	<li <?php comment_class() ?> id="comment-<?php comment_ID() ?>">
		<div class="comment-avatar-box">
				<a href="<?php echo get_comment_author_url() ?>" rel="nofollow">
					<?php if ( $comment->user_id ) : ?>
						<?php echo bp_core_fetch_avatar( array( 'item_id' => $comment->user_id, 'width' => $avatar_size, 'height' => $avatar_size, 'email' => $comment->comment_author_email ) ) ?>
					<?php else : ?>
						<?php echo get_avatar( $comment, $avatar_size ) ?>
					<?php endif; ?>
				</a>
		</div>

		<div class="comment-content">
			<div class="comment-meta">
					<?php
						/* translators: 1: comment author url, 2: comment author name, 4: comment date/timestamp */
						printf( __( '<a href="%1$s" rel="nofollow">%2$s</a> said on <span class="time-since">%4$s</span>', 'buddypress' ), get_comment_author_url(), get_comment_author(), get_comment_link(), get_comment_date() );
					?>
					
					<em>
						<?php if ( comments_open() ) : ?>
							<?php comment_reply_link( array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ); ?>
						<?php endif; ?>
						
						<?php if ( current_user_can( 'edit_comment', $comment->comment_ID ) ) : ?>
							<?php printf( '&middot; <a class="comment-edit-link bp-secondary-action" href="%1$s" title="%2$s">%3$s</a> ', get_edit_comment_link( $comment->comment_ID ), esc_attr__( 'Edit comment', 'buddypress' ), __( 'Edit', 'buddypress' ) ) ?>
						<?php endif; ?>
					</em>
			</div>

			<div class="comment-entry">
				<?php if ( $comment->comment_approved == '0' ) : ?>
				 	<em class="moderate"><?php _e( 'Your comment is awaiting moderation.', 'buddypress' ); ?></em>
				<?php endif; ?>

				<?php comment_text() ?>
			</div>

		</div>

<?php
}
endif;

if ( !function_exists( 'bp_dtheme_page_on_front' ) ) :
/**
 * Return the ID of a page set as the home page.
 *
 * @return false|int ID of page set as the home page
 * @since 1.2
 */
function bp_dtheme_page_on_front() {
	if ( 'page' != get_option( 'show_on_front' ) )
		return false;

	return apply_filters( 'bp_dtheme_page_on_front', get_option( 'page_on_front' ) );
}
endif;



if ( !function_exists( 'bp_dtheme_show_notice' ) ) :
/**
 * Show a notice when the theme is activated - workaround by Ozh (http://old.nabble.com/Activation-hook-exist-for-themes--td25211004.html)
 *
 * @since 1.2
 */
function bp_dtheme_show_notice() {
	global $pagenow;

	// Bail if bp-default theme was not just activated
	if ( empty( $_GET['activated'] ) || ( 'themes.php' != $pagenow ) || !is_admin() )
		return;

	?>

	<div id="message" class="updated fade">
		<p><?php printf( __( 'Theme activated! This theme supports <a href="%s">sidebar widgets</a> and <a href="%s">custom nav menus</a>.', 'buddypress' ), admin_url( 'widgets.php' ), admin_url( 'nav-menus.php' ) ) ?></p>
				
	</div>

	<style type="text/css">#message2, #message0 { display: none; }</style>

	<?php
}
add_action( 'admin_notices', 'bp_dtheme_show_notice' );
endif;

if ( !function_exists( 'bp_dtheme_main_nav' ) ) :
/**
 * wp_nav_menu() callback from the main navigation in header.php
 *
 * Used when the custom menus haven't been configured.
 *
 * @global object $bp Global BuddyPress settings object
 * @param array Menu arguments from wp_nav_menu()
 * @see wp_nav_menu()
 * @since BuddyPress 1.5
 */
function bp_dtheme_main_nav( $args ) {
	global $bp;

	$pages_args = array(
		'depth'      => 0,
		'echo'       => false,
		'exclude'    => '',
		'title_li'   => ''
	);
	$menu = wp_page_menu( $pages_args );
	$menu = str_replace( array( '<div class="menu"><ul>', '</ul></div>' ), array( '<ul id="nav">', '</ul><!-- #nav -->' ), $menu );
	echo $menu;

	do_action( 'bp_nav_items' );
}
endif;

if ( !function_exists( 'bp_dtheme_page_menu_args' ) ) :
/**
 * Get our wp_nav_menu() fallback, bp_dtheme_main_nav(), to show a home link.
 *
 * @param array $args Default values for wp_page_menu()
 * @see wp_page_menu()
 * @since BuddyPress 1.5
 */
function bp_dtheme_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'bp_dtheme_page_menu_args' );
endif;

if ( !function_exists( 'bp_dtheme_comment_form' ) ) :
/**
 * Applies BuddyPress customisations to the post comment form.
 *
 * @global string $user_identity The display name of the user
 * @param array $default_labels The default options for strings, fields etc in the form
 * @see comment_form()
 * @since BuddyPress 1.5
 */
function bp_dtheme_comment_form( $default_labels ) {
	global $user_identity;

	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$fields =  array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name', 'buddypress' ) . ( $req ? '<span class="required"> *</span>' : '' ) . '</label> ' .
		            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email', 'buddypress' ) . ( $req ? '<span class="required"> *</span>' : '' ) . '</label> ' .
		            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website', 'buddypress' ) . '</label>' .
		            '<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);

	$new_labels = array(
		'comment_field'  => '<p class="form-textarea"><textarea name="comment" id="comment" cols="60" rows="10" aria-required="true"></textarea></p>',
		'fields'         => apply_filters( 'comment_form_default_fields', $fields ),
		'logged_in_as'   => '',
		'must_log_in'    => '<p class="alert">' . sprintf( __( 'You must be <a href="%1$s">logged in</a> to post a comment.', 'buddypress' ), wp_login_url( get_permalink() ) )	. '</p>',
		'title_reply'    => __( 'Leave a reply', 'buddypress' )
	);

	return apply_filters( 'bp_dtheme_comment_form', array_merge( $default_labels, $new_labels ) );
}
add_filter( 'comment_form_defaults', 'bp_dtheme_comment_form', 10 );
endif;

if ( !function_exists( 'bp_dtheme_before_comment_form' ) ) :
/**
 * Adds the user's avatar before the comment form box.
 *
 * The 'comment_form_top' action is used to insert our HTML within <div id="reply">
 * so that the nested comments comment-reply javascript moves the entirety of the comment reply area.
 *
 * @see comment_form()
 * @since BuddyPress 1.5
 */
function bp_dtheme_before_comment_form() {
?>
	<div class="comment-avatar-box">
		<div class="avb">
			<?php if ( bp_loggedin_user_id() ) : ?>
				<a href="<?php echo bp_loggedin_user_domain() ?>">
					<?php echo get_avatar( bp_loggedin_user_id(), 50 ) ?>
				</a>
			<?php else : ?>
				<?php echo get_avatar( 0, 50 ) ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="comment-content standard-form">
<?php
}
add_action( 'comment_form_top', 'bp_dtheme_before_comment_form' );
endif;

if ( !function_exists( 'bp_dtheme_after_comment_form' ) ) :
/**
 * Closes tags opened in bp_dtheme_before_comment_form().
 *
 * @see bp_dtheme_before_comment_form()
 * @see comment_form()
 * @since BuddyPress 1.5
 */
function bp_dtheme_after_comment_form() {
?>

	</div><!-- .comment-content standard-form -->

<?php
}
add_action( 'comment_form', 'bp_dtheme_after_comment_form' );
endif;

if ( !function_exists( 'bp_dtheme_sidebar_login_redirect_to' ) ) :
/**
 * Adds a hidden "redirect_to" input field to the sidebar login form.
 *
 * @since BuddyPress 1.5
 */
function bp_dtheme_sidebar_login_redirect_to() {
	$redirect_to = apply_filters( 'bp_no_access_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );
?>
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
<?php
}
add_action( 'bp_sidebar_login_form', 'bp_dtheme_sidebar_login_redirect_to' );
endif;

/**
 * Display navigation to next/previous pages when applicable
 *
 * @global unknown $wp_query
 * @param string $nav_id DOM ID for this navigation
 * @since BuddyPress 1.5
 */
function bp_dtheme_content_nav( $nav_id ) {
	global $wp_query;

	if ( $wp_query->max_num_pages > 1 ) : ?>
		<div id="<?php echo $nav_id; ?>" class="navigation">
			<div class="alignleft"><?php next_posts_link( __( '&larr; Previous Entries', 'buddypress' ) ); ?></div>
			<div class="alignright"><?php previous_posts_link( __( 'Next Entries &rarr;', 'buddypress' ) ); ?></div>
		</div><!-- #<?php echo $nav_id; ?> -->
	<?php endif;
}


/****************************** BUDDYBOSS FUNCTIONS ******************************/

/**
 * Initialize BuddBoss
 *
 * @since 2.0
 */
function buddy_boss_init()
{
	global $bboss_pics_img_size;
	
	// Add theme support for logo uploading and post thumbnails
	if ( function_exists( 'add_theme_support' ) )
	{
		add_theme_support( 'post-thumbnails' );
	  set_post_thumbnail_size( 150, 150 ); // default Post Thumbnail dimensions   
	}
	
	// Add our default sizes
	if ( function_exists( 'add_image_size' ) )
	{ 
		add_image_size( 'buddyboss_pic_tn', 150, 150, true );
		add_image_size( 'buddyboss_pic_med', 548, 9999 );
		add_image_size( 'buddyboss_pic_wide', 795, 9999 );
	}
	$bboss_pics_img_size = is_active_sidebar( 'Profile' ) ? 'buddyboss_pic_med' : 'buddyboss_pic_wide';
}
add_action( 'after_setup_theme', 'buddy_boss_init' );

/**
 * First run for theme, setup basic options
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_theme_activate() {
	add_option( 'buddy_boss_wall_on', 1 );
	add_option( 'buddy_boss_pics_on', 1 );
}
wp_register_theme_activation_hook('buddyboss2', 'buddyboss_theme_activate');

function buddyboss_theme_deactivate() {
	
}
wp_register_theme_deactivation_hook('buddyboss2', 'buddyboss_theme_deactivate'); 

/**
 * Check if the current user is on a friend's profile page
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_is_admin()
{
	return is_user_logged_in() && current_user_can( 'administrator' );
}

/**
 * Check if the current profile a user is on is a friend or not
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_is_my_friend( $id=null )
{
	global $bp;
	if ( $id === null ) $id = $bp->displayed_user->id;
	return 'is_friend' == BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $id );
}

/**
 * Get users by role
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_users_by_role( $role )
{
	$user_ids = get_transient( 'bb_user_ids_'.$role );
	
	if ( !$user_ids )
	{
		if ( class_exists( 'WP_User_Search' ) ) {
			$wp_user_search = new WP_User_Search( '', '', $role );
			$user_ids = $wp_user_search->get_results();
		} else {
			global $wpdb;
			$user_ids = $wpdb->get_col('
			SELECT ID
			FROM '.$wpdb->users.' INNER JOIN '.$wpdb->usermeta.'
			ON '.$wpdb->users.'.ID = '.$wpdb->usermeta.'.user_id
			WHERE '.$wpdb->usermeta.'.meta_key = \''.$wpdb->prefix.'capabilities\'
			AND '.$wpdb->usermeta.'.meta_value LIKE \'%"'.$role.'"%\'
			');
		}
		set_transient( 'bb_user_ids_'.$role, $user_ids, 3600 );
	}
	return $user_ids;
}  
/**
 * Stop the core buddybar.css from loading
 *
 * @since BuddyBoss 2.0
 */
if (!is_admin())
{
	wp_deregister_style( 'bp-admin-bar' );
	if ( function_exists( 'show_admin_bar' ) )
	{
		show_admin_bar( false );
		remove_action( 'bp_init', 'bp_core_load_buddybar_css' );
	}
}


/**
 * Remove Visit menu from admin bar
 *
 * @since BuddyBoss 1.0
 */
remove_action( 'bp_adminbar_menus', 'bp_adminbar_random_menu', 100 );


/**
 * Replace default member avatar
 *
 * @since BuddyBoss 2.0
 */
if ( !function_exists('fb_addgravatar') ) {
	function fb_addgravatar( $avatar_defaults ) {
		$myavatar = get_bloginfo('template_directory') . '/_inc/images/avatar-member.jpg';
		$avatar_defaults[$myavatar] = 'BuddyBoss Man';
		return $avatar_defaults;
	}
	add_filter( 'avatar_defaults', 'fb_addgravatar' );
}
 
/**
 * Replace default group avatar 
 *
 * @since BuddyBoss 1.0
 */
function my_default_get_group_avatar($avatar) {

global $bp, $groups_template;
if( strpos($avatar,'group-avatars') ) {
return $avatar;
}

else {
$custom_avatar = get_stylesheet_directory_uri() .'/_inc/images/avatar-group.jpg';

if($bp->current_action == "")
return '<img width="'.BP_AVATAR_THUMB_WIDTH.'" height="'.BP_AVATAR_THUMB_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
else
return '<img width="'.BP_AVATAR_FULL_WIDTH.'" height="'.BP_AVATAR_FULL_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
}
}
add_filter( 'bp_get_group_avatar', 'my_default_get_group_avatar');


/**
 * Custom Login Logo 
 *
 * @since BuddyBoss 1.0
 */
function my_custom_login_logo() {
	
	$logo = (get_option("buddy_boss_custom_logo", FALSE) == FALSE) ? get_bloginfo('template_directory').'/_inc/images/logo.jpg' : get_option("buddy_boss_custom_logo");
    echo '<style type="text/css">
        #login h1 a { background:url('.$logo.') top center no-repeat !important; width: 500px; height: 164px; margin: 0 -90px; }
        html { background-color: #fff !important; }
    </style>';
}
add_action('login_head', 'my_custom_login_logo');


/**
 * Custom Login Link 
 *
 * @since BuddyBoss 1.0.8
 */
function change_wp_login_url() {
echo bloginfo('url');
}
function change_wp_login_title() {
echo get_option('blogname');
}
add_filter('login_headerurl', 'change_wp_login_url');
add_filter('login_headertitle', 'change_wp_login_title');


/**
 * Pics Component
 *
 * @since BuddyBoss 2.0
 */
$module_path = realpath(dirname(__FILE__)."/buddy_boss_pics.php");
if (file_exists($module_path))
{
	 include_once($module_path);
	 
}
if (BUDDY_BOSS_PICS_ENABLED) $buddy_boss_pics = $bbpics = new BuddyBoss_Pics();


/**
 * Wall Component
 *
 * @since BuddyBoss 1.0.6
 */
$module_path = realpath(dirname(__FILE__)."/buddy_boss_wall.php");
if (file_exists($module_path))
{
	 include_once($module_path);
	 
}
if (BUDDY_BOSS_WALL_ENABLED) $buddy_boss_wall = $bbwall = new BuddyBoss_Wall();


/**
 * Admin Options 
 *
 * @since BuddyBoss 1.0.6
 */
$module_path = realpath(dirname(__FILE__)."/admin_options.php");
if (file_exists($module_path))
{
	 include_once($module_path);
}


/**
 * Improved Post Excerpt 
 *
 * @since BuddyBoss 1.0.9
 */
function improved_trim_excerpt($text) {
	global $post;
	if ( '' == $text ) {
		$text = get_the_content('');
		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
		$text = strip_tags($text, '<p><a><br /><ul><ol><li>');
		$excerpt_length = apply_filters('excerpt_length', 80);
		$words = explode(' ', $text, $excerpt_length + 1);
		if (count($words)> $excerpt_length) {
			array_pop($words);
			array_push($words, '&nbsp;<span class="readmore"><a href="'. get_permalink($post->ID) . '">' . '[read more &rarr;]' . '</a></span>');
			$text = implode(' ', $words);
		}
	}
	return $text;
}
remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'improved_trim_excerpt');


/**
 * Register theme function
 *
 * @desc registers a theme activation hook
 * @param string $code : Code of the theme. This can be the base folder of your theme. Eg if your theme is in folder 'mytheme' then code will be 'mytheme'
 * @param callback $function : Function to call when theme gets activated.
 */
function wp_register_theme_activation_hook( $code, $function )
{
	$option_key = "theme_is_activated_" . $code;
  if( !get_option( $option_key ) )
  {
  	call_user_func($function);
  	update_option($option_key , 1);
  }
}


/**
 * Deregister theme function
 *
 * @desc registers deactivation hook
 * @param string $code : Code of the theme. This must match the value you provided in wp_register_theme_activation_hook function as $code
 * @param callback $function : Function to call when theme gets deactivated.
 * @since BuddyBoss 2.0
 */
function wp_register_theme_deactivation_hook( $code, $function )
{
	// store function in code specific global
	$GLOBALS["wp_register_theme_deactivation_hook_function" . $code]=$function;
	
	// create a runtime function which will delete the option set while activation of this theme and will call deactivation function provided in $function
	$fn=create_function('$theme', ' call_user_func($GLOBALS["wp_register_theme_deactivation_hook_function' . $code . '"]); delete_option("theme_is_activated_' . $code. '");');
	
	// add above created function to switch_theme action hook. This hook gets called when admin changes the theme.
	// Due to wordpress core implementation this hook can only be received by currently active theme (which is going to be deactivated as admin has chosen another one.
	// Your theme can perceive this hook as a deactivation hook.
	add_action("switch_theme", $fn);
}


/**
 * Add extra formatting buttons to TinyMCE
 *
 * @since BuddyBoss 2.0
 */
function enable_more_buttons($buttons) {
  $buttons[] = 'hr';
  $buttons[] = 'removeformat';
  $buttons[] = 'fontselect';
 
  return $buttons;
}
add_filter("mce_buttons", "enable_more_buttons");


/**
 * Added function bb_is_plugin_active
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_is_plugin_active( $plugin ) {
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}


/**
 * DEBUG Functions
 *
 * @since BuddyBoss 2.0
 */
function list_hooked_functions($tag=false){
 global $wp_filter;
 if ($tag) {
  $hook[$tag]=$wp_filter[$tag];
  if (!is_array($hook[$tag])) {
  trigger_error("Nothing found for '$tag' hook", E_USER_WARNING);
  return;
  }
 }
 else {
  $hook=$wp_filter;
  ksort($hook);
 }
 echo '<pre>';
 foreach($hook as $tag => $priority){
  echo "<br />&gt;&gt;&gt;&gt;&gt;\t<strong>$tag</strong><br />";
  ksort($priority);
  foreach($priority as $priority => $function){
  echo $priority;
  foreach($function as $name => $properties) echo "\t$name<br />";
  }
 }
 echo '</pre>';
 return;
}


/* log to screen if in debug mode */
function buddy_boss_log($msg)
{
	global $buddy_boss_debug_log;
	if (!BUDDY_BOSS_DEBUG) return;

	if (is_array($msg) || is_object($msg))
	{
		$buddy_boss_debug_log .= " <li> <pre>".print_r($msg, true)."</pre> </li>";
	}
	else
	{
		$buddy_boss_debug_log.="<li>".$msg. "<li>";
	}

}

function buddy_boss_dump_log()
{
	if (!BUDDY_BOSS_DEBUG) return ;
	global $buddy_boss_debug_log;

	echo "<h2> DEBUG LOG </h2>";
	echo "<ul>". $buddy_boss_debug_log."</ul>";
}

add_filter( 'template_include', 'var_template_include', 1000 );
function var_template_include( $t ){
    $GLOBALS['current_theme_template'] = basename($t);
    return $t;
}

function get_current_template( $echo = false ) {
    if( !isset( $GLOBALS['current_theme_template'] ) )
        return false;
    if( $echo )
        echo $GLOBALS['current_theme_template'];
    else
        return $GLOBALS['current_theme_template'];
}
?>