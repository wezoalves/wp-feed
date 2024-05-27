=== Amazing Feed ===
Contributors: wezoalves
Donate link: https://example.com/
Tags: feed, rss, xml, publishers, wezoalves
Tested up to: 6.5  
Requires at least: 6.2  
Stable tag: 1.0.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html#license-text

Feed is a comprehensive solution for integrating **WordPress** content with multiple platforms.

== Description ==

Feed is a comprehensive solution for integrating **WordPress** content with multiple platforms. This plugin creates REST API endpoints to list the latest articles from your site, formatted according to each platform's specifications, ensuring smooth and efficient integration.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `wezo-feed` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Access the endpoint relevant to the platform you are integrating with, such as: =

- `/wp-json/googlenews/v1/feed/articles` for Google News
- `/wp-json/icaro/v1/feed/articles` for ICARO (Tim News)

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).

== Changelog ==

= 1.0 =
* A change since the previous version.

== Arbitrary section ==

- Creates REST API endpoints in WordPress to provide articles in formats expected by Google News, ICARO (Tim News), and Portal R7.
- Example endpoints include:
  - `/wp-json/googlenews/v1/feed/articles` for **Google News**
  - `/wp-json/icaro/v1/feed/articles` for **ICARO (Tim News)**