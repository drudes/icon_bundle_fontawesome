<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type IconDataInputEntry array{
 *  label?: string,
 *  styles?: string[],
 *  aliases?: array{
 *    names?: string[],
 *  }
 * }
 *
 * @phpstan-import-type IconsDataArray from \Drupal\icon_bundle_fontawesome\Metadata\MetadataProviderInterface
 * @phpstan-import-type IconsSearchArray from \Drupal\icon_bundle_fontawesome\Metadata\MetadataProviderInterface
 */
final class MetadataProvider implements MetadataProviderInterface, ContainerInjectionInterface {
  /**
   * @var MetadataLocatorInterface
   */
  protected $metadataLocator;

  /**
   *
   */
  public function __construct(MetadataLocatorInterface $metadata_locator) {
    $this->metadataLocator = $metadata_locator;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container): static {
    $metadata_locator = MetadataLocator::create($container);

    return new self($metadata_locator);
  }

  /**
   * {@inheritdoc}
   */
  public function getIconsDataArray(string $file = NULL): array {
    $file ??= 'icons.yml';
    if (NULL === ($location = $this->metadataLocator->getLocation($file))) {
      return [];
    }

    $location_id = base64_encode($location);
    $info = [
      'location_id' => $location_id,
    ];
    if (NULL === ($data_array = self::getCachedIconsDataArray($location, $location_id, $info))) {
      return [];
    }

    return $data_array;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconsSearchArray(array $data_array): array {
    if (NULL === ($location_id = $data_array['_info']['location_id'] ?? NULL)) {
      return self::buildIconsSearchArray($data_array);
    }

    return self::getCachedIconsSearchArray($data_array, $location_id);
  }

  /**
   * @phpstan-return IconsSearchArray
   */
  public static function getCachedIconsSearchArray(array $data_array, string $location_id): array {
    $cache_id = 'icon_bundle_fontawesome_icon_search_array:' . $location_id;
    if (!$cached_array = \Drupal::cache('data')->get($cache_id)) {
      $search_array = self::buildIconsSearchArray($data_array);

      \Drupal::cache('data')->set($cache_id, $search_array, strtotime('+1 hour'), [
        'icon_bundle_fontawesome_icon_data_array:' . $location_id,
      ]);
    }
    else {
      $search_array = (array) $cached_array->data;
    }

    return $search_array;
  }

  /**
   * Get the icon data.
   *
   * @phpstan-return null|IconsDataArray
   */
  protected static function getCachedIconsDataArray(string $location, string $location_id, array $info): ?array {
    $cache_id = 'icon_bundle_fontawesome_icon_data_array:' . $location_id;

    if (!($cache = \Drupal::cache('data')->get($cache_id))) {
      $info['cache_id'] = $cache_id;
      if (NULL === ($data_array = self::parseIconsDataArrayYamlFile($location, $info))) {
        // We don't cache on error. Empty arrays are not interesting.
        return NULL;
      }
      \Drupal::cache('data')->set($cache_id, $data_array, strtotime('+1 hour'), []);
    }
    else {
      $data_array = $cache->data;
    }

    return (array) $data_array;
  }

  /**
   * @phpstan-return null|IconsDataArray
   */
  protected static function parseIconsDataArrayYamlFile(string $location, array $info): ?array {
    // Check if the icons.yml file exists.
    if (FALSE === ($contents = file_get_contents($location))) {
      return NULL;
    }

    return self::parseIconsDataArrayYamlString($contents, $info);
  }

  /**
   * @phpstan-return null|IconsDataArray
   */
  protected static function parseIconsDataArrayYamlString(string $contents, array $info): ?array {
    try {
      $array = Yaml::parse($contents);
    }
    catch (ParseException $e) {
      return NULL;
    }

    return self::parseIconsDataArray($array, $info);
  }

  /**
   * @phpstan-param array<string,IconDataInputEntry> $array
   * @phpstan-return IconsDataArray
   */
  protected static function parseIconsDataArray(array $array, array $info): array {
    $data_array = ['_info' => $info];
    // Traverse through every icon.
    foreach ($array as $name => $data) {
      $styles = $data['styles'] ?? [];
      $label = $data['label'] ?? NULL;
      $type = empty($styles) ? NULL : reset($styles);

      if (NULL !== $label && NULL !== $type) {
        $data_array[$name] = [
          'name'    => $name,
          'type'    => $type,
          'label'   => $label,
          'styles'  => $styles,
          'aliases' => [
            'names' => $data['aliases']['names'] ?? [],
          ],
        ];
      }
    }

    return $data_array;
  }

  /**
   * @phpstan-return IconsSearchArray
   */
  protected static function buildIconsSearchArray(array $data_array): array {
    $search_array = [];
    foreach ($data_array as $icon_key => $icon_data) {
      $icon_names = array_merge([$icon_key], $icon_data['aliases']['names'] ?? []);
      foreach ($icon_names as $icon_name) {
        if (!isset($search_array[$icon_name])) {
          $search_array[$icon_name] = [];
        }
        if (!in_array((string) $icon_key, $search_array[$icon_name])) {
          $search_array[$icon_name][] = (string) $icon_key;
        }
      }
    }

    foreach ($search_array as $icon_name => $icon_keys) {
      $icon_name = (string) $icon_name;
      $length = mb_strlen($icon_name);
      for ($i = 1; $i < $length; ++$i) {
        $key = mb_substr($icon_name, 0, $i);
        if (!isset($search_array[$key])) {
          $search_array[$key] = [];
        }
        $search_array[$key] = array_unique(array_merge($search_array[$key], $icon_keys));
      }
    }

    return $search_array;
  }

}
