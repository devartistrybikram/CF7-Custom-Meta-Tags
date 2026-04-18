<?php
/**
 * Metadata context builder.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_Context
{
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
	 * Constructor.
	 *
	 * @param CF7CMT_Settings $settings Settings service.
	 * @param CF7CMT_Geo      $geo      Geo service.
	 */
	public function __construct($settings, $geo)
	{
		$this->settings = $settings;
		$this->geo      = $geo;
	}

	/**
	 * Build metadata.
	 *
	 * @param WPCF7_ContactForm|null $form        Contact form object.
	 * @param array<string,mixed>    $posted_data Posted data.
	 * @return array<string,string>
	 */
	public function build($form = null, $posted_data = array())
	{
		$page_id = 0;

		if (! empty($posted_data['page_id'])) {
			$page_id = absint($posted_data['page_id']);
		} elseif (! empty($posted_data['_wpcf7_container_post'])) {
			$page_id = absint($posted_data['_wpcf7_container_post']);
		} elseif (function_exists('get_queried_object_id')) {
			$page_id = absint(get_queried_object_id());
		}

		if (! $form && ! empty($posted_data['_wpcf7']) && function_exists('wpcf7_contact_form')) {
			$form = wpcf7_contact_form(absint($posted_data['_wpcf7']));
		}

		$form_id       = $form ? (string) absint($form->id()) : '';
		$form_title    = $form ? sanitize_text_field($form->title()) : '';
		$current_url   = CF7CMT_Utils::get_current_url();
		$referrer_url  = '';
		$cookie_source = array(
			'page_title'   => CF7CMT_Utils::get_cookie('page_title'),
			'page_url'     => CF7CMT_Utils::get_cookie('page_url'),
			'referrer_url' => CF7CMT_Utils::get_cookie('referrer_url'),
			'utm_source'   => CF7CMT_Utils::get_cookie('utm_source'),
			'utm_medium'   => CF7CMT_Utils::get_cookie('utm_medium'),
			'utm_campaign' => CF7CMT_Utils::get_cookie('utm_campaign'),
			'utm_term'     => CF7CMT_Utils::get_cookie('utm_term'),
			'utm_content'  => CF7CMT_Utils::get_cookie('utm_content'),
		);

		if (! empty($posted_data['referrer_url'])) {
			$referrer_url = esc_url_raw((string) $posted_data['referrer_url']);
		} elseif (! empty($cookie_source['referrer_url'])) {
			$referrer_url = esc_url_raw($cookie_source['referrer_url']);
		} else {
			$referrer_url = esc_url_raw(wp_get_raw_referer());
		}

		$submission_id = '';

		if (! empty($posted_data['submission_id'])) {
			$submission_id = sanitize_text_field((string) $posted_data['submission_id']);
		} else {
			$submission_id = CF7CMT_Utils::get_cookie('submission_uuid');
		}

		if (empty($submission_id)) {
			$submission_id = CF7CMT_Utils::generate_uuid();
		}

		$user_ip      = ! empty($posted_data['user_ip']) ? sanitize_text_field((string) $posted_data['user_ip']) : CF7CMT_Utils::get_client_ip();
		$user_agent   = '';
		$geo_location = array(
			'city'    => '',
			'country' => '',
		);

		if (! empty($posted_data['user_agent'])) {
			$user_agent = sanitize_text_field((string) $posted_data['user_agent']);
		} elseif (! empty($_SERVER['HTTP_USER_AGENT'])) {
			$user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
		}

		if (
			$this->settings->is_tag_enabled('geo_city')
			|| $this->settings->is_tag_enabled('geo_country')
		) {
			$geo_location = $this->geo->get_location($user_ip);
		}

		$metadata = array(
			'page_title'    => $this->get_tag_value($posted_data, 'page_title', $page_id ? get_the_title($page_id) : $cookie_source['page_title']),
			'page_id'       => $page_id ? (string) $page_id : '',
			'page_url'      => $this->get_tag_value($posted_data, 'page_url', $current_url ? $current_url : ($page_id ? get_permalink($page_id) : $cookie_source['page_url'])),
			'referrer_url'  => $referrer_url,
			'form_id'       => $this->get_tag_value($posted_data, 'form_id', $form_id),
			'form_title'    => $this->get_tag_value($posted_data, 'form_title', $form_title),
			'submission_id' => $submission_id,
			'user_ip'       => $user_ip,
			'user_agent'    => $user_agent,
			'geo_city'      => $this->get_tag_value($posted_data, 'geo_city', $geo_location['city']),
			'geo_country'   => $this->get_tag_value($posted_data, 'geo_country', $geo_location['country']),
			'utm_source'    => $this->get_utm_value($posted_data, 'utm_source', $cookie_source['utm_source']),
			'utm_medium'    => $this->get_utm_value($posted_data, 'utm_medium', $cookie_source['utm_medium']),
			'utm_campaign'  => $this->get_utm_value($posted_data, 'utm_campaign', $cookie_source['utm_campaign']),
			'utm_term'      => $this->get_utm_value($posted_data, 'utm_term', $cookie_source['utm_term']),
			'utm_content'   => $this->get_utm_value($posted_data, 'utm_content', $cookie_source['utm_content']),
		);

		foreach ($metadata as $tag => $value) {
			if (! $this->settings->is_tag_enabled($tag)) {
				$metadata[ $tag ] = '';
				continue;
			}

			if (in_array($tag, array('page_url', 'referrer_url'), true)) {
				$metadata[ $tag ] = esc_url_raw((string) $value);
				continue;
			}

			$metadata[ $tag ] = sanitize_text_field((string) $value);
		}

		return $metadata;
	}

	/**
	 * Get a field value from posted data or fallback.
	 *
	 * @param array<string,mixed> $posted_data Posted data.
	 * @param string              $tag         Tag name.
	 * @param string              $fallback    Fallback value.
	 * @return string
	 */
	protected function get_tag_value($posted_data, $tag, $fallback)
	{
		if (isset($posted_data[ $tag ])) {
			return (string) $posted_data[ $tag ];
		}

		return (string) $fallback;
	}

	/**
	 * Get a UTM value.
	 *
	 * @param array<string,mixed> $posted_data Posted data.
	 * @param string              $tag         Tag name.
	 * @param string              $cookie      Cookie fallback.
	 * @return string
	 */
	protected function get_utm_value($posted_data, $tag, $cookie)
	{
		if (isset($posted_data[ $tag ])) {
			return (string) $posted_data[ $tag ];
		}

		if (isset($_GET[ $tag ])) {
			return sanitize_text_field(wp_unslash($_GET[ $tag ]));
		}

		return (string) $cookie;
	}
}
