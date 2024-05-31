<?php
/*
 * Plugin Name: Amazing Feed
 */
/*
Plugin Name: Amazing Feed
Description: Seamlessly integrate your WordPress content with multiple platforms, including Google News, ICARO (Tim News), and Portal R7. This plugin creates REST API endpoints to list the latest articles in formats compatible with each platform's specifications, ensuring efficient and quick integration.
Version: 1.0.2
Author: Weslley Alves
Author URI: https://www.wezo.com.br
License: GPL-3.0-or-later
Requires at least: 2.8.0
Requires PHP: 7.4
*/

if (! defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'lib/Core/Article.php';
require_once plugin_dir_path(__FILE__) . 'lib/Core/Blog.php';
require_once plugin_dir_path(__FILE__) . 'lib/Core/Page.php';
require_once plugin_dir_path(__FILE__) . 'lib/Utils/Date.php';
require_once plugin_dir_path(__FILE__) . 'lib/Utils/Text.php';
require_once plugin_dir_path(__FILE__) . 'lib/Partner/Icaro.php';
require_once plugin_dir_path(__FILE__) . 'lib/Partner/Sitemap.php';
require_once plugin_dir_path(__FILE__) . 'lib/Partner/SitemapIndex.php';
require_once plugin_dir_path(__FILE__) . 'lib/Partner/GoogleNews.php';

function plugin_feed_rest_url_prefix()
{
    return 'apifeed';
}
add_filter('rest_url_prefix', 'plugin_feed_rest_url_prefix');

function register_endpoints()
{
    register_rest_route('icaro/v1/feed', '/articles', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new Wezo\Plugin\Feed\Partner\Icaro(), 'callbackArticles'],
    ]);

    register_rest_route('googlenews/v1/feed', '/articles', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\GoogleNews(), 'callbackArticles'],
    ]);

    // /apifeed/sitemap/index?output=xml&type[]=offer&type[]=video&type[]=post
    register_rest_route('sitemap', '/index', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\SitemapIndex(), 'callbackSitemap'],
    ]);

    register_rest_route('sitemap', '/pages', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\Sitemap('page'), 'callbackSitemap'],
    ]);

    // /apifeed/sitemap/posts?output=xml&limit=1000&type=post&page=1
    register_rest_route('sitemap', '/posts', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\Sitemap('post'), 'callbackSitemap'],
    ]);

    flush_rewrite_rules(true);
}

add_action('rest_api_init', 'register_endpoints');