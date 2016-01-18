<?php

/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage buddyboss
 */

	global $buddyboss_wall;
?>

<?php do_action( 'bp_before_activity_entry' ); ?>

<li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">

	<div class="activity-avatar">
		<a href="<?php bp_activity_user_link(); ?>">

			<?php bp_activity_avatar(); ?>

		</a>
	</div>

	<div class="activity-content">

		<div class="activity-header">

			<?php bp_activity_action(); ?>

		</div>

		<?php if ( 'activity_comment' == bp_get_activity_type() ) : ?>

			<div class="activity-inreplyto">
				<strong><?php _e( 'In reply to: ', 'buddyboss' ); ?></strong><?php bp_activity_parent_content(); ?> <a href="<?php bp_activity_thread_permalink(); ?>" class="view" title="<?php _e( 'View Thread / Permalink', 'buddyboss' ); ?>"><?php _e( 'View', 'buddyboss' ); ?></a>
			</div>

		<?php endif; ?>

		<?php if ( bp_activity_has_content() ) : ?>

			<div class="activity-inner">

				<?php bp_activity_content_body(); ?>

			</div>

		<?php endif; ?>

		<?php do_action( 'bp_activity_entry_content' ); ?>

	</div>

    <?php if ( is_user_logged_in() ) : ?>

        <div class="activity-meta">

            <?php if ( bp_activity_can_favorite() ) : ?>

                <?php if ( !bp_get_activity_is_favorite() ) : ?>

                    <a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e( 'Like', 'buddyboss' ); ?>"><?php _e( 'Like', 'buddyboss' ); ?></a>

                <?php else : ?>

                    <a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e( 'Unlike', 'buddyboss' ); ?>"><?php _e( 'Unlike', 'buddyboss' ); ?></a>

                <?php endif; ?>

            <?php endif; ?>

            <?php if ( bp_activity_user_can_delete() ): ?>

              <?php bp_activity_delete_link(); ?>

            <?php endif; ?>

            <?php if ( bp_activity_can_comment() ) : ?>

                <a href="<?php bp_get_activity_comment_link(); ?>" class="button acomment-reply bp-primary-action" id="acomment-comment-<?php bp_activity_id(); ?>"><?php printf( __( 'Comment <span>%s</span>', 'buddyboss' ), bp_activity_get_comment_count() ); ?></a>

            <?php endif; ?>

            <?php do_action( 'bp_activity_entry_meta' ); ?>

        </div>

    <?php endif; ?>

	<?php do_action( 'bp_before_activity_entry_comments' ); ?>

	<?php if ( ( is_user_logged_in() && bp_activity_can_comment() ) || bp_activity_get_comment_count() || $buddyboss_wall->has_likes( bp_get_activity_id() ) ) : ?>

		<div class="activity-comments">

			<?php if ( !( bp_activity_can_comment() ) || !bp_activity_get_comment_count() ) : ?>

				<?php wall_add_likes_comments(); ?>

			<?php else: ?>

				<?php bp_activity_comments(); ?>

			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : ?>

				<form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
					<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar( 'width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT ); ?></div>
					<div class="ac-reply-content">
						<div class="ac-textarea">
							<textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
						</div>
						<button type="submit" name="ac_form_submit"><?php _e( 'Post', 'buddyboss' ); ?></button> &nbsp; <a href="#" class="ac-reply-cancel desktop"><?php _e( 'or press esc to cancel', 'buddyboss' ); ?></a> <a href="#" class="ac-reply-cancel mobile"><?php _e( 'Cancel', 'buddyboss' ); ?></a>
						<input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
					</div>

					<?php do_action( 'bp_activity_entry_comments' ); ?>

					<?php wp_nonce_field( 'new_activity_comment', '_wpnonce_new_activity_comment' ); ?>

				</form>

			<?php endif; ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'bp_after_activity_entry_comments' ); ?>

</li>

<?php do_action( 'bp_after_activity_entry' ); ?>
