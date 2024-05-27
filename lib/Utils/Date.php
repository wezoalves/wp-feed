<?php
/*
 * Plugin Name: Amazing Feed
 */
namespace Wezo\Plugin\Feed\Utils;

class Date
{
  /**
   * Determines if the given date is in the first or second half of the year.
   *
   * @param string $date A date string in 'Y-m-d' format.
   * @return int 1 if the date is in the first half of the year, 2 otherwise.
   */
  public function getHalfOfYear($date) : int
  {
    $dateTime = new \DateTime($date);
    $month = (int) $dateTime->format('m');
    return $month <= 6 ? 1 : 2;
  }
  /**
   * Gets the date of a post in ISO 8601 format.
   *
   * @param int $postId The ID of the post.
   * @param string $type The type of date to retrieve ('publish' or 'modified').
   * @return string The date in ISO 8601 format.
   */
  public function getDate($postId, $type = 'publish')
  {
    if ($type === 'modified') {
      return get_the_modified_date(DATE_ISO8601, $postId);
    }
    return get_the_date(DATE_ISO8601, $postId);
  }

  /**
   * Formats a given date to 'Y-m-d\TH:i:sO' format.
   *
   * @param string $date A date string.
   * @return string The formatted date string.
   */
  public function formatToIso8601($date)
  {
    $dateTime = new \DateTime($date);
    return $dateTime->format('Y-m-d\TH:i:sO');
  }

  /**
   * Gets a date 5 years after the given date in 'Y-m-d\TH:i:sO' format.
   *
   * @param string $date A date string.
   * @return string The date 5 years later in the specified format.
   */
  public function getDateYearsLater($date, $years = 5)
  {
    $dateTime = new \DateTime($date);
    $dateTime->modify("+{$years} years");
    return $dateTime->format('Y-m-d\TH:i:sO');
  }
}
