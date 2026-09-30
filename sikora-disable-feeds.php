<?php
/**
 * Plugin Name:       Sikora Disable Feeds
 * Description:       Removes feed links from the page source and redirects feed requests to the homepage.
 * Version:           2.2.0
 * Requires at least: 4.0
 * Requires PHP:      7.1
 * Author:            Sikora Collective
 * Author URI:        https://sikoracollective.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sikora-disable-feeds
 *
 * @package Sikora_Disable_Feeds
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap plugin hooks.
 *
 * WordPress registers feed_links and feed_links_extra in default-filters.php
 * before plugins load, so they can be removed here on plugins_loaded. The
 * priorities (2 and 3) must match the ones WordPress used to add them.
 *
 * @since 2.2.0
 * @return void
 */
function sikora_disable_feeds_init(): void {
	remove_action( 'wp_head', 'feed_links', 2 );       // Main site feed and global comments feed.
	remove_action( 'wp_head', 'feed_links_extra', 3 ); // Post comment, category, tag, author, search, and post type archive feeds.

	// Priority 1 so the redirect runs before most other template_redirect callbacks.
	// template-loader.php fires this action before it calls do_feed().
	add_action( 'template_redirect', 'sikora_disable_feeds_redirect', 1 );
}
add_action( 'plugins_loaded', 'sikora_disable_feeds_init' );

/**
 * Redirect any feed request to the homepage.
 *
 * Runs on template_redirect before WordPress loads a feed template. is_feed()
 * is true for core feeds and for custom feeds registered with add_feed().
 *
 * @since 2.1.0
 * @return void
 */
function sikora_disable_feeds_redirect(): void {
	if ( ! is_feed() ) {
		return;
	}

	// wp_safe_redirect() only allows redirects to allowed hosts, so the
	// destination is limited to this site's own home URL.
	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
