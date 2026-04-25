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
			'page_slug' => array(
				'label'       => __('Page Slug', 'cf7-custom-meta-tags'),
				'description' => __('The current page slug.', 'cf7-custom-meta-tags'),
				'group'       => 'page',
			),
			'user_device' => array(
				'label'       => __('User Device', 'cf7-custom-meta-tags'),
				'description' => __('Detected device type (Mobile/Desktop/Tablet).', 'cf7-custom-meta-tags'),
				'group'       => 'user',
			),
			'user_latitude' => array(
				'label'       => __('User Latitude', 'cf7-custom-meta-tags'),
				'description' => __('Browser geolocation latitude.', 'cf7-custom-meta-tags'),
				'group'       => 'geo',
			),
			'user_longitude' => array(
				'label'       => __('User Longitude', 'cf7-custom-meta-tags'),
				'description' => __('Browser geolocation longitude.', 'cf7-custom-meta-tags'),
				'group'       => 'geo',
			),
			'geo_latitude' => array(
				'label'       => __('Geo Latitude', 'cf7-custom-meta-tags'),
				'description' => __('Latitude detected from IP lookup.', 'cf7-custom-meta-tags'),
				'group'       => 'geo',
			),
			'geo_longitude' => array(
				'label'       => __('Geo Longitude', 'cf7-custom-meta-tags'),
				'description' => __('Longitude detected from IP lookup.', 'cf7-custom-meta-tags'),
				'group'       => 'geo',
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
		$ip = '';

		// 1. Cloudflare (most reliable if using CF)
		if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		// 2. X-Forwarded-For (can contain multiple IPs)
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

			foreach ($ips as $candidate) {
				$candidate = trim($candidate);

				if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
					$ip = $candidate;
					break;
				}
			}
		}

		// 3. X-Real-IP (some servers use this)
		elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}

		// 4. Fallback
		elseif (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		// Final validation
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			return '';
		}

		// Normalize localhost
		if ($ip === '::1') {
			return '127.0.0.1';
		}

		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			return '';
		}

		return sanitize_text_field($ip);
	}
}
