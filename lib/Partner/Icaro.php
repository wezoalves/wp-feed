<?php

namespace Wezo\Plugin\Feed\Partner;

use Wezo\Plugin\Feed\Core\Blog;
use Wezo\Plugin\Feed\Core\Article;

/**
 * Class Icaro
 *
 * @package Wezo\Plugin\Feed\Partner
 */
class Icaro
{
  /**
   * Callback function to retrieve articles for the ICARO platform.
   *
   * @param \WP_REST_Request $request The REST request object.
   */
  public function callbackArticles(\WP_REST_Request $request)
  {
    $xml = $this->getRss($request);
    header('Content-Type: application/xml; charset=' . get_option('blog_charset'));
    echo ($xml);
    exit;
  }

  /**
   * Generates the RSS feed for the ICARO platform.
   *
   * @param \WP_REST_Request $request The REST request object.
   * @return string The generated RSS feed as a string.
   */
  private function getRss($request) : string
  {
    $size = $request->get_param('num') ? intval($request->get_param('num')) : 30;

    $info = (new Blog())->getInfo($request, true);

    $rss = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><rss version="2.0"></rss>');
    $rss->addAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
    $rss->addAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
    $rss->addAttribute('xmlns:dc', 'dc=http://purl.org/dc/elements/1.1/');
    $rss->addAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
    $rss->addAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');

    $channel = $rss->addChild('channel');
    $channel->addChild('title', $info->title);
    $channel->addChild('description', $info->description);
    $channel->addChild('language', $info->language);
    $channel->addChild('link', $info->link);
    $channel->addChild('managingEditor', $info->managing);
    $atomLinkElement = $channel->addChild('atom:link', null, 'http://www.w3.org/2005/Atom');
    $atomLinkElement->addAttribute('href', $info->atomLink);
    $atomLinkElement->addAttribute('rel', 'self');
    $atomLinkElement->addAttribute('type', 'application/rss+xml');

    $this->getItems($channel, $size);

    $dom = dom_import_simplexml($rss)->ownerDocument;
    $dom->formatOutput = true;

    return $dom->saveXML();
  }

  /**
   * Retrieves items to be included in the RSS feed.
   *
   * @param \SimpleXMLElement $channel The XML channel element.
   * @param int $size The number of items to retrieve.
   */
  private function getItems($channel, $size = 1)
  {

    if (! $channel) {
      return;
    }

    $articles = (new Article)->getLasts($size);

    foreach ($articles as $post) :

      $post = (object) $post;

      $article = $channel->addChild('item');

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
      $contentCdata = $contentEncodedDom->ownerDocument->createCDATASection($post->content);
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

