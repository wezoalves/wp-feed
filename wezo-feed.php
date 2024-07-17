<?php
/*
 * Plugin Name: Amazing Feed
 * Plugin URI: https://wezo.com.br/
 * Description: Seamlessly integrate your WordPress content with multiple platforms, including Google News, ICARO (Tim News), and Portal R7. This plugin creates REST API endpoints to list the latest articles in formats compatible with each platform's specifications, ensuring efficient and quick integration.
 * Version: 1.0.2
 * Author: Weslley Alves
 * Author URI: https://www.wezo.com.br
 * License: GPL-3.0-or-later
 * Requires at least: 2.8.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
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

// Regras de reescrita personalizadas
function custom_rewrite_rules()
{
    add_rewrite_rule('^apifeed/sitemap/posts/?$', 'index.php?rest_route=/sitemap/posts', 'top');
    add_rewrite_rule('^apifeed/sitemap/pages/?$', 'index.php?rest_route=/sitemap/pages', 'top');
    add_rewrite_rule('^apifeed/sitemap/index/?$', 'index.php?rest_route=/sitemap/index', 'top');
    add_rewrite_rule('^apifeed/icaro/v1/feed/articles/?$', 'index.php?rest_route=/icaro/v1/feed/articles', 'top');
    add_rewrite_rule('^apifeed/googlenews/v1/feed/articles/?$', 'index.php?rest_route=/googlenews/v1/feed/articles', 'top');
}
add_action('init', 'custom_rewrite_rules');

function custom_flush_rewrite_rules()
{
    custom_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'custom_flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

// Registrar endpoints personalizados da API REST
function register_custom_endpoints()
{
    register_rest_route('sitemap', '/posts', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\Sitemap('post'), 'callbackSitemap'],
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('sitemap', '/pages', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\Sitemap('page'), 'callbackSitemap'],
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('sitemap', '/index', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\SitemapIndex(), 'callbackSitemap'],
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('icaro/v1/feed', '/articles', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new Wezo\Plugin\Feed\Partner\Icaro(), 'callbackArticles'],
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('googlenews/v1/feed', '/articles', [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [new \Wezo\Plugin\Feed\Partner\GoogleNews(), 'callbackArticles'],
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'register_custom_endpoints');
