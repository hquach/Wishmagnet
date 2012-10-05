<?php

	/**
	* buddy_boss_pics is a BuddyPress plugin combining user activity feeds with photo uploading
	*
	* @author BuddyBoss
	* @since	BuddyBoss 2.0
	*/
	
	// Indicate whether the Wall module is enabled
	// Check if the friends module is enabled if not disable the wall
	if (function_exists('friends_get_alphabetically'))
	{
		$option = get_option("buddy_boss_pics_on");
		define(BUDDY_BOSS_PICS_ENABLED, $option);
	}
	else
	{
		define(BUDDY_BOSS_PICS_ENABLED, FALSE);
	}
	
	// Indicate whether to show debug msgs on screen
	if ( !defined('BUDDY_BOSS_DEBUG') )
		define(BUDDY_BOSS_DEBUG, false);
	
	// Indicate whether to show debug msgs on screen
	if ( !defined('BUDDY_BOSS_PICS_SLUG') )
		define(BUDDY_BOSS_PICS_SLUG, 'pictures');
	
	// a variable to hold the event log
	$buddy_boss_debug_log = "";
	
	
	// DEFAULT CONFIGURATION OPTIONS
	$buddy_boss_pics_defaults = array(
		"MENU_NAME"							=> __( 'Photos' , 'buddyboss' )
	);
	
	class BuddyBoss_Pics
	{
		/**
		* BUDDYPRESS VARIABLES
		*
		* @since BuddyBoss 2.0
		*/
	
		/**
		* OPTIONS
		*
		* @since BuddyBoss 2.0
		*/
		private $options;
		
		/**
		 * SHOW INLINE COMMENTS PIC PAGE
		 *
		 * @since BuddyBoss 2.0
		 */
		public $redirect_single = false;
		public $show_single = false;
		
		/**
		 * PICTURE GRID TEMPLATE VARIABLS
		 *
		 * @since BuddyBoss 2.0
		 */
		public $grid_has_pics = false;
		public $grid_num_pics = 0;
		public $grid_current_pic = null;
		public $grid_pic_index = 0;
		public $grid_data = array();
		public $grid_html = null;
		public $grid_has_run = false;
		public $grid_pagination = null;
		public $grid_num_pages = 0;
		public $grid_current_page = 1;
		public $grid_pics_per_page = 9;
		
		/**
		* STORAGE
		*
		* @since BuddyBoss 2.0
		*/
		public $cache;
	
		/**
		* INITIALIZE CLASS
		*
		* @since BuddyBoss 2.0
		*/
		function __construct($options = null)
		{
			global $buddy_boss_pics_defaults,  $activity_template;
	
			if (isset($options) && $options !=null)
			{
				$this->options = $options;
			}
			else
			{
				$this->options = $buddy_boss_pics_defaults;
				buddy_boss_log("PICS Using default config");
			}
	
			// Log
			buddy_boss_log($this->options);
	
			// Add body class
			add_filter( 'body_class', array($this, 'add_body_class') );
	
			// Caching
			$this->cache = get_transient('bbpics_cacher');
			add_action( 'wp_shutdown', array($this, 'shutdown') );
			
			// Globals
			add_action( 'bp_setup_globals',  array( $this, 'setup_globals' ) );
			
			// Menu
			add_action( 'bp_setup_nav', array( $this, 'setup_menu' ) );
			
			// Add a query string to show inline content for single pictures
			$this->redirect_single = (  ( isset( $_GET['buddyboss_ajax_pic'] ) && $_GET['buddyboss_ajax_pic'] === 'true' ) );
			
			if ( $this->redirect_single === true )
			{
				add_filter( 'bp_activity_permalink_redirect_url', array( $this, 'single_pic_uri' ) );
			
			}
			
			// Show single picture without header or footer for inline lightbox
			$this->show_single = (  ( isset( $_GET['buddyboss_ajax_pic_page'] ) && $_GET['buddyboss_ajax_pic_page'] === 'true' ) );
			
			if ( $this->show_single === true )
			{
				add_filter( 'bp_activity_template_profile_activity_permalink', array( $this, 'single_pic_template' ) );
			}
			
			return $this;
		}
		
		/**
		 * SETUP BUDDYPRESS GLOBAL OPTIONS
		 *
		 * @since	BuddyBoss 2.0
		 */
		function setup_globals()
		{
			$bp->active_components[BUDDY_BOSS_PICS_SLUG] = 'buddyboss-pics'; 
		}
		
		/**
		 * SETUP MENU, ADD NAVIGATION OPTIONS
		 *
		 * @since	BuddyBoss 2.0
		 * @todo: cache the amount of pics
		 */
		function setup_menu()
		{
			global $wpdb, $bp;
			
			$photos_user_id = $bp->displayed_user->id;
			$activity_table = $wpdb->prefix."bp_activity";
			$activity_meta_table = $wpdb->prefix."bp_activity_meta";
			
			// Prepare a SQL query to retrieve the activity posts 
			// that have pictures associated with them
			$sql = "SELECT COUNT(*) as photo_count FROM $activity_table a INNER JOIN $activity_meta_table am ON a.id = am.activity_id WHERE a.user_id = %d AND meta_key = 'bboss_pics_aid'";
			$sql = $wpdb->prepare( $sql, $photos_user_id );
			
			buddy_boss_log( ' MENU PHOTO COUNT SQL ' );
			buddy_boss_log( $sql );
			$photos_cnt = $wpdb->get_var( $sql );

			/* Add 'Photos' to the main user profile navigation */
			bp_core_new_nav_item( array(
				'name' => sprintf( __( 'Photos <span>%d</span>', 'buddyboss' ), $photos_cnt),
				'slug' => BUDDY_BOSS_PICS_SLUG,
				'position' => 80,
				'screen_function' => 'buddyboss_pics_screen_picture_grid',
				'default_subnav_slug' => 'my-gallery'
			) );
		
			$buddyboss_pics_link = $bp->displayed_user->domain . BUDDY_BOSS_PICS_SLUG . '/';
		
			bp_core_new_subnav_item( array(
				'name' => __( 'Photos', 'buddypress' ),
				'slug' => 'my-gallery',
				'parent_slug' => BUDDY_BOSS_PICS_SLUG,
				'parent_url' => $buddyboss_pics_link,
				'screen_function' => 'buddyboss_pics_screen_picture_grid',
				'position' => 10
			) );
		}
	
		/**
		* SAVES CACHE @ WP SHUTDOWN
		*
		* @since BuddyBoss 1.5
		*/
		function shutdown()
		{
			set_transient('bbpics_cacher', $this->cache);
		}
	
		/**
		* GET OPTION
		*
		* @since BuddyBoss 1.5
		*/
		function get_option($name)
		{
			if (isset($this->options[$name])) return $this->options[$name];
			return false;
		}
	
		/**
		* Add active wall class
		*
		* @since BuddyBoss 2.0
		*/
		function add_body_class( $classes )
		{
			$classes[] = 'buddyboss-active-pics';
			return $classes;
		}
		
		/**
		 * Filter current redirect URL to add single picture comment/view query string
		 *
		 * @since BuddyBoss 2.0
		 */
		function single_pic_uri( $orig )
		{
			return rtrim( $orig, '/' ) . '/?buddyboss_ajax_pic_page=true';
		}
		
		/**
		 * Filter current template file to use header and footer free ajax template
		 *
		 * @since BuddyBoss 2.0
		 */
		function single_pic_template( $tpl )
		{
			return '/members/single/activity/permalink-ajax';
		}

	} // end of BUDDY_BOSS_PICS class
	
	/**
	 * Generate content for our picture grid
	 *
	 * @since BuddyBoss 2.0
	 * @todo	Update the theme file (members/single/pictures.php) and create a Wordpress like loop for the images 
	 					e.g. 
	 					<?php if ( buddyboss_picgrid_has_pics() ): while( buddyboss_picgrid_has_pics() ): ?>
	 						<?php buddyboss_picgrid_thumbnail(); ?>
	 						- and -
	 						<a href="<?php buddyboss_picgrid_fullsize_url(); ?>" title="<?php buddyboss_picgrid_image_title(); ?>">
	 							<img src="<?php buddyboss_picgrid_thumbnail_url(); ?>" width="<?php buddyboss_picgrid_thumbnail_width(); ?>" height="<?php buddyboss_picgrid_thumbnail_height(); ?>" />
	 						</a>
	 					<?php endwhile; endif; ?>
	 					
	 					
	 					(need to rename these for clarity, I think they're too long (JP))
	 					
	 					* functions to create:
	 					
	 					buddyboss_picgrid_has_pics()							For the if/while Wordpress style loop
	 					buddyboss_picgrid_attachment_id()					Returns the ID of the current image
	 					buddyboss_picgrid_thumbnail()							Echo '<li><a><img>' tags for you of the current thumbnail
	 					buddyboss_picgrid_thumbnail_url()					Echos the url location of the current thumbnail
	 					get_buddyboss_picgrid_thumbnail_url()			Returns the url location of the current thumbnail
	 					buddyboss_picgrid_thumbnail_width()				Echos the current thumbnail width
	 					get_buddyboss_picgrid_thumbnail_width()		Returns the current thumbnail width
	 					buddyboss_picgrid_thumbnail_height()			Echos the current thumbnail height
	 					get_buddyboss_picgrid_thumbnail_height()	Returns the current thumbnail height
	 					buddyboss_picgrid_fullsize_url()					Echos the url location of the current full size image
	 					get_buddyboss_picgrid_fullsize_url()			Returns the url location of the current thumbnail
	 					buddyboss_picgrid_fullsize_width()				Echos the current full size image width
	 					get_buddyboss_picgrid_fullsize_width()		Returns the current full size image width
	 					buddyboss_picgrid_fullsize_height()				Echos the current full size image height
	 					get_buddyboss_picgrid_fullsize_height()		Returns the current full size image height
	 					
	 */
	function buddyboss_pics_screen_picture_grid_content()
	{
		global $bp, $wpdb, $bbpics;
		
		$wpdb->show_errors = BUDDY_BOSS_DEBUG;
		
		$img_size = is_active_sidebar( 'Profile' ) ? 'buddyboss_pic_med' : 'buddyboss_pic_wide';
		
		$gallery_class = is_active_sidebar( 'Profile' ) ? 'gallery has-sidebar' : 'gallery';
		
		$user_id = $bp->displayed_user->id;
		$activity_table = $wpdb->prefix."bp_activity";
		$activity_meta_table = $wpdb->prefix."bp_activity_meta";
		
		$pages_sql = "SELECT COUNT(*) FROM $activity_table a INNER JOIN $activity_meta_table am ON a.id = am.activity_id WHERE a.user_id = $user_id AND meta_key = 'bboss_pics_aid'";
		
		$bbpics->grid_num_pics = $wpdb->get_var($pages_sql);
		
		$bbpics->grid_current_page = isset( $_GET['page'] ) ? (int) $_GET['page'] : 1;
		
		// Prepare a SQL query to retrieve the activity posts 
		// that have pictures associated with them
		$sql = "SELECT a.*, am.meta_value FROM $activity_table a INNER JOIN $activity_meta_table am ON a.id = am.activity_id WHERE a.user_id = $user_id AND meta_key = 'bboss_pics_aid' ORDER BY a.date_recorded DESC";
		
		buddy_boss_log("SQL: $sql");
		
		$pics  = $wpdb->get_results($sql,ARRAY_A);
		
		$bbpics->grid_pagination = new BuddyBoss_Paginated( $pics, $bbpics->grid_pics_per_page, $bbpics->grid_current_page );
		
		buddy_boss_log("RESULT: $pics");
		
		// If we have results let's print out a simple grid
		if ( !empty( $pics ) )
		{
			$bbpics->grid_had_pics = true;
			$bbpics->grid_num_pics = count( $pics );
			
			/**
			 * DEBUG
			 *
			echo '<br/><br/><div style="display:block;background:#f0f0f0;border:2px solid #ccc;margin:20px;padding:15px;color:#333;"><pre>';
			var_dump( $pics );
			echo '</pre></div><hr/><br/><br/><br/><br/>';
			die;
			/**/
			
			$html_grid = '<ul class="'.$gallery_class.'" id="buddyboss-pics-grid">'."\n";
			
			foreach( $pics as $pic )
			{
				/**
				 * DEBUG
				 *
				echo '<br/><br/><div style="display:block;background:#f0f0f0;border:2px solid #ccc;margin:20px;padding:15px;color:#333;"><pre>';
				var_dump( bp_activity_get_permalink($pic['id']), $pic );
				echo '</pre></div><hr/><br/><br/><br/><br/>';
				die;
				/**/
				
				$attachment_id = isset($pic['meta_value']) ? (int)$pic['meta_value'] : 0;
				
				// Make sure we have a valid attachment ID
				if ( $attachment_id > 0 )
				{
					// Let's get the permalink of this attachment to show within a lightbox
					$permalink = bp_activity_get_permalink( $pic[ 'id' ] );
					$ajax_link = rtrim( $permalink, '/' ) . '/?buddyboss_ajax_pic=true';
					
					// Grab the image details
					$image = wp_get_attachment_image_src( $attachment_id, $img_size );
					
					// grab the thumbnail details
					$tn = wp_get_attachment_image_src( $attachment_id, 'buddyboss_pic_tn' );
					
					if ( is_array($tn) && !empty($tn) && isset($tn[0]) && $tn[0] != '' )
					{
						$bbpics->grid_data[] = array(
							'attachment'	=> $attachment_id,
							'image'				=> $image,
							'tn'					=> $tn,
							'permalink'		=> $permalink,
							'ajaxlink'		=> $ajax_link
						);
						
						$html_grid .= '<li class="gallery-item"><div><a rel="gal_item" href="' . $ajax_link . '"><img src="'.$tn[0].'" width="'.$tn[1].'" height="'.$tn[2].'" /></a></div></li>'."\n";
					}
				}
			}
			
			$html_grid .= '</ul>'."\n\n";
			
			$bbpics->grid_html = $html_grid;
			
			$bbpics->grid_has_pics = true;
		}
		else {
			$bbpics->grid_has_pics = false;
			$bbpics->grid_num_pics = 0;
			$bbpics->grid_current_pic = null;
			$bbpics->grid_data = array();
			$bbpics->grid_html = null;
		}
	}
	
	/**
	 * Show the grid of uploaded pictures for a user
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_pics_screen_picture_grid()
	{		
		add_action( 'bp_template_content', 'buddyboss_pics_screen_picture_grid_content' );
		
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/pictures' ) );
	}
	$ii = 0;
	/**
	 * Check if a picture grid has pictures
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_has_pics()
	{
		global $bbpics, $ii;
		$ii++; if ( $ii > 25 ) return false;
		if ( $bbpics->grid_has_run === false )
		{
			buddyboss_pics_screen_picture_grid_content();
			$bbpics->grid_has_run = true;
			return $bbpics->grid_has_pics;
		}
		
		if ( $bbpics->grid_has_pics === true )
		{
			if ( $bbpics->grid_has_run === true )
			{
				if ( $bbpics->grid_num_pics < $bbpics->grid_pic_index )
				{
					return false;
				}
			}
			
			
			$bbpics->grid_current_pic = $bbpics->grid_pagination->fetchPagedRow();

			if ( $bbpics->grid_current_pic === false )
			{
				return false;
			}

		}

		return $bbpics->grid_has_pics;
	}
	
	/**
	 * Handles the enxt picture in the loop
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_the_pic()
	{
		global $bbpics;
		
		buddyboss_setup_next_pic();
	}
	
	/**
	 * Setup the next picture
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_setup_next_pic()
	{
		global $bbpics;
		
		++$bbpics->grid_pic_index;
	}
	
	/**
	 * buddyboss_pics_html_grid
	 * buddyboss_pic_attachment_id
	 * buddyboss_pic_image
	 * buddyboss_pic_tn
	 * buddyboss_pic_permalink
	 */
	function get_buddyboss_pics_html_grid()
	{
		return $bbpics->html_grid;
	}
	function get_buddyboss_pic_attachment_id()
	{
		global $bbpics;
		return $bbpics->grid_data[$bbpics->grid_current_pic]['attachment_id'];
	}
	function get_buddyboss_pic_image()
	{
		global $bbpics;
		return $bbpics->grid_data[$bbpics->grid_current_pic]['image'];
	}
	function get_buddyboss_pic_tn()
	{
		global $bbpics;
		return $bbpics->grid_data[$bbpics->grid_current_pic]['tn'];
	}
	function get_buddyboss_pic_permalink()
	{
		global $bbpics;
		return $bbpics->grid_data[$bbpics->grid_current_pic]['permalink'];
	}
	function get_buddyboss_pic_ajaxlink()
	{
		global $bbpics;               
		return $bbpics->grid_data[$bbpics->grid_current_pic]['ajaxlink'];
	}
	function buddyboss_pics_pagination()
	{
		global $bbpics;
		
		echo $bbpics->grid_pagination->fetchPagedNavigation();
	}

	/* FILTERS */
	if (BUDDY_BOSS_PICS_ENABLED)
	{
		add_filter('bp_activity_after_save', 'buddyboss_pics_input_filter' );
		add_filter('bp_get_activity_action', 'buddyboss_pics_read_activity_filter');
		add_filter('bp_get_activity_content_body', 'buddyboss_pics_read_content_filter');
	}
	else {
		add_filter('bp_get_activity_content_body', 'buddyboss_pics_off_read_content_filter');
	}
	
	/**
	 * This will save some data to the activity meta table when someone posts a picture
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_pics_input_filter(&$activity)
	{
		global $bp, $buddy_boss_wall;
		
		$user = $bp->loggedin_user;
		$new_action = $result = false;
		
		if ( strstr( $activity->content, 'class="buddyboss-pics-picture-link"' ) !== false && isset($_POST['has_pic']) && isset($_POST['has_pic']['attachment_id']) )
		{
			$action  = '<a href="'.$user->domain.'">'.$user->fullname.'</a> ' . __('posted a new picture','buddyboss');
			bp_activity_update_meta( $activity->id, 'bboss_pics_action', $action );
			bp_activity_update_meta( $activity->id, 'bboss_pics_aid', $_POST['has_pic']['attachment_id'] );
		}
	}
	
	/**
	 * This filters pics actions, when reading an item it will convert it to use pics language
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_pics_read_activity_filter($action)
	{
		global $activities_template;
		$curr_id = $activities_template->current_activity;
		$act_id = $activities_template->activities[$curr_id]->id;
		$bbpics_action = bp_activity_get_meta( $act_id, 'bboss_pics_action' );
		return ($bbpics_action) ? $bbpics_action : $action;

	}

	/**
	 * This filters pics content, when reading an item it will convert it to use pics language
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_pics_read_content_filter($filter)
	{
		global $bboss_pics_img_size, $activities_template;
		$curr_id = $activities_template->current_activity;
		$act_id = $activities_template->activities[$curr_id]->id;
		$bbpics_aid = bp_activity_get_meta( $act_id, 'bboss_pics_aid' );
		$image = wp_get_attachment_image_src( $bbpics_aid, $bboss_pics_img_size );
		if ( $image !== false && is_array( $image ) && count( $image ) > 2 )
		{
			$src = $image[0];
			$w = $image[1];
			$h = $image[2];
			$filter .= "<img class='buddyboss-pics-picture' src='$src' width='$w' height='$h' />";
		}

		return $filter;
	}

	/**
	 * This filters pics content when off, when reading an item it will convert it to the image filename
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddyboss_pics_off_read_content_filter($filter)
	{
		global $bboss_pics_img_size, $activities_template;
		$curr_id = $activities_template->current_activity;
		$act_id = $activities_template->activities[$curr_id]->id;
		$bbpics_aid = bp_activity_get_meta( $act_id, 'bboss_pics_aid' );
		$image = wp_get_attachment_image_src( $bbpics_aid, 'full' );
		if ( $image !== false && is_array( $image ) && count( $image ) > 2 )
		{
			$src = $image[0];
			$w = $image[1];
			$h = $image[2];
			$filter .= '<a href="'. $image[0] .'" target="_blank">'. basename( $image[0] ) .'</a>';
		}

		return $filter;
	}
	
	buddy_boss_pics_cleanup_db();
	/**
	 * CLEANUP DATABASE AND RECONCILE WITH WP MEDIA LIBRARY
	 */
	function buddy_boss_pics_cleanup_db()
	{
		global $wpdb;
		
		$activity_table = $wpdb->prefix."bp_activity";
		$activity_meta_table = $wpdb->prefix."bp_activity_meta";
		$posts_table = $wpdb->prefix."posts";
		
		// Prepare a SQL query to retrieve the activity posts 
		// that have pictures associated with them
		$all_aids_sql = "SELECT am.meta_value, am.activity_id FROM $activity_table a 
										 INNER JOIN $activity_meta_table am ON a.id = am.activity_id 
										 WHERE am.meta_key = 'bboss_pics_aid'";
		
		// Now perpare a SQL query to retrieve all attachments
		// that are BuddyBoss wall pictures AND are published in the media library
		$existing_sql = "SELECT am.meta_value FROM $activity_table a 
										 INNER JOIN $activity_meta_table am ON a.id = am.activity_id 
										 INNER JOIN $posts_table p ON am.meta_value = p.ID 
										 WHERE am.meta_key = 'bboss_pics_aid'
										 AND p.post_status = 'inherit'
										 AND p.post_parent = 0";
		
		// Query the database for all attachment IDS
		$all_aids = (array) $wpdb->get_results( $all_aids_sql, ARRAY_A );
		
		// Query the database for all pics in the media library that are BuddyBoss pics
		$existing_aids = (array) $wpdb->get_col( $existing_sql );
		
		// If we have a result set
		if ( !empty( $all_aids ) )
		{
			// Prepare array
			$attachment_ids = $activity_ids = $aids2activity = array();
			foreach ( $all_aids as $aid )
			{
				$attachment_ids[] = $aid['meta_value'];
				$aids2activity[ $aid['meta_value'] ] = $activity_ids[] = $aid['activity_id'];
			}
			
			// Lets get the difference of our published vs. orphaned IDs
			$orphans = array_diff( $attachment_ids, $existing_aids );
			
			// Delete related activity stream posts
			if ( !empty( $orphans ) )
			{
				$orphan_acitivity_ids = array();
				
				foreach ( $orphans as $orphan )
				{
					if ( isset( $aids2activity[ $orphan ] ) )
					{
						$orphan_acitivity_ids[] = $aids2activity[ $orphan ];
					}
				}
				
				$orphan_acitivity_ids_string = implode( ',', $orphan_acitivity_ids );
				
				$sql = $wpdb->prepare( "DELETE FROM $activity_table WHERE id IN ($orphan_acitivity_ids_string)" );
				
				$deleted = $wpdb->query( $sql );
				
				BP_Activity_Activity::delete_activity_item_comments( $orphan_acitivity_ids );
				BP_Activity_Activity::delete_activity_meta_entries( $orphan_acitivity_ids );
			}
		}
	}
	
	/**
	 * HOOK INTO MEDIA LIBRARY ON ITEM DELETE
	 */
	function buddy_boss_after_attachment_deleted()
	{
		add_action( 'clean_post_cache', 'buddy_boss_pics_cleanup_db' );
	}
	add_action( 'delete_attachment', 'buddy_boss_after_attachment_deleted' );
	
	
	/**
	* ACTIVATION AND DEACTIVATION CODE
	*/
	function buddy_boss_pics_on_activate()
	{
		buddy_boss_pics_cleanup_db();
		
		return 'The Picture Gallery was activated successfully';
	}
	
	
	/**
	* Deregister the BuddyBoss Pics Component
	*
	* @since BuddyBoss 2.0
	*/
	function buddy_boss_pics_on_deactivate()
	{
		buddy_boss_pics_cleanup_db();
		
		return '';
	}
	
	function buddyboss_pics_upload_dir( $filter )
	{
		return $filter;
	}
	
	// AJAX update picture
	function buddyboss_post_picture() {
		global $bp;
		// Check the nonce
		check_admin_referer( 'post_update', '_wpnonce_post_update' );
	
		if ( !is_user_logged_in() ) {
			echo '-1';
			return false;
		}
		
		include( dirname( __FILE__ ) . '/_inc/upload.php' );
		
		if (!function_exists('wp_generate_attachment_metadata')){
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		}
		if (!function_exists('media_handle_upload')){
			require_once(ABSPATH . 'wp-admin/includes/admin.php');
		}
		
		add_filter( 'upload_dir', 'buddyboss_pics_upload_dir' );		
		$aid = media_handle_upload( 'qqfile', 0 );
		remove_filter( 'upload_dir', 'buddyboss_pics_upload_dir' );
		
		$attachment = get_post( $aid );
		
		$name = $url = null;
		
		if ( $attachment !== null )
		{
			$name = $attachment->post_title;
			$img_size = is_active_sidebar( 'Profile' ) ? 'buddyboss_pic_med' : 'buddyboss_pic_wide';
			$url_nfo = wp_get_attachment_image_src( $aid, $img_size );
			$url = is_array( $url_nfo ) && !empty( $url_nfo ) ? $url_nfo[0] : null;
		}
		
		$result = array(
			'status'					=> ( $attachment !== null ),
			'attachment_id'		=> $aid,
			'url'							=> $url,
			'name'						=> $name
		);
		
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		die;
	
	}
	add_action( 'wp_ajax_buddyboss_post_picture', 'buddyboss_post_picture' );

	class BuddyBoss_Paginated
	{
		private $rs;                  					//result set
		private $pageSize;                      //number of records to display
		private $pageNumber;                    //the page to be displayed
		private $rowNumber;                     //the current row of data which must be less than the pageSize in keeping with the specified size
		private $offSet;
		private $layout;
	
		function __construct( $obj, $displayRows = 10, $pageNum = 1 )
		{
			$this->setRs( $obj );
			$this->setPageSize( $displayRows );
			$this->assignPageNumber( $pageNum );
			$this->setRowNumber( 0 );
			$this->setOffSet( ( $this->getPageNumber() - 1 ) * ( $this->getPageSize() ) );
		}
	
		//implement getters and setters
		public function setOffSet( $offSet )
		{
			$this->offSet = $offSet;
		}
	
		public function getOffSet()
		{
			return $this->offSet;
		}
	
	
		public function getRs()
		{
			return $this->rs;
		}
	
		public function setRs( $obj )
		{
			$this->rs = $obj;
		}
	
		public function getPageSize()
		{
			return $this->pageSize;
		}
	
		public function setPageSize( $pages )
		{
			$this->pageSize = $pages;
		}
	
		//accessor and mutator for page numbers
		public function getPageNumber()
		{
			return $this->pageNumber;
		}
	
		public function setPageNumber( $number )
		{
			$this->pageNumber = $number;
		}
	
		//fetches the row number
		public function getRowNumber()
		{
			return $this->rowNumber;
		}
	
		public function setRowNumber( $number )
		{
			$this->rowNumber = $number;
		}
	
		public function fetchNumberPages()
		{
			if ( !$this->getRs() )
			{
				return false;
			}

			$pages = ceil( count( $this->getRs() ) / (float) $this->getPageSize() );
			return $pages;
		}
	
		//sets the current page being viewed to the value of the parameter
		public function assignPageNumber($page) {
			if(($page <= 0) || ($page > $this->fetchNumberPages()) || ($page == "")) {
				$this->setPageNumber(1);
			}
			else {
				$this->setPageNumber($page);
			}
			//upon assigning the current page, move the cursor in the result set to (page number minus one) multiply by the page size
			//example  (2 - 1) * 10
		}
	
		public function fetchPagedRow()
		{
			if( ( !$this->getRs() ) || ( $this->getRowNumber() >= $this->getPageSize() ) )
			{
				return false;
			}
	
			$this->setRowNumber( $this->getRowNumber() + 1 );
			$index = $this->getOffSet();
			$this->setOffSet( $this->getOffSet() + 1 );
			return $index;
		}
	
		public function isFirstPage()
		{
			return ( $this->getPageNumber() <= 1 );
		}
	
		public function isLastPage()
		{
			return ( $this->getPageNumber() >= $this->fetchNumberPages() );
		}
	
		public function fetchPagedLinks($parent, $queryVars)
		{
			$currentPage = $parent->getPageNumber();
			$str = "<div class='paged-navigation'>";
			
			if( !$parent->isFirstPage() )
			{
				$previousPage = $currentPage - 1;
				$str .= "<a class='pag-prev' href=\"?page=$previousPage$queryVars\">&lt; ". _e( 'Prev' , 'buddyboss' ) ."</a> | ";
			}
	
			if( !$parent->isFirstPage() )
			{
				if( $currentPage != 1 && $currentPage != 2 && $currentPage != 3 )
				{
					$str .= "<a href='?page=1$queryVars' title='Start' class='pag-first'>". _e( 'First' , 'buddyboss' ) . " (1)</a> | ";
				}
			}
	
			for( $i = $currentPage - 2; $i <= $currentPage + 2; $i++ )
			{
				//if i is less than one then continue to next iteration		
				if( $i < 1 )
				{
					continue;
				}
		
				if( $i > $parent->fetchNumberPages() )
				{
					break;
				}
		
				if( $i == $currentPage )
				{
					$str .= "<span class='pag-curr'>" . _e( 'Page' , 'buddyboss' ) . " $i</span>";
				}
				else {
					$str .= "<a 'pag-page' href=\"?page=$i$queryVars\">$i</a>";
				}



				if ( $currentPage != $parent->fetchNumberPages() || $i != $parent->fetchNumberPages() )
					$str .= ' | ';
				
			}//end for

			if ( !$parent->isLastPage() )
			{
				if( $currentPage != $parent->fetchNumberPages() && $currentPage != $parent->fetchNumberPages() -1 && $currentPage != $parent->fetchNumberPages() - 2 )
				{
					$str .= "<a class='pag-last' href=\"?page=".$parent->fetchNumberPages()."$queryVars\" title=\"Last\">" . _e( 'Last' , 'buddyboss' ) . " (".$parent->fetchNumberPages().")</a> | ";
				}
			}
	
			if( !$parent->isLastPage() )
			{
				$nextPage = $currentPage + 1;
				$str .= "<a class='pag-next' href=\"?page=$nextPage$queryVars\">" . _e( 'Next' , 'buddyboss' ) . " &gt;</a>";
			}
			
			$str .= "</div>";
			
			return $str;
		}	
		
		public function fetchPagedNavigation( $queryVars = "" )
		{
			return $this->fetchPagedLinks( $this, $queryVars );
		}
		
	} //end BuddyBoss_Paginated
?>