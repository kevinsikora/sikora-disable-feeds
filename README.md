# Sikora Disable Feeds (Admin)

A lightweight WordPress plugin that turns off RSS, RSS2, RDF, and Atom feeds. It removes the feed `<link>` tags from the page source and sends any feed request to the homepage with a permanent (301) redirect.

- **Version:** 2.1.0
- **Author:** [Sikora Collective](https://sikoracollective.com/)

## What it does

### 1. Removes feed links from `<head>`

WordPress normally adds `<link rel="alternate" type="application/rss+xml" ...>` tags to every page. The plugin removes both sets:

| Hook removed | What it outputs |
| --- | --- |
| `feed_links` (priority 2) | Main site feed and the global comments feed |
| `feed_links_extra` (priority 3) | Feeds for single-post comments, categories, tags, authors, search results, and custom post type archives |

WordPress registers these actions in `wp-includes/default-filters.php`, which loads before plugins, so they are removed as soon as the plugin file runs. No hook wrapper is needed.

### 2. Redirects feed requests to the homepage

The `sikora_disable_feeds()` function is attached to the built-in feed actions at priority 1, so it runs before WordPress's own feed handlers (priority 10). It sends a `301` redirect to `home_url( '/' )` and stops execution before any feed content is generated.

| Action | Example URLs |
| --- | --- |
| `do_feed_rdf` | `/feed/rdf/` |
| `do_feed_rss` | `/feed/rss/` |
| `do_feed_rss2` | `/feed/`, `/feed/rss2/`, `/comments/feed/`, `/category/*/feed/`, `/tag/*/feed/`, `/author/*/feed/` |
| `do_feed_atom` | `/feed/atom/` |

Comment, category, tag, and author feeds all run through these same actions, so they are covered too. The redirect uses `wp_safe_redirect()`, which only allows destinations on permitted hosts.

## Requirements

- WordPress 4.x or later
- PHP 7.1 or later (the plugin uses a `void` return type declaration)

## Installation

### Standard plugin

1. Copy the plugin file into a folder such as `wp-content/plugins/sikora-disable-feeds/`.
2. In the WordPress admin, go to **Plugins** and activate **Sikora Disable Feeds (Admin)**.

### Must-use plugin (optional)

To keep the plugin from being deactivated in the admin, place the PHP file directly in `wp-content/mu-plugins/`. WordPress loads must-use plugins automatically.

## Configuration

There is nothing to configure. The plugin takes effect as soon as it is activated.

## Verifying it works

1. View the source of any page. It should contain no `application/rss+xml` or `application/atom+xml` link tags.
2. Request a feed URL and check the response:

   ```bash
   curl -I https://example.com/feed/
   ```

   The response should be `HTTP/1.1 301 Moved Permanently`, with a `Location:` header pointing to the homepage.

## Notes and limitations

- **301 redirects are cached.** Browsers and some feed readers remember permanent redirects. After the plugin is deactivated, clients that already received the redirect may keep going to the homepage until their cache expires.
- **Custom feeds are not covered.** Feeds registered with `add_feed()` by other plugins or themes use their own `do_feed_{name}` action, which this plugin does not intercept.
- **Other discovery links remain.** Only feed links are removed. Tags such as RSD (`rsd_link`), the REST API link (`rest_output_link_wp_head`), and oEmbed discovery links are left in place.
- **Themes can add links back.** Feed links that a theme or another plugin outputs manually, instead of through the default WordPress hooks, will still appear.
- **Unused parameter.** WordPress passes `$is_comment_feed` to `sikora_disable_feeds()`, but the function ignores it because every feed is redirected the same way.

## Changelog

### 2.1.0
- Current release.
