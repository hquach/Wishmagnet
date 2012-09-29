<?php

/**
 * BuddyPress - Activity Loop used when the Wall is activated.
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_dtheme_object_filter()
 *
 * @package BuddyBoss
 */

?>

<?php do_action( 'bp_before_activity_loop' ) ?>

<?php global $buddy_boss_wall;
$qs = wall_qs_filter('activity');
?>

<?php if ( bp_has_activities($qs) ) : ?>

	<?php /* Show pagination if JS is not enabled, since the "Load More" link will do nothing */ ?>
	<noscript>
		<div class="pagination">
			<div class="pag-count"><?php bp_activity_pagination_count() ?></div>
			<div class="pagination-links"><?php bp_activity_pagination_links() ?></div>
		</div>
	</noscript>

	<?php if ( empty( $_POST['page'] ) ) : ?>
		<ul id="activity-stream" class="activity-list item-list">
	<?php endif; ?>

	<?php while ( bp_activities() ) : bp_the_activity(); ?>
 
		<?php include( locate_template( array( 'activity/entry-wall.php' ), false ) ) ?>

	<?php endwhile; ?>

		<?php if ( bp_get_activity_count() == bp_get_activity_per_page() ) : ?>

		<li class="load-more">
			<a href="#more"><?php _e( 'Load More', 'buddyboss' ) ?></a>
		</li>
	<?php endif;  ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>
		</ul>
	<?php endif; ?>

<?php else : ?>
	<div id="message" class="info">
		<p>This user has not added any Wall posts yet.</p>
	</div>
<?php endif; ?>

<?php do_action( 'bp_after_activity_loop' ) ?>

<form action="" name="activity-loop-form" id="activity-loop-form" method="post">
	<?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ) ?>
</form>