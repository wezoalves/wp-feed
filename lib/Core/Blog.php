<?php
/*
 * Plugin Name: Amazing Feed
 */
namespace Wezo\Plugin\Feed\Core;

/**
 * Class Blog
 *
 * This class retrieves information about the WordPress blog.
 */
class Blog
{
    /**
     * Retrieves information about the WordPress blog.
     *
     * @param \WP_REST_Request|null $request The request object.
     * @param bool $object Whether to return the data as an object.
     * @return array|object Information about the WordPress blog.
     */
    public function getInfo($request = null, $object = false)
    {
        // Determine the Atom feed link based on the request
        $atomLink = $request ? get_bloginfo('url') . '/wp-json' . $request->get_route() : null;

        // Gather information about the WordPress blog
        $data = [
            'title' => get_bloginfo('name'),
            'charset' => get_option('blog_charset'),
            'description' => get_bloginfo('description'),
            'language' => get_bloginfo('language'),
            'link' => get_bloginfo('url'),
            'managing' => get_bloginfo('admin_email') . ' (' . get_bloginfo('name') . ')',
            'atomLink' => $atomLink,
        ];

        // Convert data to object if requested
        return $object ? (object) $data : $data;
    }
}