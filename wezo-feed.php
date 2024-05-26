<?php
/*
Plugin Name: Amazing Feed
Description: Seamlessly integrate your WordPress content with the ICARO platform. This plugin creates a REST API endpoint to list the latest articles in a format compatible with ICARO specifications, ensuring efficient and quick integration.
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