<?php

/**
 * BuddyPress - Activity Stream File Loader (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage buddyboss
 */

if (BUDDYBOSS_WALL_ENABLED):

	// If the BuddyBoss wall is enabled, we need the custom entry-wall.php
	locate_template( array( 'buddypress/activity/entry-wall.php' ), true, false );

else:

	// If the BuddyBoss wall isn't enabled, load the default entry.php
	locate_template( array( 'buddypress/activity/entry-default.php' ), true, false );

endif; ?>