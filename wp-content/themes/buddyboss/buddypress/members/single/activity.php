<?php

/**
 * BuddyPress - Users Activity
 *
 * @package BuddyPress
 * @subpackage buddyboss
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>

		<?php bp_get_options_nav(); ?>

		<?php if (BUDDYBOSS_WALL_ENABLED) : ?>

		<li id="activity-filter-select" class="last">
			<label for="activity-filter-by"><?php _e( 'Show:', 'buddyboss' ); ?></label>
			<select id="activity-filter-by">
				<option value="-1"><?php _e( 'Everything', 'buddyboss' ); ?></option>
				<option value="activity_update"><?php _e( 'Updates', 'buddyboss' ); ?></option>

				<?php
				if ( !bp_is_current_action( 'groups' ) ) :
					if ( bp_is_active( 'blogs' ) ) : ?>

						<option value="new_blog_post"><?php _e( 'Posts', 'buddyboss' ); ?></option>
						<option value="new_blog_comment"><?php _e( 'Comments', 'buddyboss' ); ?></option>

					<?php
					endif;

					if ( bp_is_active( 'friends' ) ) : ?>

						<option value="friendship_accepted,friendship_created"><?php _e( 'Friendships', 'buddyboss' ); ?></option>

					<?php endif;

				endif;

				if ( bp_is_active( 'forums' ) ) : ?>

					<option value="new_forum_topic"><?php _e( 'Forum Topics', 'buddyboss' ); ?></option>
					<option value="new_forum_post"><?php _e( 'Forum Replies', 'buddyboss' ); ?></option>

				<?php endif;

				if ( bp_is_active( 'groups' ) ) : ?>

					<option value="created_group"><?php _e( 'New Groups', 'buddyboss' ); ?></option>
					<option value="joined_group"><?php _e( 'Group Memberships', 'buddyboss' ); ?></option>

				<?php endif;

				do_action( 'bp_member_activity_filter_options' ); ?>

			</select>
		</li>

		<?php endif; ?>

	</ul>
</div><!-- .item-list-tabs -->

<?php do_action( 'bp_before_member_activity_post_form' ) ?>

<?php if ( BUDDYBOSS_WALL_ENABLED && ( '' == bp_current_action() || 'just-me' == bp_current_action() )
           || !BUDDYBOSS_WALL_ENABLED && bp_is_my_profile() ) : ?>

    <?php bp_get_template_part( 'activity/post-form' ) ?>

<?php endif; ?>

<?php do_action( 'bp_after_member_activity_post_form' ) ?>

<?php do_action( 'bp_before_member_activity_content' ) ?>

<div class="activity" role="main">

	<?php bp_get_template_part( 'activity/activity-loop' ) ?>

</div><!-- .activity -->

<?php do_action( 'bp_after_member_activity_content' ) ?>
