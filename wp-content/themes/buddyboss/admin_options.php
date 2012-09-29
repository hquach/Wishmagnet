<?php

/* ADMIN OPTIONS */

/**
 * Add BuddyBoss to the Admin menu
 */
function buddy_boss_admin_menu()
{
	global $wp_admin_bar;
	
	add_menu_page( 'BuddyBoss Settings', 'BuddyBoss', 'manage_options', 'buddy_boss_settings_menu', 'buddy_boss_general_settings', get_bloginfo('template_directory').'/_inc/images/buddyboss-admin-icon-16.png', 4 );
	
}
add_action('admin_menu', 'buddy_boss_admin_menu');

/** 
 * General Settings Page
 */
function buddy_boss_general_settings()
{
	
	// the 'pb' token defines if we're posting a new picture
	// todo: create a better admin panel
	// todo: create more semantic post tokens
	// todo: hook this into the Wordpress media library
	if ( !empty($_POST['pb']) )
	{
		// Let's get the newly uploaded file information
		$file = $_FILES['logo'];
	
		// Check if we have an upload
		if ( !empty($file) && $file['error'] == 4)
		{
			// We're missing crucial file informaiton, return an error
			$msg  = "Please specify the logo file to upload.";
			$mode = "error";
		}
		
		// We have no errors, proceed with upload
		else if ( !empty($file) && $file['error'] == 0)
		{
			// This is our status flag that will stop processing at various
			// statges if an error is detected
			$ok =  TRUE;
			
			// Define acceptable MIME types
			$mime = array
			(
				'image/gif' => 'gif',
				'image/jpeg' => 'jpeg',
				'image/png' => 'png',
				'application/x-shockwave-flash' => 'swf',
				'image/psd' => 'psd',
				'image/bmp' => 'bmp',
				'image/tiff' => 'tiff',
				'image/tiff' => 'tiff',
				'image/jp2' => 'jp2',
				'image/iff' => 'iff',
				'image/vnd.wap.wbmp' => 'bmp',
				'image/xbm' => 'xbm',
				'image/vnd.microsoft.icon' => 'ico'
			);
			
			// Extract variables needed from $_FILE array
			$size	= $file['size'] / 1024; // in KB
			$type	= $file['type'];
			$name	= $file['name'];
			$tmp	= $file['tmp_name'];
			
			// Calculate extension and image size
			$ext	= strtolower(substr(strrchr($name, "."), 1));
			$img_info = getimagesize($tmp);
			
			// Maximum file size is 150kb
			// todo: create a variable and maybe admin option for this
			if ($size > 150)
			{
				$msg = "File size must be no more than 150 KB";
				$mode = "error";
				$ok = FALSE;
			}
			
			// Check if it's an image
			if ( empty( $img_info ) )
			{
				$msg = "Make sure the image type is JPG, GIF, or PNG.";
				$mode = "error";
				$ok = FALSE;
			}
			
		}
		
		// There was an unknown error, let's inform the user
		else {
			$msg = "There has been an error uploading the logo. Try again";
			$mode = "error";
			$ok = FALSE;
		}
		
		// If the status of $ok is TRUE we've verified the upload's success
		if ($ok)
		{
			// Prepare file name
			$file_mime = $img_info['mime'];
			
			// Set a proper extension
			if( $ext == 'jpc' || $ext == 'jpx' || $ext == 'jb2' )
			{
				$extension = $ext;
			}
			else {
				$extension = ( $mime[$file_mime] == 'jpeg' ) ? 'jpg' : $mime[$file_mime];
			}
			
			if( !$extension )
			{
				$extension = '';
				$name = str_replace('.', '', $name);
			}
			
			// Parse the file name and force a proper extension
			$file_name = strtolower($name);
			$file_name = str_replace(' ', '-', $file_name);
			$file_name = substr($file_name, 0, -strlen($ext));
			$file_name .= $extension;
			
			// Get the uploads directory and proper path
			$upload_dir = wp_upload_dir();
			$path = $upload_dir['path'];
			$rel  = $upload_dir['url'].'/'.$file_name;
			
			// Attempt to move the uploaded file and save to it's new name
			$ok = move_uploaded_file(realpath($tmp), $path."/".$file_name);
			
			// If everything went OK the upload was a success and the new logo's location
			// will be save to the WP options table.
			// todo: create an image cropper and use the WP media library functionality
			// todo: allow the user to select an image from the WP media library as their logo
			if ($ok)
			{
				$msg = "Your logo has been uploaded";
				$mode = "updated";
				update_option("buddy_boss_custom_logo", $rel);
				update_option("buddy_boss_custom_logo_file", $path."/".$file_name);
	
			}
			
			// If the upload failed to be moved let's spit out an error and some general advice
			else {
				$msg = "Upload error: Make sure your wp-content/uploads folder is writable (<a href='http://codex.wordpress.org/Changing_File_Permissions' target='_blank'>755 or 777</a>)";
				$mode = "error";
			}
		}
	}
	
	// Delete the logo
	if (isset($_POST['del']))
	{
		$file = get_option('buddy_boss_custom_logo_file');
		
		// Attempt to delete the current file:
		$status = unlink($file);
		
		// Delete the options from WP options table
		delete_option('buddy_boss_custom_logo');
		delete_option('buddy_boss_custom_logo_file');
		
		// Return success/errors
		$mode ="updated";
		$msg = $status ? "Reverted to the default logo." : "We couldn't delete your current logo at $file, but we've reverted to the default logo anyways.";
	}
	
	// Check to see if we're activating the wall component.
	$wall_activate_log = '';
	
	if (isset($_POST['wall_update']))
	{
		// Let's check the state we should enable
		if (isset($_POST['wall']))
		{
			// 0 = false = not enabled
			// 1 = false - enabled
			$state  = ( intval($_POST["wall"]) === 1 );
			
			// Based on the state posted and the current state, return error
			// or success messages and perform required actions
			
			if ($state === TRUE && !function_exists("friends_get_alphabetically"))
			{
				$msg = "Wall cannot be turned on if the friends component is inactive";
				$mode = "error";
			}
			elseif ($state === TRUE && BUDDY_BOSS_WALL_ENABLED)
			{
				$msg = "The Wall is already active!";
				$mode = "error";
			}
			elseif($state === FALSE && !BUDDY_BOSS_WALL_ENABLED)
			{
				$msg = "The Wall is already inactive!";
				$mode = "error";
			}
			else {
				update_option("buddy_boss_wall_on", $state );
				$state_lbl = ($state === TRUE)? "enabled": "disabled";
				$msg = "Wall settings updated: Profile Wall is now $state_lbl.";
				$mode = "updated";
	
				if ($state == 1) $wall_activate_log = buddy_boss_wall_on_activate();
				if ($state == 0) $wall_activate_log = buddy_boss_wall_on_deactivate();
	
			}
		}
	}
	
	
	// Check to see if we're activating the picture component.
	$pics_activate_log = '';
	
	if (isset($_POST['pics_update']))
	{
		// Let's check the state we should enable
		if (isset($_POST['pics']))
		{
			// 0 = false = not enabled
			// 1 = false - enabled
			$state  = ( intval($_POST["pics"]) === 1 );
			
			// Based on the state posted and the current state, return error
			// or success messages and perform required actions
			
			if ($state === TRUE && BUDDY_BOSS_PICS_ENABLED)
			{
				$msg = "The Picture Gallery is already active!";
				$mode = "error";
			}
			elseif($state === FALSE && !BUDDY_BOSS_PICS_ENABLED)
			{
				$msg = "The Picture Gallery is already inactive!";
				$mode = "error";
			}
			else {
				update_option("buddy_boss_pics_on", $state );
				$state_lbl = ($state === TRUE)? "enabled": "disabled";
				$msg = "Picture Gallery settings updated: Gallery is now $state_lbl.";
				$mode = "updated";
	
				if ($state == 1) $pics_activate_log = buddy_boss_pics_on_activate();
				if ($state == 0) $pics_activate_log = buddy_boss_pics_on_deactivate();
	
			}
		}
	}
	
	// Set default message if not already defined
	if (!empty($msg))	$message = '<div id="message" style="padding:8px 10px;" class="'.$mode.' below-h2">'. $msg. '</div>';
	
	// Grab the current logo, falling back to our default BuddyBoss Logo
	$current_logo = (get_option("buddy_boss_custom_logo", FALSE) == FALSE) ? get_bloginfo('template_directory').'/_inc/images/logo.jpg' : get_option("buddy_boss_custom_logo");
	
	// Prepare HTML for radio button status
	$wall_on_status = (get_option("buddy_boss_wall_on", -1) == TRUE && function_exists("friends_get_alphabetically") == TRUE) ? "checked": "";
	$wall_off_status = (get_option("buddy_boss_wall_on", -1) == FALSE) ? "checked": "";
	
	$pics_on_status = (get_option("buddy_boss_pics_on", -1) == TRUE) ? "checked": "";
	$pics_off_status = (get_option("buddy_boss_pics_on", -1) == FALSE) ? "checked": "";
	
	// Prepare the URL to our favicon for the WP admin menu
	$buddyboss_edit_icon = get_bloginfo('template_directory').'/_inc/images/buddyboss-edit-icon-32.png';
	
	// Echo the HTML for the admin panel
	$html = <<<EOF
	
	<div class="wrap" style="padding-top:5px;">
	
		<img alt="BuddyBoss Theme Settings" src="$buddyboss_edit_icon" style="float:left;margin:7px 8px 0 5px;"/>
		<h2>BuddyBoss Theme Settings</h2>
				
		<p style="padding:0 0 10px 5px">Read the <a href="http://www.buddyboss.com/instructions/" target="_blank">setup instructions</a> to get started. Thanks for purchasing BuddyBoss!</p>
		
		$message
		
		<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
		
					<div class="postbox-container" style="width:69%;">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					
								<div class="postbox">
									<h3 style="cursor:default"><span>Logo &mdash; appears in logo and login page</span></h3>
									<div class="inside">
										
										<h4>Current Logo</h4>
											<img style="border:1px solid #ccc" src="$current_logo">
								
										<h4>Upload New Logo</h4>
										<form enctype="multipart/form-data" method="post">
											<input type="file" name="logo"></input>
											<input class="button-primary" type="submit" name="pb" value="Upload" />
											<input class="button-secondary" type="submit" name="del" value="Revert to default"/>
											<p style="color:#777">Logo should be no more than 87px tall and 500px wide. Smaller dimensions are okay.</p>
											<p style="color:#777"><strong>If multisite is enabled:</strong> Go to <em>Network Admin</em> (top right), then click <em>Settings</em>. Scroll down to <em>Upload Settings</em> and make sure "Images" is checked under <em>Media upload buttons</em>.</p>
										</form>
									</div><!-- end .inside -->
								</div><!-- end .postbox -->
								
								<div class="postbox">
									<h3 style="cursor:default"><span>Profile Wall Component</span></h3>
									<div class="inside">
										<form method="post">
											<p>$wall_activate_log</p>
											<p style="padding:7px 10px;background-color:#fff;border:1px solid #dfdfdf;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px">Wall Component: <input type="radio" name="wall" value="1" $wall_on_status> Enabled
											<input type="radio" name="wall" value="0" $wall_off_status > Disabled
											<input class="button-secondary" type="submit" name = "wall_update" value="Update">
											</p>
											<p style="color:#777"><strong>Activity Streams</strong> and <strong>Friends</strong> need to first be enabled, under <em>BuddyPress > Component Setup</em>.</p>
										</form>
									</div><!-- end .inside -->
								</div><!-- end .postbox -->

								<div class="postbox">
									<h3 style="cursor:default"><span>Picture Gallery Component</span></h3>
									<div class="inside">
										<form method="post">
											<p>$pics_activate_log</p>
											<p style="padding:7px 10px;background-color:#fff;border:1px solid #dfdfdf;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px">
												Picture Gallery Component:
												<input type="radio" name="pics" value="1" $pics_on_status> Enabled
												<input type="radio" name="pics" value="0" $pics_off_status > Disabled
												<input class="button-secondary" type="submit" name = "pics_update" value="Update">
											</p>
										</form>
									</div><!-- end .inside -->
								</div><!-- end .postbox -->

					</div>
					</div><!-- end .postbox-container -->
								
					
					
					<div class="postbox-container" style="width:30%;">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div class="postbox">
								<h3 style="cursor:default">Theme Support</h3>
								<div class="inside">
										<ul style="padding:10px 0 6px;">
											<li><a style="text-decoration:none;" href="http://www.buddyboss.com/instructions-2/" target="_blank">Setup Instructions</a></li>
											<li><a style="text-decoration:none;" href="http://www.buddyboss.com/faq/" target="_blank">Frequently Asked Questions</a></li>
											<li><a style="text-decoration:none;" href="http://www.buddyboss.com/plugins/" target="_blank">Recommended Plugins</a></li>
											<li><a style="text-decoration:none;" href="http://www.buddyboss.com/updating/" target="_blank">How to Update</a></li>
											<li><a style="text-decoration:none;" href="http://www.buddyboss.com/release-notes/" target="_blank">Current Version &amp; Release Notes</a></li>
											<li><a style="text-decoration:none;" href="http://www.buddyboss.com/child-themes/" target="_blank">Guide to Child Themes</a></li>
											<li><a style="text-decoration:none;" href="http://www.buddyboss.com/forum/" target="_blank">Support Forums</a></li>
										</ul>
								</div><!-- end .inside -->
							</div><!-- end .postbox -->
					</div>
					</div><!-- end .postbox-container -->
							 									
			
			<div class="clear"></div>
							
		</div><!-- end #dashboard-widgets -->
		</div><!-- end #dashboard-widgets-wrap -->
		
	</div><!-- end .wrap -->
	<div class="clear"></div>	
EOF;
	echo $html;
}
?>