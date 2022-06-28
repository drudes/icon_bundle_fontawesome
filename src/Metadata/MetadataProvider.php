<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\UseCacheBackendTrait;
use Drupal\Core\Cache\RefineableCacheableDependencyTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
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

  use UseBackendCacheTrait;
  use RefineableCacheableDependencyTrait;

  /**
   * @var MetadataLocatorInterface
   */
  protected $metadataLocator;

//--  /**
//--   * @var CacheBackendInterface
//--   */
//--  protected $cacheBackend;

  /**
   * @var string
   */
  protected $cacheKey;

  /**
   *
   */
  public function __construct(MetadataLocatorInterface $metadata_locator, CacheBackendInterface $cache_backend, string $cache_key) {
    $this->metadataLocator = $metadata_locator;
    $this->cacheBackend = $cache_backend;
    $this->cacheKey = $cache_key;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container): self {
    $config_factory = $container->get('config.factory');
    $cache_backend = $container->get('cache.data');
    return self::createFromConfig($config_factory, $cache_backend);
  }

  public static function createFromConfig(ConfigFactoryInterface $config_factory, CacheBackendInterface $cache_backend): self {
    $config_key = 'icon_bundle_fontawesome.settings';
    $config = $config_factory->get($config_key);
    $metadata_locator = new MetadataLocator($config->getRawData());
    $metadata_provider = new self($metadata_locator, $cache_backend, 'icon_bundle_fontawesome:metadata');
    $metadata_provider->cacheTags = [
      'config:' . $config_key,
    ];
    return $metadata_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconsDataArray(): array {
    if (NULL === ($location = $this->metadataLocator->getLocation('icons.yml'))) {
      return [];
    }

    $cache_id = $this->cacheKey . ':icons_data_array';
    $cache_max_age = $this->getCacheMaxAge();

    if (0 === $cache_max_age || !($cache = $this->cacheBackend->get($cache_id))) {
      if (NULL === ($data_array = self::parseIconsDataArrayYamlFile($location))) {
        // We don't cache on error. Empty arrays are not interesting.
        return [];
      }
      if (0 !== $cache_max_age) {
        $cache_tags = Cache::mergeTags($this->getCacheTags(), [ $cache_id ]);
        $this->cacheBackend->set($cache_id, $data_array, $cache_max_age, $cache_tags);
      }
    }
    else {
      $data_array = (array)$cache->data;
    }

    return $data_array;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconsSearchArray(): array {
    $cache_id = $this->cacheKey . ':icons_search_array';
    $cache_max_age = $this->getCacheMaxAge();
    if (0 === $cache_max_age || !($cache = $this->cacheBackend->get($cache_id))) {
      $data_array = $this->getIconsDataArray();
      $search_array = self::buildIconsSearchArray($data_array);

      if (0 !== $cache_max_age) {
        $cache_tags = Cache::mergeTags($this->getCacheTags(), [
          'icon_bundle_fontawesome.metadata:icons_data_array',
          $cache_id,
        ]);
        $this->cacheBackend->set($cache_id, $search_array, $cache_max_age, $cache_tags);
      }
    }
    else {
      $search_array = (array) $cache->data;
    }

    return $search_array;
  }

  /**
   * @phpstan-param array<array-key,mixed> $info
   * @phpstan-return null|IconsDataArray
   */
  protected static function parseIconsDataArrayYamlFile(string $location): ?array {
    // Check if the icons.yml file exists.
    if (FALSE === ($contents = file_get_contents($location))) {
      return NULL;
    }

    return self::parseIconsDataArrayYamlString($contents);
  }

  /**
   * @phpstan-param array<array-key,mixed> $info
   * @phpstan-return null|IconsDataArray
   */
  protected static function parseIconsDataArrayYamlString(string $contents): ?array {
    try {
      $array = Yaml::parse($contents);
    }
    catch (ParseException $e) {
      return NULL;
    }

    return self::parseIconsDataArray($array);
  }

  /**
   * @phpstan-param array<string,IconDataInputEntry> $array
   * @phpstan-param array<array-key,mixed> $info
   * @phpstan-return IconsDataArray
   */
  protected static function parseIconsDataArray(array $array): array {
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
   * @phpstan-param IconsDataArray $data_array
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
