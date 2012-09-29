/*--------------------------------------------------------------
BUDDYBOSS THEME
--------------------------------------------------------------*/

After activating the BuddyPress plugin, move this entire folder to the root level of "wp-content/themes".
Then activate the theme via Appearance > Themes.

Instructions: http://www.buddyboss.com/instructions/
Support: http://www.buddyboss.com/forum/

Enjoy!


/*--------------------------------------------------------------
RELEASE NOTES
--------------------------------------------------------------*/
2.0.3 - 03/14/12
Fixed Wall to display only your activity and mentions
Fixed activity stream from showing updates improperly
Comments are now visible for logged out users.
Fixed likes on wall and activity stream.

2.0.2 - 12/30/11
PO file included with theme under languages folder
Fixed CSS formatting on comments
Fixed CSS formatting on comments - removed numbered list
Fixed Read More for comments

2.0.1 - 10/05/11
Fixed @mention notifications Page Not Found error
Fixed CSS formatting on pages - bulleted lists and tables
Fixed CSS error of highlighting selected menu item's drop-down pages.
Fixed message overlapping "Add Photo" when using the Public Mention button.


2.0 - 9/27/11
Full compatibility with BuddyPress 1.5
Complete code rewrite – faster, cleaner, better documented, more stable
Much improved "Facebook" Wall
Photo uploading to the Wall, with user photo galleries
"Likes" on Wall posts
The return of @mentions. They now work in the wall.
Widgets galore! Optional widgetized sidebars on all BuddyPress components, including profiles and groups
All BuddyPress components are registered as WordPress pages and can be reordered within the admin
Full support for bbPress 2.0 and standalone forums
Improved commenting
Improved support for IE7
Improved image uploader for site logo
Added editor styles


1.1.4 - 7/26/11
Fixed bug with post form displaying on users' Walls while logged out.
Improved CSS with BuddyPress Activity Plus plugin.
Added word-wrap to admin bar for long words.
Made admin bar slightly wider to allow for longer words.
Fixed minor CSS issue with right aligned text getting cut off.
Updated "Theme Support" section in BuddyBoss admin settings.


1.1.3 - 7/05/2011
Compatibility with WordPress 3.2 and BuddyPress 1.2.9
Added optional sidebar widget area to all BuddyPress directory pages.
Improved the look of the BuddyBoss admin settings.
Added a "Read More" link to blog posts.
Added Next/Previous links to homepage (when using blog posts).
Fixed issue with post form displaying on other users' profiles when Wall is off.
Improved CSS display with WPMU Dev Facebook.
"Show/Hide Form" setting for BP Profile Search can now be checked or unchecked. It doesn't matter anymore.


1.1.2 - 6/10/2011
Added site logo to Sign Up (Register) page.
Removed an incorrect HTML comment in header.php.
Fixed CSS display with long list items.
Fixed CSS display with Group Email Subscriptions plugin.
Fixed CSS display with U Forum Attachments plugin.
Fixed CSS display with lists in forum posts.
Fixed CSS bug with group Join/Leave buttons in IE7.
When comments are closed, posts now display "Comments are closed" instead of "Leave a comment".
Updated permalink page template to include user profile links.


1.1.1 - 6/02/2011
Added fixes for formatting of BuddyPress Links plugin and BP Gallery plugin.
When posting new topics on Group Forums Directory, "Post In Group Forum" is now first and marked as required, to prevent posting errors.
Added bullets to widget list items to make individual items more clear.
Updated Homepage "My Profile" icons.
Updated footer credit.
Reformatted readme.txt to make it more legible.


1.1 - 5/24/2011
Added custom stylesheet to make theme updates easier.
Added support for featured images in blog posts.


1.0.9 - 5/17/2011
Greatly overhauled BuddyBoss Settings admin panel.
Login image can now be up to 500px wide (previously got cut off at 320px).
Administrators can now post to all members and can reply to all wall posts, regardless of friendship status.
Activity posting is now enabled on the Sitewide Activity page.
Wall only displays posts from Public groups now; posts from Hidden or Private groups are filtered out.
Rewrote front page template so that Activity Stream can be set as Front Page.
Fixed bug with wall link in the adminbar redirecting to the wrong page.
Footer can now contain any length of content without overflow.
When user has not added Wall posts yet, message now says "This user has not added any Wall posts yet."
Fixed issues with display of certain widgets, including Who's Online Avatars and Calendar.
Updated layout of Blogs Directory (only visible in multi-site).
Removed RSS feed link from Group home pages, for simplicity.
Improved layout of members search form, for when BP Profile Search plugin is not enabled.
Set blog page to display formatted excerpts, instead of entire posts.
Improved blog comment formatting.
Added "Edit this entry" link to blog posts.


1.0.8.2 - 5/12/2011
Fixed styling with radios and checkboxes on Register form.
Fixed missing background color on Freshness forum header.


1.0.8.1 - 5/11/2011
Removed "My Profile" notice (added in 1.0.7). Replaced with "Edit My Profile" link under avatar when on your own profile.


1.0.8 - 5/09/2011
Custom Login Logo now links to your website instead of WordPress.org.
Dropdown menus can now contain any length of text.
Fixed bug with widget title bars in IE7.
Fixed minor display issues noticeable while editing forum posts.
Blog posts now display the date along with the time.


1.0.7 - 5/06/2011
Updates to main stylesheet.
Added "My Profile" notice when on your own profile.
Added "Delete" button to Wall activity items.
Added "Reply" button user's own Wall activity items.


1.0.6 - 4/29/2011
Added Facebook-style wall component.

1.0.5
Added support for secondary footer custom menu.

1.0.4
Added support for WordPress 3.0 custom menus.

1.0.3
Improved signup process.

1.0.2
Added widget areas. Minor CSS updates.

1.0.1
Bug fixes related to activity stream.

1.0
Initial Version.


/*--------------------------------------------------------------
CHANGE LOG - Files that have been updated in each release
--------------------------------------------------------------*/
2.0.3
_inc/global.js
buddy_boss_wall.php
activity/entry.php
activity/entry-wall.php

2.0.2
_inc/css/default.css
_inc/global.js
buddy_boss_pics.php
buddy_boss_wall.php
sidebar-home-left.php
activity/comment.php
activity/entry.php
activity/entry-wall.php
functions.php
languages/en_US.po
languages/en_US.mo

2.0.1
buddy_boss_wall.php
_inc/css/default.css (Sections 4.0, 5.0)
activity/post-form.php


2.0
Complete code rewrite. All files changed.


---- BuddyPress 2.0 released, theme rewritten ----


1.1.4
_inc/css/default.css
_inc/css/buddybar.css
activity/post-form.php
activity/entry.php
admin_options.php


1.1.3
admin_options.php
_inc/ajax.php
_inc/global.js (for compatibility with WordPress 3.2)
_inc/css/default.css
_inc/css/registration.css
functions.php (registered new widget sections)
members/index.ph
groups/index.php
forums/index.php
blogs/index.php
activity/index.php
front-page.php
members/single/activity.php
sidebar-directory.php (new)
sidebar-members.php (removed)


1.1.2
header.php
registration/header.php
index.php (comments section)
archive.php (comments section)
search.php (comments section)
front-page.php (comments section)
members/single/activity/permalink.php
_inc/css/default.css
_inc/css/registration.css (copy this file to your child theme also, if you're using one)


1.1.1
forums/index.php
footer.php
_inc/css/default.css
_inc/images/icon-avatar.png
_inc/images/icon-edit.png
_inc/images/icon-profile.png
_inc/images/icon-search.png


1.1
style.css
_inc/css/custom.css (new)
_inc/css/default.css
functions.php
index.php
archive.php
front-page.php


1.0.9
functions.php
_inc/css/default.css
activity/post-form.php
activity/entry-wall.php
activity/activity-wall-loop.php
activity/index.php
blogs/index.php
forums/index.php
groups/index.php
members/index.php
sidebar-members.php
groups/single/activity.php
front-page.php
index.php
archive.php
single.php
admin_options.php
buddy_boss_wall.php
_inc/images/buddyboss-edit-icon-32.png (new)
_inc/images/buddyboss-admin-icon-16.png (new)


1.0.8.2
_inc/css/registration.css
_inc/css/default.css


1.0.8.1
_inc/css/default.css
_inc/images/change-avatar.png
members/single/member-header.php


1.0.8
functions.php
_inc/css/default.css
_inc/css/buddybar.css
_inc/images/nav.png
groups/single/forum/topic.php
groups/single/forum/edit.php
index.php
single.php
front-page.php


1.0.7
_inc/css/default.css
buddy_boss_wall.php
buddy_boss_wall_third_party.php
activity (entire folder)
members/single/member-header.php
groups/single/admin.php
groups/single/members.php
groups/single/send-invites.php
footer.php

