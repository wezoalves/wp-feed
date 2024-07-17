<?php

namespace Wezo\Plugin\Feed\Partner;

use Wezo\Plugin\Feed\Core\Blog;

class SitemapIndex
{
    private int $maxItems = 1000;
    private int $limit = 1000;
    private string $output = 'esc_html';
    private string $prefixEndpoint = "";
    private array $typePosts = ['post'];
    private array $categoryPosts = [];
    private array $tagPosts = [];
    private string $pathPrefix = 'apifeed';

    public function callbackSitemap($request)
    {

        $site = (new Blog())->getInfo($request, true);

        $this->prefixEndpoint = $site->link . '/' . $this->pathPrefix . '/sitemap/';

        // output
        $this->output = $request->get_param('output') ? $request->get_param('output') : 'esc_html';

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

        $xml = $this->getSitemapIndex();

        $response = new \WP_REST_Response();
        $response->set_data($xml);
        $response->set_status(200);
        header('Content-Type: application/xml; charset=' . $site->charset);
        echo $this->output === 'esc_html' ? wp_kses($response->get_data(), true) : $response->get_data();
        exit;
    }

    private function getSitemapIndex() : string
    {
        $xmlSitemap = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" ?>
            <sitemapindex 
              xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            </sitemapindex>'
        );

        // articles types
        foreach ($this->typePosts as $type) :
            $totalItemsType = $this->getTotalItems($type);
            $numPages = ceil($totalItemsType / $this->limit);

            for ($page = 1; $page <= $numPages; $page++) {
                $url = "{$this->prefixEndpoint}posts?output={$this->output}&amp;limit={$this->limit}&amp;type={$type}&amp;page={$page}";
                $sitemap = $xmlSitemap->addChild('sitemap');
                $sitemap->addChild('loc', $url);
                $sitemap->addChild('lastmod', gmdate('Y-m-d\TH:i:s\Z'));
            }

        endforeach;

        // pages 
        $totalPages = (new \Wezo\Plugin\Feed\Core\Page())->getTotals($this->limit);
        $numPages = ceil($totalPages / $this->limit);

        for ($page = 1; $page <= $numPages; $page++) {
            $url = "{$this->prefixEndpoint}pages?output={$this->output}&amp;limit={$this->limit}&amp;page={$page}";
            $sitemap = $xmlSitemap->addChild('sitemap');
            $sitemap->addChild('loc', $url);
            $sitemap->addChild('lastmod', gmdate('Y-m-d\TH:i:s\Z'));
        }

        $dom = dom_import_simplexml($xmlSitemap)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private function getTotalItems($type = null)
    {
        $args = array(
            'post_type' => $type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        );

        if ($this->categoryPosts && ! empty($this->categoryPosts)) {
            $category_ids = [];
            foreach ($this->categoryPosts as $slug) {
                $category = get_term_by('slug', $slug, 'category');
                if ($category) {
                    $category_ids[] = $category->term_id;
                }
            }
            $args['category__in'] = $category_ids;
        }

        if ($this->tagPosts && ! empty($this->tagPosts)) {
            $tag_ids = [];
            foreach ($this->tagPosts as $slug) {
                $tag = get_term_by('slug', $slug, 'post_tag');
                if ($tag) {
                    $tag_ids[] = $tag->term_id;
                }
            }
            $args['tag__in'] = $tag_ids;
        }

        $query = new \WP_Query($args);
        return $query->found_posts;
    }
}
