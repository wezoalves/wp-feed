<?php
/*
 * Plugin Name: Amazing Feed
 */
namespace Wezo\Plugin\Feed\Partner;

use Wezo\Plugin\Feed\Core\Blog;
use Wezo\Plugin\Feed\Core\Article;

/**
 * Class GoogleNews
 * Generates RSS feed compatible with Google News.
 *
 * @package Wezo\Plugin\Feed\Partner
 */
class GoogleNews
{
    /**
     * Maximum number of articles allowed in the feed.
     *
     * @var int $maxArticles
     */
    private int $maxArticles = 500;

    /**
     * Default number of articles to retrieve if not specified in the request.
     *
     * @var int $sizeArticles
     */
    private int $sizeArticles = 30;

    /**
     * Default type of articles to retrieve if not specified in the request.
     *
     * @var array $typeArticles
     */
    private array $typeArticles = ['post'];

    /**
     * Retrieves articles for the Google News platform.
     *
     * @param \WP_REST_Request $request The REST request object.
     */
    public function callbackArticles($request)
    {

        $output = $request->get_param('output') ? intval($request->get_param('output')) : 'esc_html';

        // Determine the limit based on the request or default size
        $limit = $request->get_param('num') ? intval($request->get_param('num')) : $this->sizeArticles;
        $limit = min($limit, $this->maxArticles);

        // Set the type of articles to retrieve
        if ($request->get_param('type') && is_array($request->get_param('type'))) {
            $this->typeArticles = $request->get_param('type');
        }
        if ($request->get_param('type') && ! is_array($request->get_param('type'))) {
            $this->typeArticles = [$request->get_param('type')];
        }

        // Set the categories of posts to retrieve
        $categoriesSlug = [];
        if ($request->get_param('category') && is_array($request->get_param('category'))) {
            $categoriesSlug = $request->get_param('category');
        }
        if ($request->get_param('category') && ! is_array($request->get_param('category'))) {
            $categoriesSlug = [$request->get_param('category')];
        }

        // Set the tags of posts to retrieve
        $tagsSlug = [];
        if ($request->get_param('subject') && is_array($request->get_param('subject'))) {
            $tagsSlug = $request->get_param('subject');
        }
        if ($request->get_param('subject') && ! is_array($request->get_param('subject'))) {
            $tagsSlug = [$request->get_param('subject')];
        }

        // Get site information
        $site = (new Blog())->getInfo($request, true);

        // Generate the RSS feed
        $xml = $this->getRss($site, $limit, $categoriesSlug, $tagsSlug);

        $response = new \WP_REST_Response();
        $response->set_data($xml);
        $response->set_status(200);
        header('Content-Type: application/xml; charset=' . $site->charset);
        echo $output == 'esc_html' ? wp_kses($response->get_data(), true) : $response->get_data();
        exit;
    }

    /**
     * Generates the RSS feed for Google News.
     *
     * @param object|null $site Blog information.
     * @param int $limit Number of articles to retrieve.
     * @param array $categoriesSlug Categories of posts to retrieve.
     * @param array $tagsSlug Tags of posts to retrieve.
     * @return string The generated RSS feed as a string.
     */
    private function getRss($site = null, $limit = 0, $categoriesSlug = [], $tagsSlug = []) : string
    {

        // Create the XML structure for the RSS feed
        $rss = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" ?>
            <urlset 
              xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
              xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
            </urlset>'
        );

        // Populate the RSS feed with articles
        $this->getItems($rss, $site, $limit, $categoriesSlug, $tagsSlug);

        // Format and return the XML feed
        $dom = dom_import_simplexml($rss)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    /**
     * Retrieves items to be included in the RSS feed.
     *
     * @param \SimpleXMLElement $rss The XML RSS element.
     * @param object|null $site Blog information.
     * @param int $limit Number of articles to retrieve.
     * @param array $categoriesSlug Categories of posts to retrieve.
     * @param array $tagsSlug Tags of posts to retrieve.
     */
    private function getItems($rss, $site, $limit = 0, $categoriesSlug = [], $tagsSlug = [])
    {
        // Retrieve the articles to include in the feed
        $articles = (new Article())->getLasts($limit, $this->typeArticles, $categoriesSlug, $tagsSlug);
        
        // Add each article to the XML RSS feed
        foreach ($articles as $post) {
            $post = (object) $post;

            // Create the URL element
            $url = $rss->addChild('url');
            $url->addChild('loc', $post->link);

            // Add news-related elements
            $news = $url->addChild('news:news', null, 'http://www.google.com/schemas/sitemap-news/0.9');
            $publication = $news->addChild('news:publication');
            $publication->addChild('news:name', $site->title);
            $publication->addChild('news:language', $site->language);
            $news->addChild('news:publication_date', $post->pubDate);
            $news->addChild('news:title', $post->title);
            $news->addChild('news:keywords', $post->keywords);

            // Add optional news-related elements
            $news->addChild('news:access', 'Free'); // Registration / Subscription / Free / Behind paywall
            $news->addChild('news:genres', 'Feature'); // PressRelease / Opinion / Blog / Feature / Review
        }
    }
}