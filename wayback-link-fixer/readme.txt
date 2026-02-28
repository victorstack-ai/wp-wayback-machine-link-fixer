=== Wayback Machine Link Fixer ===
Contributors: victorstackai
Tags: broken links, wayback machine, internet archive, link fixer, dead links
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 0.1.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically replaces broken external links with the closest Wayback Machine snapshot URL.

== Description ==

Wayback Machine Link Fixer scans rendered post and comment HTML for broken external links. When a link returns a 4xx or 5xx status code, the plugin queries the Internet Archive Wayback Machine Availability API for the closest archived snapshot and rewrites the link automatically.

**Features:**

* Scans post content and comments for broken external links.
* Queries the Wayback Machine Availability API for archived snapshots.
* Rewrites broken links to the closest available snapshot URL.
* Adds a `data-wayback-fixed` attribute to rewritten links for easy identification.
* Lightweight with no settings page required -- just activate and go.

== Installation ==

1. Upload the `wayback-machine-link-fixer` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. That is it. Broken external links will be automatically replaced with Wayback Machine snapshots when available.

== Frequently Asked Questions ==

= Does this plugin modify my database content? =

No. The plugin only filters rendered HTML output. Your original post and comment content remains unchanged in the database.

= Which links does the plugin check? =

The plugin only checks external HTTP/HTTPS links. Internal links to your own site are always left unchanged.

= What happens if no Wayback Machine snapshot exists? =

If no archived snapshot is available, the original broken link is left as-is.

== Changelog ==

= 0.1.0 =
* Initial release.
* Scan post content and comments for broken external links.
* Rewrite broken links to Wayback Machine snapshots.
