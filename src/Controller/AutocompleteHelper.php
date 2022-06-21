<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Controller;

use Drupal\Component\Utility\Tags;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class AutocompleteHelper {

  /**
   *
   */
  public static function filterByLastTag(Request $request, array $strings): array {
    if ('' === ($typed_tag = self::getLastTag($request))) {
      return [];
    }
    return static::filterByTag($typed_tag, $strings);
  }

  /**
   *
   */
  public static function filterByTag(string $tag, array $strings): array {
    $response = [];

    foreach ($strings as $string) {
      $matched = mb_strstr($string, $tag);
      if (FALSE !== $matched && mb_strlen($matched) > 0) {
        $response[] = [
          'value' => $string,
          'label' => t($string),
        ];
      }
    }

    return $response;
  }

  /**
   *
   */
  public static function getInput(Request $request): string {
    // Get the value of q from the query string.
    if (is_string($input = $request->query->get('q', ''))) {
      return $input;
    }

    return '';
  }

  /**
   *
   */
  public static function getTags(Request $request): array {
    $input = self::getInput($request);
    return Tags::explode(mb_strtolower($input));
  }

  /**
   *
   */
  public static function getLastTag(Request $request): string {
    if (empty($tags = self::getTags($request))) {
      return '';
    }

    return array_pop($tags);
  }

}
