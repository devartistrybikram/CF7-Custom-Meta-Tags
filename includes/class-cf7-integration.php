<?php
/**
 * Contact Form 7 integration.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_CF7_Integration
{
	/**
	 * Settings service.
	 *
	 * @var CF7CMT_Settings
	 */
	protected $settings;

	/**
	 * Context builder.
	 *
	 * @var CF7CMT_Context
	 */
	protected $context;

	/**
	 * Constructor.
	 *
	 * @param CF7CMT_Settings $settings Settings service.
	 * @param CF7CMT_Context  $context  Context builder.
	 */
	public function __construct($settings, $context)
	{
		$this->settings = $settings;
		$this->context  = $context;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks()
	{
		add_action('wpcf7_init', array($this, 'register_form_tags'));
		add_filter('wpcf7_posted_data', array($this, 'inject_posted_data'), 10, 1);
		add_filter('wpcf7_special_mail_tags', array($this, 'replace_special_mail_tag'), 10, 3);
		add_filter('wpcf7_mail_components', array($this, 'replace_mail_components'), 10, 3);
	}

	/**
	 * Register CF7 form tags.
	 *
	 * @return void
	 */
	public function register_form_tags()
	{
		foreach (array_keys(CF7CMT_Utils::get_supported_tags()) as $tag) {
			wpcf7_add_form_tag(
				$tag,
				array($this, 'render_hidden_tag'),
				array(
					'display-hidden' => true,
				)
			);
		}
	}

	/**
	 * Render a hidden field tag.
	 *
	 * @param WPCF7_FormTag $tag Form tag.
	 * @return string
	 */
	public function render_hidden_tag($tag)
	{
		$tag_name = '';

		if (is_object($tag) && ! empty($tag->name)) {
			$tag_name = $tag->name;
		} elseif (is_object($tag) && ! empty($tag->basetype)) {
			$tag_name = $tag->basetype;
		} elseif (is_object($tag) && ! empty($tag->type)) {
			$tag_name = $tag->type;
		}

		if (empty($tag_name) || ! $this->settings->is_tag_enabled($tag_name)) {
			return '';
		}

		$form = null;
		// Try from tag (newer CF7)
		if (is_object($tag) && method_exists($tag, 'get_contact_form')) {
			$form = $tag->get_contact_form();
		}

		// Fallback (global current form)
		if (!$form && function_exists('wpcf7_get_current_contact_form')) {
			$form = wpcf7_get_current_contact_form();
		}

		$data = $this->context->build($form);
		$atts = array(
			'type'  => 'hidden',
			'name'  => $tag_name,
			'value' => isset($data[ $tag_name ]) ? $data[ $tag_name ] : '',
			'class' => 'wpcf7-form-control wpcf7-hidden cf7cmt-field cf7cmt-field--' . sanitize_html_class($tag_name),
		);

		if (function_exists('wpcf7_format_atts')) {
			return sprintf('<input %1$s />', wpcf7_format_atts($atts));
		}

		return sprintf(
			'<input type="hidden" name="%1$s" value="%2$s" class="%3$s" />',
			esc_attr($atts['name']),
			esc_attr($atts['value']),
			esc_attr($atts['class'])
		);
	}

	/**
	 * Inject metadata into the posted data pipeline.
	 *
	 * @param array<string,mixed> $posted_data Posted data.
	 * @return array<string,mixed>
	 */
	public function inject_posted_data($posted_data)
	{
		if (! is_array($posted_data)) {
			$posted_data = array();
		}

		$form = null;

		if (! empty($posted_data['_wpcf7']) && function_exists('wpcf7_contact_form')) {
			$form = wpcf7_contact_form(absint($posted_data['_wpcf7']));
		}

		$metadata = $this->context->build($form, $posted_data);

		foreach ($metadata as $tag => $value) {
			if (! $this->settings->is_tag_enabled($tag)) {
				continue;
			}

			if (! isset($posted_data[ $tag ]) || '' === $posted_data[ $tag ]) {
				$posted_data[ $tag ] = $value;
			}
		}

		return $posted_data;
	}

	/**
	 * Replace special mail tags.
	 *
	 * @param string $output Current output.
	 * @param string $name   Tag name.
	 * @param bool   $html   HTML flag.
	 * @return string
	 */
	public function replace_special_mail_tag($output, $name, $html = false)
	{
		$normalized = ltrim((string) $name, '_');

		if (! array_key_exists($normalized, CF7CMT_Utils::get_supported_tags())) {
			return $output;
		}

		if (! $this->settings->is_tag_enabled($normalized)) {
			return '';
		}

		$posted_data = isset($_POST) && is_array($_POST) ? wp_unslash($_POST) : array();
		$form        = null;

		if (! empty($posted_data['_wpcf7']) && function_exists('wpcf7_contact_form')) {
			$form = wpcf7_contact_form(absint($posted_data['_wpcf7']));
		}

		$metadata = $this->context->build($form, $posted_data);

		return isset($metadata[ $normalized ]) ? (string) $metadata[ $normalized ] : $output;
	}

	/**
	 * Replace supported tags in mail components.
	 *
	 * @param array<string,mixed>    $components Mail components.
	 * @param WPCF7_ContactForm|null $form       Contact form.
	 * @param WPCF7_Mail|null        $mail       Mail object.
	 * @return array<string,mixed>
	 */
	public function replace_mail_components($components, $form = null, $mail = null)
	{
		$posted_data = isset($_POST) && is_array($_POST) ? wp_unslash($_POST) : array();
		$metadata    = $this->context->build($form, $posted_data);
		$replace_map = array();

		foreach ($metadata as $tag => $value) {
			if (! $this->settings->is_tag_enabled($tag)) {
				continue;
			}

			$replace_map[ '[' . $tag . ']' ]  = (string) $value;
			$replace_map[ '[_' . $tag . ']' ] = (string) $value;
		}

		foreach ($components as $key => $value) {
			$components[ $key ] = $this->replace_component_value($value, $replace_map);
		}

		return $components;
	}

	/**
	 * Replace tags in a component value.
	 *
	 * @param mixed                $value       Component value.
	 * @param array<string,string> $replace_map Replacement map.
	 * @return mixed
	 */
	protected function replace_component_value($value, $replace_map)
	{
		if (is_string($value)) {
			return strtr($value, $replace_map);
		}

		if (is_array($value)) {
			foreach ($value as $key => $item) {
				$value[ $key ] = $this->replace_component_value($item, $replace_map);
			}
		}

		return $value;
	}
}
