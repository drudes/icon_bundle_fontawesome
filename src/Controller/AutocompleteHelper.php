<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type AutocompleteResultEntry array{
 *  value: string,
 *  label:\Drupal\Core\StringTranslation\TranslatableMarkup
 * }
 */
class AutocompleteHelper {

  /**
   * @phpstan-param string[] $strings
   * @phpstan-return AutocompleteResultEntry[]
   */
  public static function filterByLastWord(Request $request, array $strings): array {
    if ('' === ($typed_tag = self::getLastWord($request))) {
      return [];
    }
    return static::filterByWord($typed_tag, $strings);
  }

  /**
   * @phpstan-param string[] $strings
   * @phpstan-return AutocompleteResultEntry[]
   */
  public static function filterByWord(string $word, array $strings): array {
    $response = [];

    foreach ($strings as $string) {
      $matched = mb_strstr($string, $word);
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
   * @phpstan-return string[]
   */
  public static function getWords(Request $request): array {
    $input = self::getInput($request);
    return preg_split('/\s+/', mb_strtolower($input), -1, PREG_SPLIT_NO_EMPTY);
  }

  /**
   */
  public static function getLastWord(Request $request): string {
    if (empty($words = self::getWords($request))) {
      return '';
    }

    return array_pop($words);
  }

}
