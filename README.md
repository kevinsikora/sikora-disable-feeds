# Sikora Disable Feeds

> Source of truth for plugin documentation is [`readme.txt`](readme.txt) (WordPress plugin readme format). This file mirrors that content for GitHub and other Markdown viewers.

Removes feed links from the page source and redirects feed requests to the homepage.

| | |
| --- | --- |
| **Contributors** | sikoracollective |
| **Tags** | feeds, rss, atom, disable, redirect |
| **Requires at least** | 4.0 |
| **Tested up to** | 6.8 |
| **Stable tag** | 2.2.0 |
| **Requires PHP** | 7.1 |
| **License** | [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) |

## Description

Sikora Disable Feeds turns off WordPress feeds with no settings screen and no configuration.

The plugin:

- Removes the default feed `<link>` tags from the document `<head>`
- Redirects feed requests to the site homepage with a permanent (301) redirect

It covers core feed URLs such as `/feed/`, `/feed/rss/`, `/feed/atom/`, comment feeds, category/tag/author feeds, and custom feeds registered with `add_feed()`.

Redirects use `wp_safe_redirect()`, so the destination is limited to permitted hosts for this site.

### How it works

On `plugins_loaded`, the plugin removes WordPress's default `feed_links` and `feed_links_extra` actions from `wp_head`.

On `template_redirect`, when `is_feed()` is true, it sends a 301 redirect to `home_url( '/' )` and stops execution before WordPress can output feed content. That action runs in `template-loader.php` before `do_feed()`.

### Notes and limitations

- **301 redirects are cached.** Browsers and some feed readers remember permanent redirects. After the plugin is deactivated, clients that already received the redirect may keep going to the homepage until their cache expires.
- **Other discovery links remain.** Only feed links are removed. Tags such as RSD (`rsd_link`), the REST API link (`rest_output_link_wp_head`), and oEmbed discovery links are left in place.
- **Themes can add links back.** Feed links that a theme or another plugin outputs manually, instead of through the default WordPress hooks, will still appear.

## Installation

### Standard installation

1. Upload the `sikora-disable-feeds` folder to the `/wp-content/plugins/` directory, or upload the plugin zip via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. There is nothing to configure. The plugin takes effect as soon as it is activated.

### Must-use plugin (optional)

To keep the plugin from being deactivated in the admin, place `sikora-disable-feeds.php` directly in `wp-content/mu-plugins/`. WordPress loads must-use plugins automatically.

## Frequently Asked Questions

### Does this plugin have any settings?

No. Activate it and feeds are disabled.

### Which feeds are disabled?

All feeds detected by WordPress's `is_feed()` check, including RSS, RSS2, RDF, Atom, comment/category/tag/author feeds, and custom feeds registered with `add_feed()`.

### How can I verify it is working?

1. View the source of any front-end page. It should contain no `application/rss+xml` or `application/atom+xml` link tags from WordPress's default feed discovery.
2. Request a feed URL and check the response headers, for example:

```bash
curl -I https://example.com/feed/
```

The response should be a `301 Moved Permanently` with a `Location` header pointing to the homepage.

### What happens if I deactivate the plugin later?

New feed requests will work again. Clients that previously received a 301 may still follow the cached redirect until that cache expires.

## Changelog

### 2.2.0

- Align with WordPress plugin header and coding standards.
- Bootstrap hooks on `plugins_loaded` instead of at file load.
- Rename the redirect callback to `sikora_disable_feeds_redirect()`.

### 2.1.0

- Redirect all feeds (including custom `add_feed()` feeds) via `template_redirect` and `is_feed()`.

## Upgrade Notice

### 2.2.0

Follows WordPress coding standards and loads hooks on plugins_loaded. Behavior is unchanged for end users.
