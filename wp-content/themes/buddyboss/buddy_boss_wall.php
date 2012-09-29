<?php

	/**
	* buddy_boss_wall is a BuddyPress plugin combining user activity feeds into a Facebook-like wall.
	*
	* @author		BuddyBoss
	* @credits	Brajesh Singh for his tutorial on in profile posting
	* @since		BuddyBoss 2.0
	*/
	
	// Indicate whether the Wall module is enabled
	// Check if the friends module is enabled if not disable the wall
	if (function_exists('friends_get_alphabetically'))
	{
		$option = get_option("buddy_boss_wall_on");
		define(BUDDY_BOSS_WALL_ENABLED, $option);
	}
	else
	{
		define(BUDDY_BOSS_WALL_ENABLED, FALSE);
	}

	
	// Indicate whether to show debug msgs on screen
	
	define(BUDDY_BOSS_DEBUG, false);
	
	// a variable to hold the event log
	$buddy_boss_debug_log = "";
	
	
	// DEFAULT CONFIGURATION OPTIONS
	$buddy_boss_wall_defaults = array(
		"POST_IN_WIRE_OPTIONS"	=> array(),
		"UPDATE_MENUS"					=> TRUE,
		"PERSONAL_TAB_NAME"			=> __( 'News Feed' , 'buddyboss' ),
		"FAV_TAB_NAME"					=> __( 'My Likes' , 'buddyboss' ),
		"MENU_NAME"							=> __( 'Wall' , 'buddyboss' )
	);
	
	class BuddyBoss_Wall
	{
		/**
		 * BUDDYPRESS ACTIVITIES
		 *
		 * @since BuddyBoss 1.5
		 */
		public $activities;
		public $activity_count = 0;
		public $filter_qs = false;
		
		/**
		 * OPTIONS
		 *
		 * @since BuddyBoss 1.5
		 */
		private $options;
		
		/**
		 * STORAGE
		 *
		 * @since BuddyBoss 2.0
		 */
		public $cache;
		
		/**
		 * LIKES
		 *
		 * @since BuddyBoss 2.0
		 */
		public $likes_store = array();
		
		/**
		 * INITIALIZE CLASS
		 *
		 * @since BuddyBoss 1.5
		 */
		function __construct($options = null)
		{
			global $buddy_boss_wall_defaults,  $activity_template;

			if (isset($options) && $options !=null)
			{
				$this->options = $options;
			}
			else
			{
				$this->options = $buddy_boss_wall_defaults;
				buddy_boss_log("WALL Using default config");
			}
			
			// Log
			buddy_boss_log($this->options);
			
			// Update menu text
			if (isset($this->options['UPDATE_MENUS']) && $this->options['UPDATE_MENUS'] == true)
			{
				add_action( 'bp_setup_nav', array($this, 'update_menus'), 98 );
				add_action( 'bp_setup_nav', array($this, 'bbg_remove_activity_friends_subnav'), 99 );
				add_filter( 'bp_get_displayed_user_nav_activity', array($this, 'bbg_replace_activity_link') );
			}
			
			// Add body class
			add_filter( 'body_class', array($this, 'add_body_class') );
			
			// Caching
			$this->cache = get_transient('bbwall_cacher');
			add_action( 'wp_shutdown', array($this, 'shutdown') );
			
			return $this;
		}
		
		/**
		 * SAVES CACHE @ WP SHUTDOWN
		 *
		 * @since BuddyBoss 1.5
		 */
		function shutdown()
		{
			set_transient('bbwall_cacher', $this->cache);
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
			$classes[] = 'buddyboss-active-wall';
			return $classes;
		}
		
		/**
		 * RENAME ACTIVITY LINK ON PROFILE SIDEBAR MENU
		 *
		 * @since BuddyBoss 1.5
		 */
		function bbg_replace_activity_link($v)
		{
			return str_replace('Activity', $this->options["MENU_NAME"], $v);
		}
		
		/**
		 * REMOVE TABS FROM PROFILE HEADER
		 *
		 * @since BuddyBoss 1.5
		 */ 
		function bbg_remove_activity_friends_subnav() {
			
			global $bp;
			
			// For now we hack the $bp global to remove a nav item without
			// removing the funcitonality. This is a bug in Buddypress
			if ( isset( $bp->bp_options_nav ) && isset ( $bp->bp_options_nav['activity'] ) )
			{
				if ( isset( $bp->bp_options_nav['activity']['friends'] ) )
					unset ( $bp->bp_options_nav['activity']['friends'] );
			
				if ( isset( $bp->bp_options_nav['activity']['mentions'] ) )
					unset ( $bp->bp_options_nav['activity']['mentions'] );
			
				if ( isset( $bp->bp_options_nav['activity']['groups'] ) )
					unset ( $bp->bp_options_nav['activity']['groups'] );
			
				if ( !bp_is_home() && isset( $bp->bp_options_nav['activity']['favorites'] ) )
					unset ( $bp->bp_options_nav['activity']['favorites'] );
			}
			
			/**
			 * This code won't work until a bugfix in Buddypress is applied.
			 *
			bp_core_remove_subnav_item( 'activity', 'friends' );
			bp_core_remove_subnav_item( 'activity', 'mentions' );
			bp_core_remove_subnav_item( 'activity', 'groups' );
			
			if ( !bp_is_home() )
				bp_core_remove_subnav_item( 'activity', 'favorites' );
			*/
		}
		
		/**
		 * RENAME MENU TABS ON PROFILE
		 */
		function update_menus()
		{
			buddy_boss_log('Updating Menus');
			global $bp;


			$domain = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
			
			$profile_link = $domain . $bp->activity->slug . '/';

			// RENAME PERSONAL TAB
			bp_core_new_subnav_item( array( 
				'name' => $this->options["PERSONAL_TAB_NAME"], 
				'slug' => 'just-me', 
				'parent_url' => $profile_link, 
				'parent_slug' => $bp->activity->slug, 
				'screen_function' => 
				'bp_activity_screen_my_activity' , 
				"position" => 10 
			) );
			
			// RENAME FAVORITES TAB
			bp_core_new_subnav_item( array( 
				'name' => $this->options["FAV_TAB_NAME"], 
				'slug' => 'favorites', 
				'parent_url' => $profile_link, 
				'parent_slug' => $bp->activity->slug, 
				'screen_function' => 'bp_activity_screen_favorites',
				'position' => 10
			) );
		}
		
		/**
		 * WRAPPER FUNCTION, WILL BE DEPRECATED
		 */
		function is_friend($id)
		{
			return buddyboss_is_my_friend($id);
		}
		
		/**
		 * GET WALL ACTIVITES
		 */
		function get_wall_activities($page = 0, $per_page=20){

			global $bp,$wpdb;
			$min = ($page>0)? ($page-1) * $per_page : 0;
			$max = ($page+1) * $per_page;
			$per_page = bp_get_activity_per_page();
			buddy_boss_log(" per page $per_page");

			if (isset($bp->loggedin_user) && isset($bp->loggedin_user->id) && $bp->displayed_user->id == $bp->loggedin_user->id)
			{
				$myprofile = true;
			}
			else {
				$myprofile = false;
			}
			$wpdb->show_errors = BUDDY_BOSS_DEBUG;
			$user_id = $bp->displayed_user->id;

			buddy_boss_log("Looking at $user_id" );
			$filter = addslashes($bp->displayed_user->fullname);
			$friend_ids = friends_get_friend_user_ids($user_id);
			$admin_ids = array(); //buddyboss_users_by_role( 'administrator' );
			$friend_ids = array_merge($friend_ids,$admin_ids);
			
			// var_dump($friend_ids);
			buddy_boss_log($friend_ids);

			if (!empty($friend_ids)) $friend_id_list = implode(",", ($friend_ids));
			
			buddy_boss_log($friend_id_list);
			$table = $wpdb->prefix.bp_activity;
			$table2 = $wpdb->prefix.bp_activity_meta;

			// Group Display code
			if(array_search("groups",$bp->active_components))
			$groups = BP_Groups_Member::get_group_ids($user_id);
			//var_dump($groups);

			$valid_groups=array();
			if (!empty($groups)) {
				foreach ($groups['groups'] as $id)
				{
					$group = new BP_Groups_Group( $id);
					//var_dump($group);
					if ("public" == $group->status) {
						//echo $group->slug;
						$valid_groups[]=$id;
					}
					$user_group[]=$group->slug;
				}
			}
					
			$group_modifier = "OR ( $table.user_id = $user_id AND $table.component = 'groups' ) ";
			$group_mentions = "OR ( $table.component = 'groups' AND ($table.content LIKE '%@$filter%'))";
			$friendships = "OR ( $table.user_id = $user_id AND $table.component = 'friends' )";
			$mentions_modifier = "OR ( $table.component = 'activity' AND $table.action LIKE '%@$filter%' ) ";
			//$mem_slugs = $bp->members->slug;
            $friends_modifier = "OR ( $table2.meta_value LIKE '%$filter%' AND $table.type!='activity_comment') ";
			$qry = "SELECT $table.id FROM $table LEFT JOIN $table2 ON $table.id=$table2.activity_id
			WHERE ( $table.user_id = $user_id AND $table.type!='activity_comment' AND $table.type!='friends' )
			$friends_modifier
			$group_modifier
			$group_mentions
			$friendships
			$mentions_modifier
			ORDER BY date_recorded DESC LIMIT $min, 30";
			
			//echo $qry;

			$activities  = $wpdb->get_results($qry,ARRAY_A);
			//var_dump($wpdb->print_error());
			buddy_boss_log($qry);

			buddy_boss_log($activities);


			if (empty($activities )) return null;


			$tmp = array();

			foreach ($activities as $a)
			{
				$tmp[] = $a["id"];

			}

			$activity_list = implode(",", $tmp);
			return $activity_list;
		}
		
		/**
		 * Retrive likes for current activity (within activity loop)
		 *
		 * @since 2.0
		 */
		function has_likes( $actid = null )
		{
			if ( $actid === null ) $actid = bp_get_activity_id();

			return bp_activity_get_meta( $actid, 'favorite_count' );
		}
		
	} // end of BUDDY_BOSS_WALL class



	/**
	 * ACTIVATION AND DEACTIVATION CODE
	 */
	function buddy_boss_wall_on_activate()
	{
		return 'The Profile Wall was activated successfully';
	}

		
	/**
	 * Deregister the BuddyBoss Wall Component
	 *
	 * @since BuddyBoss 2.0
	 */
	function buddy_boss_wall_on_deactivate()
	{
		return '';
	}

	/* FILTERS */
	if (BUDDY_BOSS_WALL_ENABLED)
	{
		add_action('bp_before_activity_comment', 'wall_add_like_action');
		add_filter('bp_get_activity_action', 'wall_read_filter');
		add_filter('bp_activity_after_save', 'wall_input_filter' );
		add_filter('bp_ajax_querystring', 'wall_qs_filter');
	}
	
	/**
	 * This adds how many people liked an item
	 *
	 * @since BuddyBoss 2.0
	 */
	function wall_add_like_action()
	{
		global $buddy_boss_wall;
		
		if ( isset($_POST['action']) && $_POST['action'] == 'new_activity_comment' )
			return false;
		
		$actid = (int) bp_get_activity_id();
		
		if ( $actid === 0 )
			return false;
		
		if ( isset( $buddy_boss_wall->likes_store[$actid] ) )
			return false;
		
		$count = (int) bp_activity_get_meta( $actid, 'favorite_count' );
		
		$buddy_boss_wall->likes_store[$actid] = 1;
		
		if ( $count === 0 )
			return false;
		
		$subject = ($count == 1) ? __( 'person' , 'buddyboss' ) : __( 'people' , 'buddyboss' );
		$verb = ($count > 1) ? __( 'like' , 'buddyboss' ) : __( 'likes' , 'buddyboss' );
		
		$like_html = "<li class=\"activity-like-count\">$count $subject $verb " . __( 'this' , 'buddyboss' ) . ".</li>";
		
		echo $like_html;
		
	}
	
	/**
	 * This adds how many people liked an item
	 *
	 * @since BuddyBoss 2.0
	 */
	function wall_add_likes_comments()
	{
		$actid = (int) bp_get_activity_id();
		
		if ( $actid === 0 )
			return false;
		
		$count = (int) bp_activity_get_meta( $actid, 'favorite_count' );
		
		if ( $count === 0 )
			return false;
		
		$subject = ($count == 1) ? __( 'person' , 'buddyboss' ) : __( 'people' , 'buddyboss' );
		$verb = ($count > 1) ? __( 'like' , 'buddyboss' ) : __( 'likes' , 'buddyboss' );
		
		$like_html = "<ul><li class=\"activity-like-count\">$count $subject $verb " . __( 'this' , 'buddyboss' ) . ".</li></ul>";
		
		echo $like_html;
	}
	
	/**
	 * This filters wall actions, when reading an item it will convert it to use wall language
	 *
	 * @since BuddyBoss 2.0
	 */
	function wall_read_filter($action)
	{
		global $bbwall, $activities_template;
		$curr_id = $activities_template->current_activity;
		$act_id = $activities_template->activities[$curr_id]->id;
		$bbwall_action = bp_activity_get_meta( $act_id, 'bboss_wall_action' );
		return ($bbwall_action) ? $bbwall_action : $action;

	}
	
	/**
	 * This will save wall related data to the activity meta table when a new wall post happens
	 *
	 * @since BuddyBoss 2.0
	 */
	function wall_input_filter(&$activity)
	{
		global $bp, $buddy_boss_wall;
		
		$user = $bp->loggedin_user;
		$tgt  = $bp->displayed_user;
		$new_action = false;

		// If we're on an activity page (user's own profile or a friends), check for a target ID
		if ( $bp->current_action == 'just-me' && (!isset($tgt->id) || $tgt->id == 0) ) return;

		// It's either an @ mention, status update, or forum post.
		if ( ($bp->current_action == 'just-me' && $user->id == $tgt->id) || $bp->current_action == 'forum' )
		{
			
			if (!empty($activity->content))
			{
				$mentioned = bp_activity_find_mentions($activity->content);
				$uids = array();
				$usernames = array();
				
				// Get all the mentions and store valid usernames in a new array
				foreach( (array)$mentioned as $mention ) {
					if ( bp_is_username_compatibility_mode() )
						$user_id = username_exists( $mention );
					else
						$user_id = bp_core_get_userid_from_nicename( $mention );
		
					if ( empty( $user_id ) )
						continue;
					
					$uids[] = $user_id;
					$usernames[] = $mention;
				}
				
				$len = count($uids);
				$mentions_action = '';
				
				// It's mentioning one person
				if($len == 1)
				{
					$user_id = 
					$tgt = bp_core_get_core_userdata( (int) $uids[0] );
					$user_url  = '<a href="'.$user->domain.'">'.$user->fullname.'</a>';
					$tgt_url  = '<a href="'.bp_core_get_userlink( $uids[0], false, true ).'">@'.$tgt->user_login.'</a>';

					$mentions_action = " " . __( 'mentioned' , 'buddyboss' ) ." ". $tgt_url;
				}
				
				// It's mentioning multiple people
				elseif($len > 1)
				{
					$user_url  = '<a href="'.$user->domain.'">'.$user->fullname.'</a>';
					$un = '@'.join(',@', $usernames);
					$mentions_action = $user_url. " " . __( 'mentioned' , 'buddyboss' ) ." ".$len." " . __( 'people' , 'buddyboss' );
				}
				
				// If it's a forum post let's define some forum topic text
				if ( $bp->current_action == 'forum' )
				{
					$new_action = str_replace( ' replied to the forum topic', $mentions_action.' in the forum topic', $activity->action);
				}
				
				// If it's a plublic message let's define that text as well
				elseif ($len > 0) {
					$new_action = $user_url. " " .$mentions_action.' ' . __( 'in a public message' , 'buddyboss' );
				}
				
				// Otherwise it's a normal status update
				else {
					$new_action = false;
				}
				
			}
		}
		
		// It's a normal wall post because the displayed ID doesn't match the logged in ID
		// And we're on activity page
		elseif ( $bp->current_action == 'just-me' && $user->id != $tgt->id ) {
			$user_url  = '<a href="'.$user->domain.'">'.$user->fullname.'</a>';
			$tgt_url  = '<a href="'.$tgt->domain.'">'.$tgt->fullname.'\'s</a>';
	
			// if a user is on his own page it is an update
			$new_action = sprintf( __( '%s wrote on %s Wall', 'buddyboss' ), $user_url , $tgt_url );
		}
		
		if ( $new_action )
		{
			bp_activity_update_meta( $activity->id, 'bboss_wall_action', $new_action );
		}
		
	}

	function wall_qs_filter($qs)
	{
		global $bp, $buddy_boss_wall, $buddy_boss_wall_defaults;
		
		//echo $qs;
		$action = $bp->current_action;
		if ( $action != "just-me")
		{
			// if we're on a different page than wall pass qs as is
			return $qs;
		}
		
		// else modify it to include wall activities

		// see if we have a page string
		$page_str  = preg_match("/page=\d+/", $qs, $m);
		$page= intval(str_replace("page=", "", $m[0])); // if so grab the number

		$activities = $buddy_boss_wall->get_wall_activities($page); // load activities for this page

		$nqs = "include=$activities";

		return $nqs;
	}

	// POST IN WIRE by Brajesh Singh

	// AJAX update posting
	function bp_mytheme_post_update() {
		global $bp;

		// Check the nonce
		check_admin_referer( 'post_update', '_wpnonce_post_update' );

		if ( !is_user_logged_in() ) {
			echo '-1';
			return false;
		}
		
		if ( empty( $_POST['content'] ) ) {
			echo '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'buddypress' ) . '</p></div>';
			return false;
		}

		if ( empty( $_POST['object'] ) && function_exists( 'bp_activity_post_update' ) ) {

			if(!bp_is_home()&&bp_is_member())
			$content="@". bp_get_displayed_user_username()." ".$_POST['content'];
			else
			$content=$_POST['content'];
			$activity_id = bp_activity_post_update( array( 'content' => $content ) );
		} elseif ( $_POST['object'] == 'groups' ) {
			if ( !empty( $_POST['item_id'] ) && function_exists( 'groups_post_update' ) )
			$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $_POST['item_id'] ) );
		} else
		$activity_id = apply_filters( 'bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );

		if ( !$activity_id ) {
			echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'buddypress' ) . '</p></div>';
			return false;
		}

		if ( bp_has_activities ( 'include=' . $activity_id ) ) : ?>
		<?php while ( bp_activities() ) : bp_the_activity(); ?>
		<?php locate_template( array( 'activity/entry-wall.php' ), true ) ?>
		<?php endwhile; ?>
		<?php endif;
	}
	add_action( 'wp_ajax_post_update', 'bp_mytheme_post_update' );

	add_action("init","mytheme_remove_original_update_func",5);
	function mytheme_remove_original_update_func(){
		remove_action( 'wp_ajax_post_update', 'bp_dtheme_post_update' );
	}
?>