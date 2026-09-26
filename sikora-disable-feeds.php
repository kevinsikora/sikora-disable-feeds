<?php
/**
 * Plugin Name: Sikora Disable Feeds (Admin)
 * Description: Removes feed links from the page source and redirects feed requests to the homepage.
 * Version: 2.1.0
 * Author: <a href="https://sikoracollective.com/">Sikora Collective</a>
 */

// Block direct access: ABSPATH is only defined when the file is loaded by WordPress.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Remove feed <link> tags from <head>, no hook wrapper needed
// WordPress registers these actions in wp-includes/default-filters.php, which
// loads before plugins, so they can be removed as soon as this file runs.
// The priorities (2 and 3) must match the ones WordPress used to add them.
remove_action( 'wp_head', 'feed_links', 2 );       // Main site feed and global comments feed.
remove_action( 'wp_head', 'feed_links_extra', 3 ); // Post comment, category, tag, author, search, and post type archive feeds.

/**
 * Redirect any feed request to the homepage.
 *
 * Hooked to the do_feed_* actions below, so it runs in place of WordPress's
 * own feed output. Sends a permanent (301) redirect and stops execution
 * before any feed content is generated.
 *
 * @param bool $is_comment_feed Passed by WordPress to do_feed_* actions; true
 *                              for comment feeds. Not used, because every feed
 *                              is redirected the same way.
 * @return void
 */
function sikora_disable_feeds( bool $is_comment_feed = false ): void {
    // wp_safe_redirect() only allows redirects to allowed hosts, so the
    // destination is limited to this site's own home URL.
    wp_safe_redirect( home_url( '/' ), 301 );

    // Stop here so WordPress doesn't continue and render the feed.
    exit;
}

// Feed actions WordPress fires for its built-in feed formats. Category, tag,
// author, and comment feeds are served through these same actions, so they
// are covered as well. Custom feeds added with add_feed() are not included.
$feed_hooks = [
    'do_feed_rdf',  // /feed/rdf/
    'do_feed_rss',  // /feed/rss/
    'do_feed_rss2', // /feed/ (default feed) and /feed/rss2/
    'do_feed_atom', // /feed/atom/
];

// Attach the redirect at priority 1 so it runs before WordPress's own feed
// handler (do_feed_* callbacks default to priority 10). The final argument
// tells WordPress to pass one argument ($is_comment_feed) to the callback.
foreach ( $feed_hooks as $hook ) {
    add_action( $hook, 'sikora_disable_feeds', 1, 1 );
}

