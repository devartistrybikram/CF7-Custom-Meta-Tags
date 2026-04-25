<?php
/**
 * Tracking and cookie persistence.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_Tracking
{
	/**
	 * Settings service.
	 *
	 * @var CF7CMT_Settings
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @param CF7CMT_Settings $settings Settings service.
	 */
	public function __construct($settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks()
	{
		add_action('template_redirect', array($this, 'persist_request_context'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
	}

	/**
	 * Persist page context and tracking values.
	 *
	 * @return void
	 */
	public function persist_request_context()
	{
		if (is_admin() || headers_sent()) {
			return;
		}

		$page_id    = function_exists('get_queried_object_id') ? absint(get_queried_object_id()) : 0;
		$page_title = $page_id ? get_the_title($page_id) : wp_get_document_title();
		$page_url   = CF7CMT_Utils::get_current_url();
		$referrer   = esc_url_raw(wp_get_raw_referer());

		if (! empty($page_title)) {
			$this->set_cookie('page_title', sanitize_text_field($page_title), absint($this->settings->get('utm_cookie_days')));
		}

		if (! empty($page_url)) {
			$this->set_cookie('page_url', $page_url, absint($this->settings->get('utm_cookie_days')));
		}

		if (! empty($referrer)) {
			$this->set_cookie('referrer_url', $referrer, absint($this->settings->get('utm_cookie_days')));
		}

		foreach (array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content') as $utm_key) {
			if (! isset($_GET[ $utm_key ])) {
				continue;
			}

			$this->set_cookie($utm_key, sanitize_text_field(wp_unslash($_GET[ $utm_key ])), absint($this->settings->get('utm_cookie_days')));
		}

		if ('' === CF7CMT_Utils::get_cookie('submission_uuid')) {
			$this->set_cookie('submission_uuid', CF7CMT_Utils::generate_uuid(), absint($this->settings->get('submission_cookie_days')));
		}
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets()
	{
		if (is_admin()) {
			return;
		}

		wp_enqueue_script(
			'cf7cmt-frontend',
			CF7CMT_URL . 'assets/js/frontend.js',
			array(),
			CF7CMT_VERSION,
			true
		);

		wp_localize_script(
			'cf7cmt-frontend',
			'cf7cmtConfig',
			array(
				'cookiePrefix'         => 'cf7cmt_',
				'utmCookieDays'        => absint($this->settings->get('utm_cookie_days')),
				'submissionCookieDays' => absint($this->settings->get('submission_cookie_days')),
				'currentUrl'           => CF7CMT_Utils::get_current_url(),
				'pageTitle'            => wp_get_document_title(),
				'referrerUrl'          => esc_url_raw(wp_get_raw_referer()),
			)
		);
	}

	/**
	 * Set a client-readable cookie.
	 *
	 * @param string $key   Cookie key.
	 * @param string $value Cookie value.
	 * @param int    $days  Cookie lifetime in days.
	 * @return void
	 */
	protected function set_cookie($key, $value, $days)
	{
		$cookie_name = CF7CMT_Utils::get_cookie_name($key);
		$expires     = time() + ($days * DAY_IN_SECONDS);
		$path        = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
		$domain      = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

		setcookie($cookie_name, $value, $expires, $path, $domain, is_ssl(), false);
		$_COOKIE[ $cookie_name ] = $value;
	}
}

