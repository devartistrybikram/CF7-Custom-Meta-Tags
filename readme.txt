=== CF7 Custom Meta Tags ===
Contributors: codex
Tags: contact form 7, cf7, metadata, utm tracking, geolocation, hidden fields
Requires at least: 6.2
Tested up to: 6.2
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add smart hidden metadata tags to Contact Form 7 with native tag support, UTM cookie persistence, and geolocation caching.

== Description ==

CF7 Custom Meta Tags extends Contact Form 7 with smart metadata tags that work as hidden form fields, mail template placeholders, and submission pipeline data.

Supported metadata includes:

* Page title, page ID, page URL, and referrer URL
* Form ID, form title, and a UUID-based submission ID
* User IP address and user agent
* Geo city and geo country via cached IP lookup
* UTM source, medium, campaign, term, and content

Key features:

* Native CF7 tag support such as `[page_title]`, `[user_ip]`, and `[utm_source]`
* Contact Form 7 tag generator panel for metadata tags
* Automatic UTM tracking with cookie persistence
* Per-IP geolocation cache using WordPress transients
* ip-api.com primary lookup with ipapi.co fallback
* Settings page built with the WordPress Settings API
* Individual tag enable or disable controls
* Mail template replacement support even when tags are not inserted into form markup

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP through the WordPress admin.
2. Activate Contact Form 7.
3. Activate CF7 Custom Meta Tags.
4. Open `Settings > CF7 Custom Meta Tags`.
5. Add tags like `[page_title]` or `[utm_campaign]` to your Contact Form 7 form when you want explicit hidden fields.
6. Use the same tags in mail templates or downstream integrations.

== Frequently Asked Questions ==

= Do I have to add every tag to the form markup? =

No. The plugin can replace supported tags directly in CF7 mail templates even if you do not insert them into the form editor.

= How is geolocation cached? =

Geo lookups are cached in WordPress transients by IP address. The default cache lifetime is 24 hours and can be changed from the settings screen.

= Which geo APIs are supported? =

The plugin uses ip-api.com first and can fall back to ipapi.co if the primary lookup fails.

= How is the submission UUID handled? =

The plugin stores a UUID in a first-party cookie. A successful CF7 submission rotates the cookie so the next submission gets a new UUID.

== Changelog ==

= 1.0.0 =

* Initial release.
* Native Contact Form 7 hidden metadata tags and mail replacement.
* UTM tracking cookies and UUID persistence.
* Geo lookup with transient caching and provider fallback.
* Settings screen and tag generator UI.

== Upgrade Notice ==

= 1.0.0 =

Initial release of CF7 Custom Meta Tags.

