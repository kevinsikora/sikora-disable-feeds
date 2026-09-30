=== Sikora Disable Feeds ===
Contributors: sikoracollective
Tags: feeds, rss, atom, disable, security
Requires at least: 4.0
Tested up to: 6.8
Stable tag: 2.3.0
Requires PHP: 7.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Removes feed links from the page source and returns 404 for feed requests.

== Description ==

Sikora Disable Feeds turns off WordPress feeds with no settings screen and no configuration.

The plugin:

* Removes the default feed `<link>` tags from the document `<head>`
* Removes the Really Simple Discovery (RSD) link from `<head>`
* Removes the `X-Pingback` response header
* Returns a non-cacheable HTTP 404 for feed requests (instead of serving feed XML or redirecting)

It covers core feed URLs such as `/feed/`, `/feed/rss/`, `/feed/atom/`, comment feeds, category/tag/author feeds, and custom feeds registered with `add_feed()`.

= How it works =

On `plugins_loaded`, the plugin:

* Removes WordPress's default `feed_links`, `feed_links_extra`, and `rsd_link` actions from `wp_head`
* Turns off feed link output via the `feed_links_show_posts_feed` and `feed_links_show_comments_feed` filters
* Strips `X-Pingback` from outgoing headers

On both `wp` and `template_redirect`, when `is_feed()` is true, it sends `nocache_headers()` and ends the request with `wp_die()` and HTTP 404. Using two hooks blocks feed output even if another callback interferes with one of them.

= Notes and limitations =

* **Other discovery links remain.** Tags such as the REST API link (`rest_output_link_wp_head`) and oEmbed discovery links are left in place.
* **Themes can add links back.** Feed links that a theme or another plugin outputs manually, instead of through the default WordPress hooks, will still appear.
* **Server-level rewrites.** If the web server or CDN rewrites a feed URL before WordPress runs, this plugin cannot handle that request.

== Installation ==

= Standard installation =

1. Upload the `sikora-disable-feeds` folder to the `/wp-content/plugins/` directory, or upload the plugin zip via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. There is nothing to configure. The plugin takes effect as soon as it is activated.

= Must-use plugin (optional) =

To keep the plugin from being deactivated in the admin, place `sikora-disable-feeds.php` directly in `wp-content/mu-plugins/`. WordPress loads must-use plugins automatically.

== Frequently Asked Questions ==

= Does this plugin have any settings? =

No. Activate it and feeds are disabled.

= Which feeds are disabled? =

All feeds detected by WordPress's `is_feed()` check, including RSS, RSS2, RDF, Atom, comment/category/tag/author feeds, and custom feeds registered with `add_feed()`.

= Why 404 instead of a redirect? =

A 301 to the homepage is often cached by browsers and CDNs, and many homepage redirects look like soft 404s to search engines. A non-cacheable 404 makes it clear the feed is unavailable without permanently mapping those URLs to the homepage.

= How can I verify it is working? =

1. View the source of any front-end page. It should contain no `application/rss+xml` or `application/atom+xml` link tags from WordPress's default feed discovery.
2. Request a feed URL and check the response headers, for example:

`curl -I https://example.com/feed/`

The response should be HTTP 404 (after any normal host redirects such as apex to www).

You can also run the included test script:

`./tests/test-feeds.sh https://example.com`

== Changelog ==

= 2.3.0 =
* Return a non-cacheable HTTP 404 for feed requests instead of a 301 redirect.
* Intercept feeds on both `wp` and `template_redirect`.
* Disable feed links via `feed_links_show_posts_feed` and `feed_links_show_comments_feed`.
* Remove the RSD link and `X-Pingback` header.

= 2.2.0 =
* Align with WordPress plugin header and coding standards.
* Bootstrap hooks on `plugins_loaded` instead of at file load.
* Rename the redirect callback to `sikora_disable_feeds_redirect()`.

= 2.1.0 =
* Redirect all feeds (including custom `add_feed()` feeds) via `template_redirect` and `is_feed()`.

== Upgrade Notice ==

= 2.3.0 =
Feed requests now return HTTP 404 instead of redirecting to the homepage. Clear any cached 301s after updating.
