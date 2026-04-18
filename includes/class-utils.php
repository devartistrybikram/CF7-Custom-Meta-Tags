<?php
/**
 * Utility helpers.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_Utils
{
	/**
	 * Get supported tags.
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function get_supported_tags()
	{
		return array(
			'page_title'    => array(
				'label'       => __('Page Title', 'cf7-custom-meta-tags'),
				'description' => __('The title of the page where the form is embedded.', 'cf7-custom-meta-tags'),
				'group'       => 'page',
			),
			'page_id'       => array(
				'label'       => __('Page ID', 'cf7-custom-meta-tags'),
				'description' => __('The current WordPress post or page ID.', 'cf7-custom-meta-tags'),
				'group'       => 'page',
			),
			'page_url'      => array(
				'label'       => __('Page URL', 'cf7-custom-meta-tags'),
				'description' => __('The current page URL.', 'cf7-custom-meta-tags'),
				'group'       => 'page',
			),
			'referrer_url'  => array(
				'label'       => __('Referrer URL', 'cf7-custom-meta-tags'),
				'description' => __('The previous page URL when available.', 'cf7-custom-meta-tags'),
				'group'       => 'page',
			),
			'form_id'       => array(
				'label'       => __('Form ID', 'cf7-custom-meta-tags'),
				'description' => __('The Contact Form 7 form ID.', 'cf7-custom-meta-tags'),
				'group'       => 'form',
			),
			'form_title'    => array(
				'label'       => __('Form Title', 'cf7-custom-meta-tags'),
				'description' => __('The Contact Form 7 form title.', 'cf7-custom-meta-tags'),
				'group'       => 'form',
			),
			'submission_id' => array(
				'label'       => __('Submission ID', 'cf7-custom-meta-tags'),
				'description' => __('A UUID-based identifier that persists until a successful submission rotates it.', 'cf7-custom-meta-tags'),
				'group'       => 'form',
			),
			'user_ip'       => array(
				'label'       => __('User IP Address', 'cf7-custom-meta-tags'),
				'description' => __('The best available client IP address.', 'cf7-custom-meta-tags'),
				'group'       => 'user',
			),
			'user_agent'    => array(
				'label'       => __('User Agent', 'cf7-custom-meta-tags'),
				'description' => __('Browser and device user agent string.', 'cf7-custom-meta-tags'),
				'group'       => 'user',
			),
			'geo_city'      => array(
				'label'       => __('Geo City', 'cf7-custom-meta-tags'),
				'description' => __('Detected city from IP lookup.', 'cf7-custom-meta-tags'),
				'group'       => 'geo',
			),
			'geo_country'   => array(
				'label'       => __('Geo Country', 'cf7-custom-meta-tags'),
				'description' => __('Detected country from IP lookup.', 'cf7-custom-meta-tags'),
				'group'       => 'geo',
			),
			'utm_source'    => array(
				'label'       => __('UTM Source', 'cf7-custom-meta-tags'),
				'description' => __('The persisted utm_source query parameter.', 'cf7-custom-meta-tags'),
				'group'       => 'utm',
			),
			'utm_medium'    => array(
				'label'       => __('UTM Medium', 'cf7-custom-meta-tags'),
				'description' => __('The persisted utm_medium query parameter.', 'cf7-custom-meta-tags'),
				'group'       => 'utm',
			),
			'utm_campaign'  => array(
				'label'       => __('UTM Campaign', 'cf7-custom-meta-tags'),
				'description' => __('The persisted utm_campaign query parameter.', 'cf7-custom-meta-tags'),
				'group'       => 'utm',
			),
			'utm_term'      => array(
				'label'       => __('UTM Term', 'cf7-custom-meta-tags'),
				'description' => __('The persisted utm_term query parameter.', 'cf7-custom-meta-tags'),
				'group'       => 'utm',
			),
			'utm_content'   => array(
				'label'       => __('UTM Content', 'cf7-custom-meta-tags'),
				'description' => __('The persisted utm_content query parameter.', 'cf7-custom-meta-tags'),
				'group'       => 'utm',
			),
		);
	}

	/**
	 * Get tag groups.
	 *
	 * @return array<string,string>
	 */
	public static function get_tag_groups()
	{
		return array(
			'page' => __('Page Metadata', 'cf7-custom-meta-tags'),
			'form' => __('Form Metadata', 'cf7-custom-meta-tags'),
			'user' => __('User Metadata', 'cf7-custom-meta-tags'),
			'geo'  => __('Geo Metadata', 'cf7-custom-meta-tags'),
			'utm'  => __('Marketing / Tracking (UTM)', 'cf7-custom-meta-tags'),
		);
	}

	/**
	 * Whether Contact Form 7 is available.
	 *
	 * @return bool
	 */
	public static function is_cf7_active()
	{
		return class_exists('WPCF7_ContactForm') && function_exists('wpcf7_add_form_tag');
	}

	/**
	 * Get cookie name for a key.
	 *
	 * @param string $key Cookie key.
	 * @return string
	 */
	public static function get_cookie_name($key)
	{
		return 'cf7cmt_' . sanitize_key($key);
	}

	/**
	 * Read a cookie value.
	 *
	 * @param string $key Cookie key.
	 * @return string
	 */
	public static function get_cookie($key)
	{
		$name = self::get_cookie_name($key);

		if (! isset($_COOKIE[ $name ])) {
			return '';
		}

		return sanitize_text_field(rawurldecode(wp_unslash($_COOKIE[ $name ])));
	}

	/**
	 * Build a best-effort current URL.
	 *
	 * @return string
	 */
	public static function get_current_url()
	{
		$request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
		$host        = isset($_SERVER['HTTP_HOST']) ? wp_unslash($_SERVER['HTTP_HOST']) : '';
		$scheme      = is_ssl() ? 'https://' : 'http://';

		if (empty($host) || empty($request_uri)) {
			return '';
		}

		return esc_url_raw($scheme . $host . $request_uri);
	}

	/**
	 * Generate a UUID.
	 *
	 * @return string
	 */
	public static function generate_uuid()
	{
		if (function_exists('wp_generate_uuid4')) {
			return wp_generate_uuid4();
		}

		$data = wp_rand();

		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			$data,
			wp_rand(0, 0xffff),
			wp_rand(0, 0xffff),
			wp_rand(0, 0x0fff) | 0x4000,
			wp_rand(0, 0x3fff) | 0x8000,
			wp_rand(0, 0xffff),
			wp_rand(0, 0xffff),
			wp_rand(0, 0xffff)
		);
	}

	/**
	 * Get the best available client IP address.
	 *
	 * @return string
	 */
	public static function get_client_ip()
	{
		$candidates = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_REAL_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'REMOTE_ADDR',
		);

		foreach ($candidates as $server_key) {
			if (empty($_SERVER[ $server_key ])) {
				continue;
			}

			$raw_value = sanitize_text_field(wp_unslash($_SERVER[ $server_key ]));
			$parts     = array_map('trim', explode(',', $raw_value));

			foreach ($parts as $part) {
				if (filter_var($part, FILTER_VALIDATE_IP)) {
					return $part;
				}
			}
		}

		return '';
	}
}
