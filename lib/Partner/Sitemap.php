<?php
/*
 * Plugin Name: Amazing Sitemap
 */
namespace Wezo\Plugin\Feed\Partner;

use Wezo\Plugin\Feed\Core\Blog;
use Wezo\Plugin\Feed\Core\Article;
use Wezo\Plugin\Feed\Core\Page;

class Sitemap
{
    private int $maxItems = 1000;
    private int $limit = 1000;
    private int $page = 1;
    private string $output = 'esc_html';
    private string $prefixEndpoint = "";
    private array $typePosts = ['post'];
    private string $typeSitemap = 'post';
    private array $categoryPosts = [];
    private array $tagPosts = [];
    private string $pathPrefix = 'apifeed';

    public function __construct($type = 'post')
    {
        $this->typeSitemap = $type;
    }

    public function callbackSitemap($request)
    {

        $site = (new Blog())->getInfo($request, true);

        $this->prefixEndpoint = $site->link . '/' . $this->pathPrefix . '/sitemap/';

        // output
        $this->output = $request->get_param('output') ? $request->get_param('output') : 'esc_html';

        // page
        $this->page = $request->get_param('page') ? intval($request->get_param('page')) : 1;

        // limit
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : $this->limit;
        $this->limit = min($limit, $this->maxItems);

        // category
        if ($request->get_param('category') && is_array($request->get_param('category'))) {
            $this->categoryPosts = $request->get_param('category');
        }
        if ($request->get_param('category') && ! is_array($request->get_param('category'))) {
            $this->categoryPosts = [$request->get_param('category')];
        }

        // tags
        if ($request->get_param('subject') && is_array($request->get_param('subject'))) {
            $this->tagPosts = $request->get_param('subject');
        }
        if ($request->get_param('subject') && ! is_array($request->get_param('subject'))) {
            $this->tagPosts = [$request->get_param('subject')];
        }

        // post type
        if ($request->get_param('type') && is_array($request->get_param('type'))) {
            $this->typePosts = $request->get_param('type');
        }
        if ($request->get_param('type') && ! is_array($request->get_param('type'))) {
            $this->typePosts = [$request->get_param('type')];
        }

        $xml = $this->getSitemap();

        $response = new \WP_REST_Response();
        $response->set_data($xml);
        $response->set_status(200);
        header('Content-Type: application/xml; charset=' . $site->charset);
        echo $this->output === 'esc_html' ? wp_kses($response->get_data(), true) : $response->get_data();
        exit;
    }

    private function getSitemap() : string
    {
        $sitemap = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" ?>
            <urlset 
              xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            </urlset>'
        );

        $this->getItems($sitemap);
        $dom = dom_import_simplexml($sitemap)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private function getItems($sitemap)
    {
        $items = [];

        if ($this->typeSitemap == 'page') {
            $items = array_merge($items, (new Page())->getLasts(
                $this->limit,
                $this->page
            ));
        }

        if ($this->typeSitemap == 'post') {
            $items = array_merge($items, (new Article())->getLasts(
                $this->limit,
                $this->typePosts,
                $this->categoryPosts,
                $this->tagPosts,
                $this->page
            ));
        }

        foreach ($items as $item) {
            $item = (object) $item;
            $url = $sitemap->addChild('url');
            $url->addChild('loc', $item->link);
            $url->addChild('lastmod', gmdate('Y-m-d\TH:i:s\Z', strtotime($item->modDate)));
            $url->addChild('changefreq', 'weekly'); // always, hourly, daily, weekly, monthly, yearly, never
            $url->addChild('priority', '0.8'); // 0.0 e 1.0
        }
    }
}
