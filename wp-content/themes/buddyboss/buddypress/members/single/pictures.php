<?php

/**
 * BuddyPress - Users Photos
 *
 * @package BuddyBoss
 */

?>

<?php get_header( 'buddypress' ); ?>

	<header class="entry-header">
		<h1 class="entry-title"><?php echo '<a href="' . bp_get_displayed_user_link() . '">' . bp_get_displayed_user_fullname() . '</a>'; ?></h1>
	</header>
	<div class="entry-content" id="content">
		<div id="buddypress">

			<?php do_action( 'bp_before_member_plugin_template' ); ?>

			<div id="item-header">

				<?php bp_get_template_part( 'members/single/member-header' ) ?>

			</div><!-- #item-header -->


			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
					<ul>

						<?php bp_get_displayed_user_nav(); ?>

						<?php do_action( 'bp_member_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body" role="main">

				<?php do_action( 'bp_before_member_body' ); ?>

				<div class="item-list-tabs no-ajax" id="subnav">
					<ul>

						<?php bp_get_options_nav(); ?>

						<?php do_action( 'bp_member_plugin_options_nav' ); ?>

					</ul>
				</div><!-- .item-list-tabs -->

				<?php if ( bp_is_my_profile() ) : ?>

					<?php bp_get_template_part( 'activity/post-form' ) ?>

				<?php endif; ?>

				<?php if ( buddyboss_has_pics() ) : ?>
					<div class="gallery has-sidebar" id="buddyboss-pics-grid">
					<?php while ( buddyboss_has_pics() ) : buddyboss_the_pic(); ?>

						<?php
							$image = get_buddyboss_pic_image();
							$tn = get_buddyboss_pic_tn();
							if ( is_array( $image ) && !empty( $image ) && is_array( $tn ) && !empty( $tn ) ):
						?>
							<dl class="gallery-item">
								<dt class="gallery-icon">
									<a rel="gal_item" href="<?php echo get_buddyboss_pic_link(); ?>">
										<img src="<?php echo esc_url( $tn[0] ); ?>" width="<?php echo (int)$tn[1]; ?>" height="<?php echo (int)$tn[2]; ?>" data-permalink="<?php echo get_buddyboss_pic_permalink(); ?>" />
									</a>
									<?php echo get_buddyboss_pic_action(); ?>
								</dt>
							</dl>
						<?php endif; ?>

					<?php endwhile; ?>
					</div>

					<?php buddyboss_pics_pagination(); ?>

				<?php else: ?>

					<div class="info" id="message"><p><?php _e( 'There were no photos found.', 'buddyboss' ); ?></p></div>

				<?php endif; ?>

				<?php do_action( 'bp_after_member_body' ); ?>

				<div id="is-buddyboss-pics-grid" data-url="<?php global $bp; echo $bp->loggedin_user->domain . BUDDYBOSS_PICS_SLUG . '/'; ?>"></div>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_member_plugin_template' ); ?>

	</div><!-- #buddypress -->
</div><!-- .entry-content -->

<?php get_footer( 'buddypress' ); ?>
