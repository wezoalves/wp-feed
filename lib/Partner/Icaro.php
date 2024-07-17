<?php
/*
 * Plugin Name: Amazing Feed
 */
namespace Wezo\Plugin\Feed\Partner;

use Wezo\Plugin\Feed\Core\Blog;
use Wezo\Plugin\Feed\Core\Article;

class Icaro
{
    private int $maxArticles = 500;
    private int $sizeArticles = 30;
    private array $typeArticles = ['post'];

    public function callbackArticles($request)
    {
        // output
        $output = $request->get_param('output') ? intval($request->get_param('output')) : 'esc_html';

        // limit
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : $this->sizeArticles;
        $limit = min($limit, $this->maxArticles);

        // type
        if ($request->get_param('type') && is_array($request->get_param('type'))) {
            $this->typeArticles = $request->get_param('type');
        }
        if ($request->get_param('type') && ! is_array($request->get_param('type'))) {
            $this->typeArticles = [$request->get_param('type')];
        }

        // category
        $categoriesSlug = [];
        if ($request->get_param('category') && is_array($request->get_param('category'))) {
            $categoriesSlug = $request->get_param('category');
        }
        if ($request->get_param('category') && ! is_array($request->get_param('category'))) {
            $categoriesSlug = [$request->get_param('category')];
        }

        // tags
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
        $rss = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><rss version="2.0"></rss>');
        $rss->addAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $rss->addAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
        $rss->addAttribute('xmlns:dc', 'dc=http://purl.org/dc/elements/1.1/');
        $rss->addAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $rss->addAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');

        $channel = $rss->addChild('channel');
        $channel->addChild('title', $site->title);
        $channel->addChild('description', $site->description);
        $channel->addChild('language', $site->language);
        $channel->addChild('link', $site->link);
        $channel->addChild('managingEditor', $site->managing);
        $atomLinkElement = $channel->addChild('atom:link', null, 'http://www.w3.org/2005/Atom');
        $atomLinkElement->addAttribute('href', $site->atomLink);
        $atomLinkElement->addAttribute('rel', 'self');
        $atomLinkElement->addAttribute('type', 'application/rss+xml');

        $this->getItems($rss, $limit, $categoriesSlug, $tagsSlug);

        $dom = dom_import_simplexml($rss)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    private function getItems($rss, $limit = 0, $categoriesSlug = [], $tagsSlug = [])
    {
        if (! $rss) {
            return;
        }

        $articles = (new Article)->getLasts($limit, $this->typeArticles, $categoriesSlug, $tagsSlug);

        foreach ($articles as $post) :
            $post = (object) $post;

            $article = $rss->addChild('item');

            $guidElement = $article->addChild('guid', $post->guid);
            $guidElement->addAttribute('isPermaLink', 'false');

            $article->addChild('title', $post->title);
            $article->addChild('pubDate', $post->pubDate);
            $article->addChild('dc:creator', $post->authorName, 'http://purl.org/dc/elements/1.1/');
            $article->addChild('author', $post->authorName);

            $article->addChild('media:credit', $post->thumbnailCaption, 'http://search.yahoo.com/mrss/');
            $article->addChild('link', $post->link);
            $article->addChild('description', $post->description);

            $contentEncodedElement = $article->addChild('content:encoded', null, 'http://purl.org/rss/1.0/modules/content/');
            $contentEncodedDom = dom_import_simplexml($contentEncodedElement);

            // remove coments
            $postContent = preg_replace('/<!--(.*?)-->/', '', $post->content);

            $contentCdata = $contentEncodedDom->ownerDocument->createCDATASection($postContent);
            $contentEncodedDom->appendChild($contentCdata);

            $article->addChild('media:thumbnail', $post->thumbnailUrl, 'http://search.yahoo.com/mrss/');
            $article->addChild('dcterms:valid', "start={$post->formattedPubDate}; end={$post->formattedDateYearsLater}; scheme=W3C-DTF", 'http://purl.org/dc/terms/');
            $article->addChild('dcterms:dateTimeWritten', $post->pubDate, 'http://purl.org/dc/terms/');
            $article->addChild('dcterms:modified', $post->modDate, 'http://purl.org/dc/terms/');
            $article->addChild('media:keywords', $post->keywords, 'http://search.yahoo.com/mrss/');
            $article->addChild('dcterms:shortTitle', $post->title, 'http://purl.org/dc/terms/');
            $article->addChild('dcterms:alternative', $post->description, 'http://purl.org/dc/terms/');

            $relateds = (new Article)->getRelated(5, $post->id);

            foreach ($relateds as $related) {
                $related = (object) $related;
                if ($related->thumbnailUrl) {
                    $linkElement = $article->addChild('atom:link', null, 'http://www.w3.org/2005/Atom');
                    $linkElement->addAttribute('rel', 'related');
                    $linkElement->addAttribute('type', 'text/html');
                    $linkElement->addAttribute('href', $related->url);
                    $linkElement->addAttribute('title', $related->title);

                    $thumbnailElement = $linkElement->addChild('media:thumbnail', null, 'http://search.yahoo.com/mrss/');
                    $thumbnailElement->addAttribute('url', $related->thumbnailUrl);
                }
            }
        endforeach;
    }
}