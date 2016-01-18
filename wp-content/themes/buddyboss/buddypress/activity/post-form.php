<?php

/**
 * BuddyPress - Activity Post Form
 *
 * @package BuddyPress
 * @subpackage buddyboss
 */

?>

<?php global $buddyboss_wall, $bp; ?>

<?php if ( bp_is_group() || bp_is_my_profile() || bp_is_current_component( 'activity' ) ): ?>

	<?php if ( !is_user_logged_in() ) : ?>

		<div id="message">
			<p><?php printf( __( 'You need to <a href="%s" title="Log in">log in</a>', 'buddyboss' ), wp_login_url() ); ?><?php if ( bp_get_signup_allowed() ) : ?><?php printf( __( ' or <a class="create-account" href="%s" title="Create an account">create an account</a>', 'buddyboss' ), bp_get_signup_page() ); ?><?php endif; ?><?php _e( ' to post to this user\'s Wall.', 'buddyboss' ); ?></p>
		</div>

	<?php elseif (!bp_is_my_profile() && (!is_super_admin() && !buddyboss_is_admin()) && (bp_is_user() && (BUDDYBOSS_WALL_ENABLED && !$buddyboss_wall->is_friend($bp->displayed_user->id)) )):?>

		<div id="message" class="info">
			<p><?php printf( __( "You and %s are not friends. Request friendship to post to their Wall.", 'buddyboss' ), bp_get_displayed_user_fullname() ) ?></p>
		</div>

	<?php else:?>

		<?php if ( is_user_logged_in() ) : ?>

			<?php if ( isset( $_GET['r'] ) ) : ?>
				<div id="message" class="info">
					<p><?php printf( __( 'You are mentioning %s in a new update, this user will be sent a notification of your message.', 'buddyboss' ), bp_get_mentioned_user_display_name( $_GET['r'] ) ) ?></p>
				</div>
			<?php endif; ?>

			<form action="<?php bp_activity_post_form_action(); ?>" method="post" id="whats-new-form" name="whats-new-form" role="complementary">

			<?php do_action( 'bp_before_activity_post_form' ); ?>

				<p class="activity-greeting">

					<?php if ( bp_is_group() ) : ?>

						<?php printf( __( "What's new in %s, %s?", 'buddyboss' ), bp_get_group_name(), bp_get_user_firstname() );?>

					<?php elseif ( bp_is_page( BP_ACTIVITY_SLUG ) || bp_is_my_profile() && bp_is_user_activity() ): ?>

						<?php printf( __( "What's new, %s?", 'buddyboss' ), bp_get_user_firstname() );?>

					<?php elseif( !bp_is_my_profile() && bp_is_user_activity() ): ?>

						<?php printf( __( "Write something to %s?", 'buddyboss' ), bp_get_displayed_user_fullname() ) ;?>

					<?php else :?>

						<?php printf( __( "Add a photo, %s", 'buddyboss' ), bp_get_user_firstname() );?>

					<?php endif; ?>

				</p>

				<div id="buddyboss-pics-add-photo">
					<?php if (BUDDYBOSS_PICS_ENABLED): ?>
						<div id="buddyboss-pics-add-photo-button"><?php _e( 'Add Photo', 'buddyboss' ); ?></div>
						<div class="buddyboss-pics-progress">
							<div class="buddyboss-pics-progress-value">0%</div>
							<progress class="buddyboss-pics-progress-bar" value="0" max="100"></progress>
						</div>
					<?php endif; ?>
					<div id="buddyboss-pics-photo-uploader"></div>
				</div><!-- #buddyboss-pics-add-photo -->

				<div id="whats-new-content">
					<div id="whats-new-textarea">
						<textarea name="whats-new" id="whats-new" cols="50" rows="10"><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_attr( $_GET['r'] ); ?> <?php endif; ?></textarea>
					</div>

					<div id="whats-new-options">
						<div id="whats-new-submit">
							<button type="submit" name="aw-whats-new-submit" id="aw-whats-new-submit"><?php _e( 'Post Update', 'buddyboss' ); ?></button>
						</div>

						<?php if ( bp_is_active( 'groups' ) && !bp_is_my_profile() && !bp_is_group() && !bp_is_user_activity() ) : ?>

							<div id="whats-new-post-in-box">

								<div class="whats-new-select no-ajax" id="filter">
			            <ul>
			                <li id="members-order-select" class="last filter">
			                    <label for="members-order-by"><?php _e( 'Post in:', 'buddyboss' ); ?></label>
			                    <select id="whats-new-post-in" name="whats-new-post-in">
			                        <option selected="selected" value="0"><?php _e( 'My Profile', 'buddyboss' ); ?></option>

			                        <?php if ( bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0' ) ) :
			                            while ( bp_groups() ) : bp_the_group(); ?>

			                                <option value="<?php bp_group_id(); ?>"><?php bp_group_name(); ?></option>

			                            <?php endwhile;
			                        endif; ?>

			                    </select>
			                </li>
			            </ul>
				        </div> <!-- /.whats-new-select -->
							</div> <!-- /.whats-new-post-in-box -->

							<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />

						<?php elseif ( bp_is_group_home() ) : ?>

							<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />
							<input type="hidden" id="whats-new-post-in" name="whats-new-post-in" value="<?php bp_group_id(); ?>" />

						<?php endif; ?>

						<?php do_action( 'bp_activity_post_form_options' ); ?>

					</div><!-- #whats-new-options -->

					<div class="clearfix" id="buddyboss-pics-preview">
						<div class="clearfix" id="buddyboss-pics-preview-inner"></div>
					</div><!-- #buddyboss-pics-preview -->

				</div><!-- #whats-new-content -->

				<?php wp_nonce_field( 'post_update', '_wpnonce_post_update' ); ?>
				<?php do_action( 'bp_after_activity_post_form' ); ?>

			</form><!-- #whats-new-form -->

		<?php endif; ?>

	<?php endif; ?>
<?php endif; ?>
