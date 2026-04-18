<?php
/**
 * Uninstall cleanup.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

delete_option('cf7cmt_settings');
