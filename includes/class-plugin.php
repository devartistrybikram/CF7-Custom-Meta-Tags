<?php
/**
 * Plugin bootstrap.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_Plugin
{
	/**
	 * Singleton instance.
	 *
	 * @var CF7CMT_Plugin|null
	 */
	protected static $instance = null;

	/**
	 * Settings service.
	 *
	 * @var CF7CMT_Settings
	 */
	protected $settings;

	/**
	 * Geo service.
	 *
	 * @var CF7CMT_Geo
	 */
	protected $geo;

	/**
	 * Context builder.
	 *
	 * @var CF7CMT_Context
	 */
	protected $context;

	/**
	 * Tracking service.
	 *
	 * @var CF7CMT_Tracking
	 */
	protected $tracking;

	/**
	 * Admin service.
	 *
	 * @var CF7CMT_Admin
	 */
	protected $admin;

	/**
	 * CF7 integration.
	 *
	 * @var CF7CMT_CF7_Integration
	 */
	protected $cf7;

	/**
	 * Get singleton instance.
	 *
	 * @return CF7CMT_Plugin
	 */
	public static function instance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		$this->settings = new CF7CMT_Settings();
		$this->geo      = new CF7CMT_Geo($this->settings);
		$this->context  = new CF7CMT_Context($this->settings, $this->geo);
		$this->tracking = new CF7CMT_Tracking($this->settings);
		$this->admin    = new CF7CMT_Admin($this->settings);
		$this->cf7      = new CF7CMT_CF7_Integration($this->settings, $this->context);

		add_action('plugins_loaded', array($this, 'boot'));
	}

	/**
	 * Activation hook.
	 *
	 * @return void
	 */
	public static function activate()
	{
		$settings = new CF7CMT_Settings();
		$settings->ensure_defaults();
	}

	/**
	 * Boot services.
	 *
	 * @return void
	 */
	public function boot()
	{
		load_plugin_textdomain('cf7-custom-meta-tags', false, dirname(plugin_basename(CF7CMT_FILE)) . '/languages');

		$this->settings->register_hooks();
		$this->tracking->register_hooks();
		$this->admin->register_hooks();

		if (CF7CMT_Utils::is_cf7_active()) {
			$this->cf7->register_hooks();
		}
		else {
			error_log('CF7 Custom Meta Tags: Contact Form 7 not active.');
		}
	}
}
