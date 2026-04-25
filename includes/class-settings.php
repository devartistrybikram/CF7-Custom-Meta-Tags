<?php
/**
 * Settings service.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_Settings
{
	/**
	 * Settings option key.
	 */
	const OPTION_KEY = 'cf7cmt_settings';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks()
	{
		$this->ensure_defaults();
	}

	/**
	 * Ensure defaults exist.
	 *
	 * @return void
	 */
	public function ensure_defaults()
	{
		if (false === get_option(self::OPTION_KEY, false)) {
			add_option(self::OPTION_KEY, $this->get_defaults());
		}
	}

	/**
	 * Get defaults.
	 *
	 * @return array<string,mixed>
	 */
	public function get_defaults()
	{
		$enabled_tags = array();

		foreach (CF7CMT_Utils::get_supported_tags() as $tag => $config) {
			$enabled_tags[ $tag ] = 1;
		}

		return array(
			'enabled_tags'           => $enabled_tags,
			'enable_geo_lookup'      => 1,
			'geo_primary_provider'   => 'ip-api',
			'geo_fallback_provider'  => 'ipapi',
			'geo_cache_hours'        => 24,
			'geo_request_timeout'    => 5,
			'utm_cookie_days'        => 30,
			'submission_cookie_days' => 1,
		);
	}

	/**
	 * Get all settings.
	 *
	 * @return array<string,mixed>
	 */
	public function all()
	{
		return wp_parse_args((array) get_option(self::OPTION_KEY, array()), $this->get_defaults());
	}

	/**
	 * Get a setting by key.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public function get($key)
	{
		$settings = $this->all();

		return isset($settings[ $key ]) ? $settings[ $key ] : null;
	}

	/**
	 * Check whether a tag is enabled.
	 *
	 * @param string $tag Tag name.
	 * @return bool
	 */
	public function is_tag_enabled($tag)
	{
		$settings = $this->all();

		return ! empty($settings['enabled_tags'][ $tag ]);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 * @return array<string,mixed>
	 */
	public function sanitize($settings)
	{
		$defaults      = $this->get_defaults();
		$supported     = array_keys(CF7CMT_Utils::get_supported_tags());
		$enabled_input = isset($settings['enabled_tags']) && is_array($settings['enabled_tags']) ? $settings['enabled_tags'] : array();
		$enabled_tags  = array();

		foreach ($supported as $tag) {
			$enabled_tags[ $tag ] = empty($enabled_input[ $tag ]) ? 0 : 1;
		}

		$primary_provider  = isset($settings['geo_primary_provider']) ? sanitize_key($settings['geo_primary_provider']) : $defaults['geo_primary_provider'];
		$fallback_provider = isset($settings['geo_fallback_provider']) ? sanitize_key($settings['geo_fallback_provider']) : $defaults['geo_fallback_provider'];
		$allowed_providers = array('ip-api', 'ipapi', 'none');

		if (! in_array($primary_provider, $allowed_providers, true)) {
			$primary_provider = $defaults['geo_primary_provider'];
		}

		if (! in_array($fallback_provider, $allowed_providers, true)) {
			$fallback_provider = $defaults['geo_fallback_provider'];
		}

		return array(
			'enabled_tags'           => $enabled_tags,
			'enable_geo_lookup'      => empty($settings['enable_geo_lookup']) ? 0 : 1,
			'geo_primary_provider'   => $primary_provider,
			'geo_fallback_provider'  => $fallback_provider,
			'geo_cache_hours'        => max(1, min(168, absint(isset($settings['geo_cache_hours']) ? $settings['geo_cache_hours'] : $defaults['geo_cache_hours']))),
			'geo_request_timeout'    => max(1, min(20, absint(isset($settings['geo_request_timeout']) ? $settings['geo_request_timeout'] : $defaults['geo_request_timeout']))),
			'utm_cookie_days'        => max(1, min(365, absint(isset($settings['utm_cookie_days']) ? $settings['utm_cookie_days'] : $defaults['utm_cookie_days']))),
			'submission_cookie_days' => max(1, min(30, absint(isset($settings['submission_cookie_days']) ? $settings['submission_cookie_days'] : $defaults['submission_cookie_days']))),
		);
	}
}


