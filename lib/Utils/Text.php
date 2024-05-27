<?php

namespace Wezo\Plugin\Feed\Utils;

class Text
{

  public function FixText($content = null)
  {
    $content = html_entity_decode($content);

    $content = strtr($content, [
      "[…]" => '…'
    ]);
    
    return $content;
  }
}
