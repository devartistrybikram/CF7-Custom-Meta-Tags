<?php
/**
 * Admin UI.
 *
 * @package CF7CustomMetaTags
 */

if (! defined('ABSPATH')) {
	exit;
}

class CF7CMT_Admin
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
		add_action('admin_menu', array($this, 'register_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
		add_action('admin_notices', array($this, 'maybe_render_cf7_notice'));
		add_filter('plugin_action_links_' . plugin_basename(CF7CMT_FILE), array($this, 'add_settings_link'));

		if (CF7CMT_Utils::is_cf7_active()) {
			add_action('wpcf7_admin_init', array($this, 'register_tag_generator'));
		}

		add_action('after_plugin_row_' . plugin_basename(CF7CMT_FILE), array($this, 'plugin_row_notice'), 10, 2);
	}
	
	public function plugin_row_notice($plugin_file, $plugin_data)
	{
		if (CF7CMT_Utils::is_cf7_active()) {
			return;
		}

		$colspan = 4;

		echo '<tr class="plugin-update-tr active">';
		echo '<td colspan="' . esc_attr($colspan) . '" class="plugin-update colspanchange">';
		echo '<div class="update-message notice inline notice-error notice-alt">';
		echo '<p><strong>CF7 Custom Meta Tags:</strong> This plugin requires Contact Form 7 to function.</p>';
		echo '</div>';
		echo '</td>';
		echo '</tr>';
	}
	/**
	 * Add settings link.
	 *
	 * @param array<int,string> $links Existing links.
	 * @return array<int,string>
	 */
	public function add_settings_link($links)
	{
		array_unshift(
			$links,
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url(admin_url('options-general.php?page=cf7-custom-meta-tags')),
				esc_html__('Settings', 'cf7-custom-meta-tags')
			)
		);

		return $links;
	}

	/**
	 * Render CF7 dependency notice.
	 *
	 * @return void
	 */
	public function maybe_render_cf7_notice()
	{
		if (CF7CMT_Utils::is_cf7_active() || ! current_user_can('manage_options')) {
			return;
		}

		$install_url = wp_nonce_url(
			self_admin_url('update.php?action=install-plugin&plugin=contact-form-7'),
			'install-plugin_contact-form-7'
		);

		$activate_url = wp_nonce_url(
			self_admin_url('plugins.php?action=activate&plugin=contact-form-7/wp-contact-form-7.php'),
			'activate-plugin_contact-form-7/wp-contact-form-7.php'
		);

		$is_installed = file_exists(WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php');

		echo '<div class="notice notice-error">';
		echo '<p><strong>CF7 Custom Meta Tags:</strong> Contact Form 7 is required.</p>';

		if ($is_installed) {
			echo '<p><a href="' . esc_url($activate_url) . '" class="button button-primary">Activate Contact Form 7</a></p>';
		} else {
			echo '<p><a href="' . esc_url($install_url) . '" class="button button-primary">Install Contact Form 7</a></p>';
		}

		echo '</div>';
	}

	/**
	 * Register menu item.
	 *
	 * @return void
	 */
	public function register_menu()
	{
		add_options_page(
			__('CF7 Custom Meta Tags', 'cf7-custom-meta-tags'),
			__('CF7 Custom Meta Tags', 'cf7-custom-meta-tags'),
			'manage_options',
			'cf7-custom-meta-tags',
			array($this, 'render_settings_page')
		);
	}

	/**
	 * Register settings and sections.
	 *
	 * @return void
	 */
	public function register_settings()
	{
		register_setting(
			'cf7cmt_settings_group',
			CF7CMT_Settings::OPTION_KEY,
			array(
				'sanitize_callback' => array($this->settings, 'sanitize'),
			)
		);

		add_settings_section(
			'cf7cmt_tags_section',
			__('Tag Availability', 'cf7-custom-meta-tags'),
			array($this, 'render_tags_section'),
			'cf7-custom-meta-tags'
		);

		add_settings_section(
			'cf7cmt_geo_section',
			__('Geo Lookup', 'cf7-custom-meta-tags'),
			array($this, 'render_geo_section'),
			'cf7-custom-meta-tags'
		);

		add_settings_section(
			'cf7cmt_tracking_section',
			__('Tracking Cookies', 'cf7-custom-meta-tags'),
			array($this, 'render_tracking_section'),
			'cf7-custom-meta-tags'
		);

		add_settings_field(
			'cf7cmt_enabled_tags',
			__('Enabled tags', 'cf7-custom-meta-tags'),
			array($this, 'render_enabled_tags_field'),
			'cf7-custom-meta-tags',
			'cf7cmt_tags_section'
		);

		add_settings_field(
			'cf7cmt_geo_controls',
			__('Provider settings', 'cf7-custom-meta-tags'),
			array($this, 'render_geo_fields'),
			'cf7-custom-meta-tags',
			'cf7cmt_geo_section'
		);

		add_settings_field(
			'cf7cmt_tracking_controls',
			__('Cookie lifetimes', 'cf7-custom-meta-tags'),
			array($this, 'render_tracking_fields'),
			'cf7-custom-meta-tags',
			'cf7cmt_tracking_section'
		);
	}

	/**
	 * Render tags section intro.
	 *
	 * @return void
	 */
	public function render_tags_section()
	{
		echo '<p>' . esc_html__('Enable or disable individual metadata tags. Disabled tags are skipped during form rendering, submission injection, and mail replacement.', 'cf7-custom-meta-tags') . '</p>';
	}

	/**
	 * Render geo section intro.
	 *
	 * @return void
	 */
	public function render_geo_section()
	{
		echo '<p>' . esc_html__('Configure geo lookup caching and provider order. Cached results are stored in WordPress transients per IP address.', 'cf7-custom-meta-tags') . '</p>';
	}

	/**
	 * Render tracking section intro.
	 *
	 * @return void
	 */
	public function render_tracking_section()
	{
		echo '<p>' . esc_html__('Persist UTM parameters, page context, and the submission UUID in first-party cookies.', 'cf7-custom-meta-tags') . '</p>';
	}

	/**
	 * Render enabled tags field.
	 *
	 * @return void
	 */
	public function render_enabled_tags_field()
	{
		$settings = $this->settings->all();
		$groups   = CF7CMT_Utils::get_tag_groups();
		$tags     = CF7CMT_Utils::get_supported_tags();

		echo '<fieldset>';

		foreach ($groups as $group_key => $group_label) {
			echo '<p><strong>' . esc_html($group_label) . '</strong></p>';

			foreach ($tags as $tag => $config) {
				if ($group_key !== $config['group']) {
					continue;
				}

				printf(
					'<label style="display:block;margin:0 0 8px;"><input type="checkbox" name="%1$s[enabled_tags][%2$s]" value="1" %3$s /> <code>[%2$s]</code> %4$s</label>',
					esc_attr(CF7CMT_Settings::OPTION_KEY),
					esc_attr($tag),
					checked(! empty($settings['enabled_tags'][ $tag ]), true, false),
					esc_html($config['label'])
				);

				echo '<p class="description" style="margin:0 0 8px 24px;">' . esc_html($config['description']) . '</p>';
			}
		}

		echo '</fieldset>';
	}

	/**
	 * Render geo settings.
	 *
	 * @return void
	 */
	public function render_geo_fields()
	{
		$settings = $this->settings->all();

		printf(
			'<label><input type="checkbox" name="%1$s[enable_geo_lookup]" value="1" %2$s /> %3$s</label>',
			esc_attr(CF7CMT_Settings::OPTION_KEY),
			checked(! empty($settings['enable_geo_lookup']), true, false),
			esc_html__('Enable geo lookup for city and country tags', 'cf7-custom-meta-tags')
		);

		echo '<p style="margin-top:12px;">';
		echo '<label for="cf7cmt_geo_primary_provider"><strong>' . esc_html__('Primary provider', 'cf7-custom-meta-tags') . '</strong></label><br />';
		echo '<select id="cf7cmt_geo_primary_provider" name="' . esc_attr(CF7CMT_Settings::OPTION_KEY) . '[geo_primary_provider]">';
		$this->render_provider_option('ip-api', (string) $settings['geo_primary_provider'], __('ip-api.com', 'cf7-custom-meta-tags'));
		$this->render_provider_option('ipapi', (string) $settings['geo_primary_provider'], __('ipapi.co', 'cf7-custom-meta-tags'));
		$this->render_provider_option('none', (string) $settings['geo_primary_provider'], __('None', 'cf7-custom-meta-tags'));
		echo '</select>';
		echo '</p>';

		echo '<p>';
		echo '<label for="cf7cmt_geo_fallback_provider"><strong>' . esc_html__('Fallback provider', 'cf7-custom-meta-tags') . '</strong></label><br />';
		echo '<select id="cf7cmt_geo_fallback_provider" name="' . esc_attr(CF7CMT_Settings::OPTION_KEY) . '[geo_fallback_provider]">';
		$this->render_provider_option('ipapi', (string) $settings['geo_fallback_provider'], __('ipapi.co', 'cf7-custom-meta-tags'));
		$this->render_provider_option('ip-api', (string) $settings['geo_fallback_provider'], __('ip-api.com', 'cf7-custom-meta-tags'));
		$this->render_provider_option('none', (string) $settings['geo_fallback_provider'], __('None', 'cf7-custom-meta-tags'));
		echo '</select>';
		echo '</p>';

		printf(
			'<p><label for="cf7cmt_geo_cache_hours"><strong>%1$s</strong></label><br /><input id="cf7cmt_geo_cache_hours" type="number" min="1" max="168" class="small-text" name="%2$s[geo_cache_hours]" value="%3$d" /> %4$s</p>',
			esc_html__('Cache lifetime (hours)', 'cf7-custom-meta-tags'),
			esc_attr(CF7CMT_Settings::OPTION_KEY),
			absint($settings['geo_cache_hours']),
			esc_html__('Default: 24 hours', 'cf7-custom-meta-tags')
		);

		printf(
			'<p><label for="cf7cmt_geo_request_timeout"><strong>%1$s</strong></label><br /><input id="cf7cmt_geo_request_timeout" type="number" min="1" max="20" class="small-text" name="%2$s[geo_request_timeout]" value="%3$d" /> %4$s</p>',
			esc_html__('API timeout (seconds)', 'cf7-custom-meta-tags'),
			esc_attr(CF7CMT_Settings::OPTION_KEY),
			absint($settings['geo_request_timeout']),
			esc_html__('Used for both primary and fallback lookups', 'cf7-custom-meta-tags')
		);
	}

	/**
	 * Render tracking settings.
	 *
	 * @return void
	 */
	public function render_tracking_fields()
	{
		$settings = $this->settings->all();

		printf(
			'<p><label for="cf7cmt_utm_cookie_days"><strong>%1$s</strong></label><br /><input id="cf7cmt_utm_cookie_days" type="number" min="1" max="365" class="small-text" name="%2$s[utm_cookie_days]" value="%3$d" /> %4$s</p>',
			esc_html__('UTM cookie lifetime (days)', 'cf7-custom-meta-tags'),
			esc_attr(CF7CMT_Settings::OPTION_KEY),
			absint($settings['utm_cookie_days']),
			esc_html__('Controls UTM and page context cookie persistence', 'cf7-custom-meta-tags')
		);

		printf(
			'<p><label for="cf7cmt_submission_cookie_days"><strong>%1$s</strong></label><br /><input id="cf7cmt_submission_cookie_days" type="number" min="1" max="30" class="small-text" name="%2$s[submission_cookie_days]" value="%3$d" /> %4$s</p>',
			esc_html__('Submission UUID lifetime (days)', 'cf7-custom-meta-tags'),
			esc_attr(CF7CMT_Settings::OPTION_KEY),
			absint($settings['submission_cookie_days']),
			esc_html__('A successful CF7 submission rotates the UUID immediately for the next submission', 'cf7-custom-meta-tags')
		);
	}

	/**
	 * Render a provider option.
	 *
	 * @param string $value    Option value.
	 * @param string $selected Selected value.
	 * @param string $label    Option label.
	 * @return void
	 */
	protected function render_provider_option($value, $selected, $label)
	{
		printf(
			'<option value="%1$s" %2$s>%3$s</option>',
			esc_attr($value),
			selected($selected, $value, false),
			esc_html($label)
		);
	}

	/**
	 * Register the CF7 tag generator panel.
	 *
	 * @return void
	 */
	public function register_tag_generator()
	{
		if (class_exists('WPCF7_TagGenerator') && method_exists('WPCF7_TagGenerator', 'get_instance')) {
			WPCF7_TagGenerator::get_instance()->add(
				'cf7cmt-meta-tags',
				__('Custom Meta Tags', 'cf7-custom-meta-tags'),
				array($this, 'render_tag_generator'),
				array(
					'version' => 2,
				)
			);

			return;
		}

		if (function_exists('wpcf7_add_tag_generator')) {
			wpcf7_add_tag_generator(
				'cf7cmt-meta-tags',
				__('Custom Meta Tags', 'cf7-custom-meta-tags'),
				array($this, 'render_tag_generator')
			);
		}
	}

	/**
	 * Render generator UI.
	 *
	 * @param WPCF7_ContactForm|null $contact_form Contact form.
	 * @param array<string,mixed>|string $args Generator args.
	 * @return void
	 */
	public function render_tag_generator($contact_form = null, $args = '')
	{
		$groups = CF7CMT_Utils::get_tag_groups();
		$tags   = CF7CMT_Utils::get_supported_tags();

		unset($contact_form, $args);

		echo '<header class="description-box">';
		echo '<h3>' . esc_html__('Custom Meta Tags', 'cf7-custom-meta-tags') . '</h3>';
		echo '<p>' . esc_html__('Copy any of these tags into the form editor. They render hidden metadata fields and also work in mail templates.', 'cf7-custom-meta-tags') . '</p>';
		echo '</header>';
		echo '<div class="control-box">';

		foreach ($groups as $group_key => $group_label) {
			echo '<fieldset>';
			echo '<legend>' . esc_html($group_label) . '</legend>';

			foreach ($tags as $tag => $config) {
				if ($group_key !== $config['group']) {
					continue;
				}

				$tag_markup = '[' . $tag . ']';

				echo '<p>';
				echo '<span style="display:flex;gap:8px;align-items:center;max-width:420px;">';
				echo '<input type="text" class="tag code cf7cmt-generator-tag" readonly="readonly" onfocus="this.select()" value="' . esc_attr($tag_markup) . '" data-tag="' . esc_attr($tag_markup) . '" />';
				echo '<button type="button" class="button button-secondary cf7cmt-insert-tag" data-tag="' . esc_attr($tag_markup) . '">' . esc_html__('Insert', 'cf7-custom-meta-tags') . '</button>';
				echo '</span>';
				echo '<br /><span class="description">' . esc_html($config['description']) . '</span>';
				echo '</p>';
			}

			echo '</fieldset>';
		}

		echo '</div>';
		echo '<div class="insert-box">';
		echo '<p class="description">' . esc_html__('These tags also work directly in mail templates even if you do not place them in the form markup.', 'cf7-custom-meta-tags') . '</p>';
		echo '</div>';
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets($hook_suffix)
	{
		global $post;

		$is_cf7_editor = false;

		if ('toplevel_page_wpcf7' === $hook_suffix || 'contact_page_wpcf7-new' === $hook_suffix) {
			$is_cf7_editor = true;
		}

		if ($post instanceof WP_Post && 'wpcf7_contact_form' === $post->post_type) {
			$is_cf7_editor = true;
		}

		if (! $is_cf7_editor) {
			return;
		}

		wp_enqueue_script(
			'cf7cmt-admin-generator',
			CF7CMT_URL . 'assets/js/admin-generator.js',
			array(),
			CF7CMT_VERSION,
			true
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page()
	{
		if (! current_user_can('manage_options')) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('CF7 Custom Meta Tags', 'cf7-custom-meta-tags') . '</h1>';
		echo '<p>' . esc_html__('Smart hidden metadata tags for Contact Form 7 with page context, tracking cookies, geo lookup, and a native tag generator.', 'cf7-custom-meta-tags') . '</p>';
		echo '<form action="options.php" method="post">';
		settings_fields('cf7cmt_settings_group');
		do_settings_sections('cf7-custom-meta-tags');
		submit_button();
		echo '</form>';
		echo '</div>';
	}
}

