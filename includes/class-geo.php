<?php
/**
 * Geo lookup service.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_Geo
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
	 * Get location by IP.
	 *
	 * @param string $ip IP address.
	 * @return array<string,string>
	 */
	public function get_location($ip)
	{
		$empty = array(
			'city'      => '',
			'country'   => '',
			'latitude'  => '',
			'longitude' => '',
		);

		if (empty($ip) || ! $this->settings->get('enable_geo_lookup')) {
			return $empty;
		}
		
		if ($ip === '127.0.0.1' || $ip === '::1') {
			return array(
				'city'    => 'Localhost',
				'country' => 'Development',
				'latitude'  => '0.0000',
				'longitude' => '0.0000',
			);
		}

		if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			return $empty;
		}

		$cache_key = 'cf7cmt_geo_' . md5($ip);
		$cached    = get_transient($cache_key);

		if (is_array($cached)) {
			return wp_parse_args($cached, $empty);
		}

		$providers = array_unique(
			array(
				(string) $this->settings->get('geo_primary_provider'),
				(string) $this->settings->get('geo_fallback_provider'),
			)
		);

		foreach ($providers as $provider) {
			if ('none' === $provider || '' === $provider) {
				continue;
			}

			$location = $this->request_provider($provider, $ip);

			if (! empty($location['city']) || ! empty($location['country'])) {
				set_transient($cache_key, $location, $this->get_cache_ttl());

				return wp_parse_args($location, $empty);
			}
		}

		set_transient($cache_key, $empty, $this->get_cache_ttl());

		return $empty;
	}

	/**
	 * Get cache TTL.
	 *
	 * @return int
	 */
	protected function get_cache_ttl()
	{
		return absint($this->settings->get('geo_cache_hours')) * HOUR_IN_SECONDS;
	}

	/**
	 * Query a provider.
	 *
	 * @param string $provider Provider name.
	 * @param string $ip       IP address.
	 * @return array<string,string>
	 */
	protected function request_provider($provider, $ip)
	{
		$response = null;

		if ('ip-api' === $provider) {
			$url      = sprintf('http://ip-api.com/json/%1$s?fields=status,country,city,lat,lon', rawurlencode($ip));
			$response = wp_remote_get(
				esc_url_raw($url),
				array(
					'timeout' => absint($this->settings->get('geo_request_timeout')),
				)
			);
		} elseif ('ipapi' === $provider) {
			$url      = sprintf('https://ipapi.co/%1$s/json/', rawurlencode($ip));
			$response = wp_remote_get(
				esc_url_raw($url),
				array(
					'timeout' => absint($this->settings->get('geo_request_timeout')),
				)
			);
		}

		if (is_wp_error($response) || empty($response)) {
			return array(
				'city'    => '',
				'country' => '',
			);
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (! is_array($data)) {
			return array(
				'city'    => '',
				'country' => '',
			);
		}

		if ('ip-api' === $provider && isset($data['status']) && 'success' !== $data['status']) {
			return array(
				'city'    => '',
				'country' => '',
			);
		}

		return array(
    		'city'      => isset($data['city']) ? sanitize_text_field((string) $data['city']) : '',
			'country'   => isset($data['country']) ? sanitize_text_field((string) $data['country']) : '',
			'latitude'  => isset($data['lat']) ? sanitize_text_field((string) $data['lat']) : (isset($data['latitude']) ? sanitize_text_field((string) $data['latitude']) : ''),
			'longitude' => isset($data['lon']) ? sanitize_text_field((string) $data['lon']) : (isset($data['longitude']) ? sanitize_text_field((string) $data['longitude']) : ''),
		);
	}
}


