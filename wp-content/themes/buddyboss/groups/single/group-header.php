<?php do_action( 'bp_before_group_header' ) ?>

<!-- Hide group header from forums -->
<?php if ( bp_is_group_forum() && bp_group_is_visible() ) : ?>
<?php else : ?>

	<div id="item-header-avatar">
		<a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>">
			<?php bp_group_avatar() ?>
		</a>
		<span class="group-type"><?php bp_group_type() ?></span>
		<span class="activity"><?php printf( __( 'active %s ago', 'buddypress' ), bp_get_group_last_active() ) ?></span>
	</div><!-- #item-header-avatar -->

	<div id="item-header-content">

		<div id="item-actions">
			<?php if ( bp_group_is_visible() ) : ?>
		
				<div id="group-admins">
				<h3><?php _e( 'Administrators', 'buddypress' ) ?></h3>
				<?php bp_group_admin_memberlist( false ) ?>
		
				<?php do_action( 'bp_after_group_menu_admins' ) ?>
				</div>
		
				<?php if ( bp_group_has_moderators() ) : ?>
					<div id="group-mods">
					<?php do_action( 'bp_before_group_menu_mods' ) ?>
		
					<h3><?php _e( 'Moderators' , 'buddypress' ) ?></h3>
					<?php bp_group_mod_memberlist( false ) ?>
		
					<?php do_action( 'bp_after_group_menu_mods' ) ?>
					</div>
				<?php endif; ?>
				
				<?php do_action( 'bp_before_group_header_meta' ) ?>

				<div id="item-meta">			
					<?php do_action( 'bp_group_header_meta' ) ?>
				</div>
		
			<?php endif; ?>
		</div><!-- #item-actions -->

		<div id="group-name">
			<h2><?php bp_group_name() ?> <?php do_action( 'bp_group_header_actions' ); ?></h2>
			<div class="entry-directory">
				<?php bp_group_description() ?>
			</div>		
		</div>
		
	</div><!-- #item-header-content -->

<?php endif; ?>

<?php do_action( 'bp_after_group_header' ) ?>

<?php do_action( 'template_notices' ) ?>