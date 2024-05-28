<?php
/*
 * Plugin Name: Amazing Feed
 */
namespace Wezo\Plugin\Feed\Partner;

use Wezo\Plugin\Feed\Core\Blog;
use Wezo\Plugin\Feed\Core\Article;

class GoogleNews
{
    private int $maxArticles = 500;

    private int $sizeArticles = 30;

    private array $typeArticles = ['post'];

    public function callbackArticles($request)
    {

        $output = $request->get_param('output') ? intval($request->get_param('output')) : 'esc_html';

        $limit = $request->get_param('num') ? intval($request->get_param('num')) : $this->sizeArticles;
        $limit = min($limit, $this->maxArticles);

        if ($request->get_param('type') && is_array($request->get_param('type'))) {
            $this->typeArticles = $request->get_param('type');
        }
        if ($request->get_param('type') && ! is_array($request->get_param('type'))) {
            $this->typeArticles = [$request->get_param('type')];
        }

        $categoriesSlug = [];
        if ($request->get_param('category') && is_array($request->get_param('category'))) {
            $categoriesSlug = $request->get_param('category');
        }
        if ($request->get_param('category') && ! is_array($request->get_param('category'))) {
            $categoriesSlug = [$request->get_param('category')];
        }

        $tagsSlug = [];
        if ($request->get_param('subject') && is_array($request->get_param('subject'))) {
            $tagsSlug = $request->get_param('subject');
        }
        if ($request->get_param('subject') && ! is_array($request->get_param('subject'))) {
            $tagsSlug = [$request->get_param('subject')];
        }

        $site = (new Blog())->getInfo($request, true);

        $xml = $this->getRss($site, $limit, $categoriesSlug, $tagsSlug);

        $response = new \WP_REST_Response();
        $response->set_data($xml);
        $response->set_status(200);
        header('Content-Type: application/xml; charset=' . $site->charset);
        echo $output == 'esc_html' ? wp_kses($response->get_data(), true) : $response->get_data();
        exit;
    }

    private function getRss($site = null, $limit = 0, $categoriesSlug = [], $tagsSlug = []) : string
    {

        $rss = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" ?>
            <urlset 
              xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
              xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
            </urlset>'
        );

        $this->getItems($rss, $site, $limit, $categoriesSlug, $tagsSlug);

        $dom = dom_import_simplexml($rss)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private function getItems($rss, $site, $limit = 0, $categoriesSlug = [], $tagsSlug = [])
    {

        $articles = (new Article())->getLasts($limit, $this->typeArticles, $categoriesSlug, $tagsSlug);


        foreach ($articles as $post) {
            $post = (object) $post;


            $url = $rss->addChild('url');
            $url->addChild('loc', $post->link);


            $news = $url->addChild('news:news', null, 'http://www.google.com/schemas/sitemap-news/0.9');
            $publication = $news->addChild('news:publication');
            $publication->addChild('news:name', $site->title);
            $publication->addChild('news:language', $site->language);
            $news->addChild('news:publication_date', $post->pubDate);
            $news->addChild('news:title', $post->title);
            $news->addChild('news:keywords', $post->keywords);

            $news->addChild('news:access', 'Free'); // Registration / Subscription / Free / Behind paywall
            $news->addChild('news:genres', 'Feature'); // PressRelease / Opinion / Blog / Feature / Review
        }
    }
}