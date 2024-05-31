<?php
/*
 * Plugin Name: Amazing Feed
 */
namespace Wezo\Plugin\Feed\Core;

class Page
{
    public int $widthFallback = 750;
    public int $heightFallback = 500;
    public string $creditFallback = 'Reprodução';

    public function getLasts($size = 30, $page = 1) : array
    {
        $args = array(
            'post_type' => ['page'],
            'posts_per_page' => $size,
            'post_status' => 'publish',
            'orderby' => array(
                'date' => 'DESC',
                'title' => 'ASC'
            ),
            'paged' => $page
        );

        $query = new \WP_Query($args);

        $pages = [];
        $dateUtils = new \Wezo\Plugin\Feed\Utils\Date();

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
                $description = (new \Wezo\Plugin\Feed\Utils\Text())->FixText($description);
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
                $thumbnailWidth = $dimensions['width'] ?? $this->widthFallback;
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
                $content = (new \Wezo\Plugin\Feed\Utils\Text())->FixText($content);
                $content = preg_replace('/\r\n|\r|\n/', '<br>', $content);
                $contentEncoded = "";
                if ($thumbnailUrl) {
                    $contentEncoded .= "<img src=\"{$thumbnailUrl}\" alt=\"{$thumbnailAlt}\" width=\"{$thumbnailWidth}px\" height=\"{$thumbnailHeight}px\" title=\"{$thumbnailTitle}\" data-portal-copyright=\"{$thumbnailCaption}\"  /> ";
                }
                $contentEncoded .= $content;

                $pages[] = [
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
                    'thumbnailWidth' => $thumbnailWidth,
                    'title' => $title,
                ];
            }
            wp_reset_postdata();
        }
        return $pages;
    }
    public function getTotals($limit = 100)
    {
        $args = array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        );

        $query = new \WP_Query($args);
        return ceil($query->found_posts / $limit);
    }
}