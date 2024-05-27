<?php
/*
Plugin Name: Amazing Feed
Description: Seamlessly integrate your WordPress content with multiple platforms, including Google News, ICARO (Tim News), and Portal R7. This plugin creates REST API endpoints to list the latest articles in formats compatible with each platform's specifications, ensuring efficient and quick integration.
Version: 1.0
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
require_once plugin_dir_path(__FILE__) . 'lib/Utils/Date.php';
require_once plugin_dir_path(__FILE__) . 'lib/Utils/Text.php';
require_once plugin_dir_path(__FILE__) . 'lib/Partner/Icaro.php';
require_once plugin_dir_path(__FILE__) . 'lib/Partner/GoogleNews.php';

function register_endpoints()
{
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

add_action('rest_api_init', 'register_endpoints');