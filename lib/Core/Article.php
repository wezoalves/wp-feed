<?php

namespace Wezo\Plugin\Feed\Core;

/**
 * Class Article
 *
 * @package Wezo\Plugin\Feed\Core
 */
class Article
{
  /**
   * Default width for thumbnails if not provided.
   *
   * @var int
   */
  public int $widthFallback = 750;
  /**
   * Default height for thumbnails if not provided.
   *
   * @var int
   */
  public int $heightFallback = 500;
  /**
   * Default credit for thumbnails if not provided.
   *
   * @var string
   */
  public string $creditFallback = 'Reprodução';

  /**
   * Retrieves the last articles based on specified parameters.
   *
   * @param int $size The number of articles to retrieve.
   * @param array $type The type of posts to retrieve.
   * @return array An array of articles.
   */
  public function getLasts($size = 30, array $type = ['post'], array $categorySlug = [])
  {

    $args = array(
      'post_type' => $type,
      'posts_per_page' => $size,
      'post_status' => 'publish',
      'orderby' => array(
        'date' => 'DESC',
        'title' => 'ASC'
      )
    );

    if ($categorySlug && ! empty($categorySlug)) :

      $category_ids = [];

      foreach ($categorySlug as $slug) {
        $category = get_term_by('slug', $slug, 'category');
        if ($category) {
          $category_ids[] = $category->term_id;
        }
      }
      $args['category__in'] = $category_ids;
    endif;

    $query = new \WP_Query($args);

    $articles = [];
    $dateUtils = (new \Wezo\Plugin\Feed\Utils\Date());

    if ($query->have_posts()) {

      while ($query->have_posts()) {

        $query->the_post();

        if (! get_the_ID()) {
          continue;
        }

        $postId = get_the_ID();
        $title = get_the_title($postId);
        $authorName = get_the_author_meta('display_name', get_the_author_meta('ID'));
        $description = wp_strip_all_tags(get_the_excerpt($postId));
        $description = $description ? $description : $title;
        $guid = "{$postId}";
        $link = get_permalink($postId);


        $thumbnailId = get_post_thumbnail_id($postId);
        $thumbnailAlt = get_post_meta($postId, '_wp_attachment_image_alt', true);
        $thumbnailAlt = $thumbnailAlt ? $thumbnailAlt : "{$title}";
        $thumbnailTitle = get_the_title($thumbnailId);
        $thumbnailCaption = wp_get_attachment_caption($postId);
        $thumbnailCaption = $thumbnailCaption ? $thumbnailCaption : $this->creditFallback;
        $dimensions = wp_get_attachment_metadata($thumbnailId);
        $thumbnailUrl = get_the_post_thumbnail_url($postId, 'full');
        $thumbnailWith = $dimensions['width'] ?? $this->widthFallback;
        $thumbnailHeight = $dimensions['height'] ?? $this->heightFallback;

        $defaultKeyword = implode(',', wp_list_pluck(wp_get_post_terms($postId, 'category'), 'name'));
        $tags = wp_get_post_terms($postId, 'post_tag');
        $tagNames = wp_list_pluck($tags, 'name');
        $tagsString = implode(',', $tagNames);
        $keywords = strtolower($tagsString) ?? $defaultKeyword;

        $pubDate = $dateUtils->getDate($postId, 'publish');
        $modDate = $dateUtils->getDate($postId, 'modified');
        $formattedPubDate = $dateUtils->formatToIso8601($pubDate);
        $formattedDateYearsLater = $dateUtils->getDateYearsLater($pubDate, 5);

        $content = get_the_content();
        $content = preg_replace('/\r\n|\r|\n/', '<br>', $content);
        $contentEncoded = "";
        if ($thumbnailUrl) :
          $contentEncoded .= "<img src=\"{$thumbnailUrl}\" alt=\"{$thumbnailAlt}\" width=\"{$thumbnailWith}px\" height=\"{$thumbnailHeight}px\" title=\"{$thumbnailTitle}\" data-portal-copyright=\"{$thumbnailCaption}\"  /> \n ";
        endif;
        $contentEncoded .= $content;

        $articles[] = [
          'authorName' => $authorName,
          'content' => $contentEncoded,
          'description' => $description,
          'formattedDateYearsLater' => $formattedDateYearsLater,
          'formattedPubDate' => $formattedPubDate,
          'guid' => $guid,
          'id' => $postId,
          'keywords' => $keywords,
          'link' => $link,
          'modDate' => $modDate,
          'pubDate' => $pubDate,
          'thumbnailAlt' => $thumbnailAlt,
          'thumbnailCaption' => $thumbnailCaption,
          'thumbnailHeight' => $thumbnailHeight,
          'thumbnailId' => $thumbnailId,
          'thumbnailTitle' => $thumbnailTitle,
          'thumbnailUrl' => $thumbnailUrl,
          'thumbnailWith' => $thumbnailWith,
          'title' => $title,
        ];


      }
      wp_reset_postdata();
    }
    return $articles;
  }

  /**
   * Retrieves related articles based on specified parameters.
   *
   * @param int $size The number of related articles to retrieve.
   * @param int|null $postId The ID of the post to find related articles for.
   * @param array $type The type of posts to consider.
   * @return array An array of related articles.
   */
  public function getRelated($size = 5, $postId = null, $type = ['post'])
  {

    if (! $postId) {
      return [];
    }

    $categories = get_the_category($postId);

    $currentSlug = null;
    if (! empty($categories)) {
      $currentSlug = $categories[0]->slug;
    }

    $query = new \WP_Query([
      'category_name' => $currentSlug,
      'orderby' => array(
        'date' => 'DESC',
        'title' => 'ASC'
      ),
      'posts_per_page' => $size,
      'post_type' => $type,
      'date_query' => [
        [
          'column' => 'post_date_gmt',
          'after' => '1 year ago',
        ],
      ],
    ]);

    $articles = [];
    if ($query->have_posts()) {
      while ($query->have_posts()) {

        $postId = get_the_ID();

        $creator = get_the_author();
        $description = wp_strip_all_tags(get_the_excerpt());
        $guid = $postId;
        $link = get_permalink();
        $pubDate = get_the_date('c', $postId);
        $query->the_post();
        $title = get_the_title();

        $thumbnailId = get_post_thumbnail_id($postId);
        $thumbnailAlt = get_post_meta($postId, '_wp_attachment_image_alt', true) ?? null;
        $thumbnailTitle = get_the_title($thumbnailId) ?? null;
        $thumbnailCaption = wp_get_attachment_caption($postId);
        $thumbnailCaption = preg_replace('/\r\n|\r|\n/', '', $thumbnailCaption);
        $thumbnailCaption = $thumbnailCaption ? $thumbnailCaption : $this->creditFallback;
        $dimensions = wp_get_attachment_metadata($thumbnailId);
        $thumbnailUrl = get_the_post_thumbnail_url($postId, 'full') ?? null;
        $thumbnailWith = $dimensions['width'] ?? $this->widthFallback;
        $thumbnailHeight = $dimensions['height'] ?? $this->heightFallback;

        $articles[] = [
          'author' => $creator,
          'description' => $description,
          'id' => $guid,
          'pubDate' => $pubDate,
          'thumbnailAlt' => $thumbnailAlt,
          'thumbnailCaption' => $thumbnailCaption,
          'thumbnailHeight' => $thumbnailHeight,
          'thumbnailTitle' => $thumbnailTitle,
          'thumbnailUrl' => $thumbnailUrl,
          'thumbnailWith' => $thumbnailWith,
          'title' => $title,
          'url' => $link,
        ];
      }
      wp_reset_postdata();
    }
    return $articles;
  }
}