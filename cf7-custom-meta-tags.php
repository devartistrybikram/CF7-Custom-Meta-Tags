<?php
/**
 * Plugin Name: CF7 Custom Meta Tags
 * Description: Adds smart hidden metadata tags, UTM persistence, geo lookup, and native Contact Form 7 tag support.
 * Version: 1.0.0
 * Author: Codex
 * Text Domain: cf7-custom-meta-tags
 * Requires at least: 6.2
 * Requires PHP: 7.4
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

define('CF7CMT_VERSION', '1.0.0');
define('CF7CMT_FILE', __FILE__);
define('CF7CMT_PATH', plugin_dir_path(__FILE__));
define('CF7CMT_URL', plugin_dir_url(__FILE__));

require_once CF7CMT_PATH . 'includes/class-utils.php';
require_once CF7CMT_PATH . 'includes/class-settings.php';
require_once CF7CMT_PATH . 'includes/class-geo.php';
require_once CF7CMT_PATH . 'includes/class-context.php';
require_once CF7CMT_PATH . 'includes/class-tracking.php';
require_once CF7CMT_PATH . 'includes/class-admin.php';
require_once CF7CMT_PATH . 'includes/class-cf7-integration.php';
require_once CF7CMT_PATH . 'includes/class-plugin.php';

register_activation_hook(__FILE__, array('CF7CMT_Plugin', 'activate'));

CF7CMT_Plugin::instance();


