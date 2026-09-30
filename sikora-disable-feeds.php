<?php
/**
 * Plugin Name:       Sikora Disable Feeds
 * Description:       Removes feed links from the page source and returns 404 for feed requests.
 * Version:           2.3.0
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
 * @since 2.2.0
 * @return void
 */
function sikora_disable_feeds_init(): void {
	// Priorities (2 and 3) must match the ones WordPress used to add them.
	remove_action( 'wp_head', 'feed_links', 2 );       // Main site feed and global comments feed.
	remove_action( 'wp_head', 'feed_links_extra', 3 ); // Post comment, category, tag, author, search, and post type archive feeds.
	remove_action( 'wp_head', 'rsd_link' );            // Really Simple Discovery link.

	// Future-proof feed link removal if core action priorities change (WP 4.4+).
	add_filter( 'feed_links_show_posts_feed', '__return_false' );
	add_filter( 'feed_links_show_comments_feed', '__return_false' );

	add_filter( 'wp_headers', 'sikora_disable_feeds_remove_pingback_header' );

	// Intercept on both wp and template_redirect so feed output is blocked early.
	add_action( 'wp', 'sikora_disable_feeds_disable', 1 );
	add_action( 'template_redirect', 'sikora_disable_feeds_disable', 1 );
}
add_action( 'plugins_loaded', 'sikora_disable_feeds_init' );

/**
 * Remove the X-Pingback response header.
 *
 * @since 2.3.0
 * @param array $headers Associative array of headers to be sent.
 * @return array Filtered headers.
 */
function sikora_disable_feeds_remove_pingback_header( array $headers ): array {
	unset( $headers['X-Pingback'] );
	return $headers;
}

/**
 * Block feed requests with a non-cacheable 404 response.
 *
 * Runs on wp and template_redirect. is_feed() is true for core feeds and for
 * custom feeds registered with add_feed().
 *
 * @since 2.1.0
 * @return void
 */
function sikora_disable_feeds_disable(): void {
	static $done = false;

	if ( $done || ! is_feed() ) {
		return;
	}

	$done = true;

	nocache_headers();

	wp_die(
		esc_html__( 'Feeds are disabled on this site.', 'sikora-disable-feeds' ),
		esc_html__( 'Feeds Disabled', 'sikora-disable-feeds' ),
		array(
			'response' => 404,
			'code'     => 'sikora_feeds_disabled',
		)
	);
}
