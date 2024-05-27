<?php
/*
 * Plugin Name: Amazing Feed
 */
namespace Wezo\Plugin\Feed\Partner;

use Wezo\Plugin\Feed\Core\Blog;
use Wezo\Plugin\Feed\Core\Article;

/**
 * A classe Icaro é responsável por gerar e retornar um feed RSS personalizado de artigos.
 */
class Icaro
{
    /**
     * @var int $maxArticles Número máximo de artigos que podem ser retornados.
     */
    private int $maxArticles = 500;

    /**
     * @var int $sizeArticles Número padrão de artigos a serem retornados.
     */
    private int $sizeArticles = 30;

    /**
     * @var array $typeArticles Tipos de artigos a serem retornados.
     */
    private array $typeArticles = ['post'];

    /**
     * Método que serve como callback para retornar artigos em formato RSS.
     *
     * @param \WP_REST_Request $request Objeto de requisição do WordPress REST API.
     */
    public function callbackArticles($request)
    {

        $output = $request->get_param('output') ? intval($request->get_param('output')) : 'esc_html';

        // Determina o limite baseado na requisição ou usa o tamanho padrão
        $limit = $request->get_param('num') ? intval($request->get_param('num')) : $this->sizeArticles;
        $limit = min($limit, $this->maxArticles);

        // Define o tipo de artigos a serem recuperados
        if ($request->get_param('type') && is_array($request->get_param('type'))) {
            $this->typeArticles = $request->get_param('type');
        }
        if ($request->get_param('type') && ! is_array($request->get_param('type'))) {
            $this->typeArticles = [$request->get_param('type')];
        }

        // Define as categorias dos posts a serem recuperados
        $categoriesSlug = [];
        if ($request->get_param('category') && is_array($request->get_param('category'))) {
            $categoriesSlug = $request->get_param('category');
        }
        if ($request->get_param('category') && ! is_array($request->get_param('category'))) {
            $categoriesSlug = [$request->get_param('category')];
        }

        // Obtém informações do site
        $site = (new Blog())->getInfo($request, true);

        // Gera o RSS
        $xml = $this->getRss($site, $limit, $categoriesSlug);

        $response = new \WP_REST_Response();
        $response->set_data($xml);
        $response->set_status(200);
        header('Content-Type: application/xml; charset=' . $site->charset);
        echo $output == 'esc_html' ? wp_kses($response->get_data(), true) : $response->get_data();
        exit;
    }

    /**
     * Gera o RSS dos artigos.
     *
     * @param object|null $site Informações do site.
     * @param int $limit Limite de artigos a serem retornados.
     * @param array $categoriesSlug Categorias dos artigos a serem retornados.
     * @return string XML do feed RSS.
     */
    private function getRss($site = null, $limit = 0, $categoriesSlug = []) : string
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

        $this->getItems($rss, $limit, $categoriesSlug);

        $dom = dom_import_simplexml($rss)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Adiciona itens ao RSS.
     *
     * @param \SimpleXMLElement $rss Objeto SimpleXMLElement representando o RSS.
     * @param object $site Informações do site.
     * @param int $limit Limite de artigos a serem adicionados.
     * @param array $categoriesSlug Categorias dos artigos a serem adicionados.
     */
    private function getItems($rss, $limit = 0, $categoriesSlug = [])
    {
        if (! $rss) {
            return;
        }

        $articles = (new Article)->getLasts($limit, $this->typeArticles, $categoriesSlug);

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