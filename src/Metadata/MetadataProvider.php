<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Cache\UseCacheBackendTrait;
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
final class MetadataProvider implements MetadataProviderInterface, ContainerInjectionInterface, CacheableDependencyInterface {

  use UseCacheBackendTrait;
  use RefinableCacheableDependencyTrait;

  /**
   * @var MetadataLocatorInterface
   */
  protected $metadataLocator;

  /**
   * @var string
   */
  protected $cacheKey;

  /**
   * @var null|array
   * @phpstan-var null|IconsDataArray
   */
  protected $iconsDataArray;

  /**
   * @var null|array
   * @phpstan-var null|IconsSearchArray
   */
  protected $iconsSearchArray;

  /**
   *
   */
  public function __construct(MetadataLocatorInterface $metadata_locator, CacheBackendInterface $cache_backend, string $cache_key) {
    $this->metadataLocator = $metadata_locator;
    $this->cacheBackend = $cache_backend;
    $this->cacheKey = $cache_key;
    $this->iconsDataArray = NULL;
    $this->iconsSearchArray = NULL;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container): self {
    $app_root = $container->getParameter('app.root');
    $config_factory = $container->get('config.factory');
    $cache_backend = $container->get('cache.data');
    return self::createFromConfig($app_root, $config_factory, $cache_backend);
  }

  /**
   *
   */
  public static function createFromConfig(string $app_root, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache_backend): self {
    $config_key = 'icon_bundle_fontawesome.settings';
    $config = $config_factory->get($config_key);
    $metadata_locator = new MetadataLocator($app_root, $config->getRawData());
    $metadata_provider = new self($metadata_locator, $cache_backend, 'icon_bundle_fontawesome:metadata');
    $metadata_provider->addCacheTags(['config:' . $config_key]);
    return $metadata_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconsDataArray(): array {
    if (!($icons_data_array = $this->getCachedIconsDataArray())) {
      if (NULL === ($location = $this->metadataLocator->getLocation('icons.yml'))) {
        return [];
      }
      if (NULL === ($icons_data_array = self::parseIconsDataArrayYamlFile($location))) {
        // We don't cache on error. Empty arrays are not interesting.
        return [];
      }
      $this->setCachedIconsDataArray($icons_data_array);
    }
    return $icons_data_array;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconsSearchArray(): array {
    if (!($icons_search_array = $this->getCachedIconsSearchArray())) {
      if ([] === ($icons_data_array = $this->getIconsDataArray())) {
        // We don't cache on error. Empty arrays are not interesting.
        return [];
      }
      $icons_search_array = self::buildIconsSearchArray($icons_data_array);
      $this->setCachedIconsSearchArray($icons_search_array);
    }
    return $icons_search_array;
  }

  /**
   *
   */
  public function getIconsDataArrayCacheId(): string {
    return $this->cacheKey . ':icons_data_array';
  }

  /**
   *
   */
  public function getIconsSearchArrayCacheId(): string {
    return $this->cacheKey . ':icons_search_array';
  }

  /**
   * @phpstan-return string[]
   */
  public function getIconsDataArrayCacheTags(): array {
    return Cache::mergeTags($this->getCacheTags(), [$this->getIconsDataArrayCacheId()]);
  }

  /**
   * @phpstan-return string[]
   */
  public function getIconsSearchArrayCacheTags(): array {
    return Cache::mergeTags($this->getIconsDataArrayCacheTags(), [$this->getIconsSearchArrayCacheId()]);
  }

  /**
   *
   */
  public function clearCachedIconsDataArray(): void {
    if (($tags = $this->getIconsDataArrayCacheTags())) {
      Cache::invalidateTags($tags);
    }
    $this->iconsDataArray = NULL;
  }

  /**
   *
   */
  public function clearCachedIconsSearchArray(): void {
    if (($tags = $this->getIconsSearchArrayCacheTags())) {
      Cache::invalidateTags($tags);
    }
    $this->iconsSearchArray = NULL;
  }

  /**
   */
  public function useCaches(bool $use_caches = FALSE): void {
    $this->useCaches = $use_caches;
    if (!$use_caches) {
      $this->iconsDataArray = NULL;
      $this->iconsSearchArray = NULL;
    }
  }

  /**
   * @phpstan-return null|IconsDataArray
   */
  protected function getCachedIconsDataArray(): ?array {
    if (!isset($this->iconsDataArray) && ($cache = $this->cacheGet($this->getIconsDataArrayCacheId()))) {
      $this->iconsDataArray = (array) $cache->data;
    }
    return $this->iconsDataArray;
  }

  /**
   * @phpstan-return null|IconsSearchArray
   */
  protected function getCachedIconsSearchArray(): ?array {
    if (!isset($this->iconsSearchArray) && ($cache = $this->cacheGet($this->getIconsSearchArrayCacheId()))) {
      $this->iconsSearchArray = (array) $cache->data;
    }
    return $this->iconsSearchArray;
  }

  /**
   * @phpstan-param IconsDataArray $icons_data_array
   */
  protected function setCachedIconsDataArray(array $icons_data_array): void {
    $this->cacheSet(
      $this->getIconsDataArrayCacheId(),
      $icons_data_array,
      $this->getCacheMaxAge(),
      $this->getIconsDataArrayCacheTags()
    );
    $this->iconsDataArray = $icons_data_array;
  }

  /**
   * @phpstan-param IconsSearchArray $icons_search_array
   */
  protected function setCachedIconsSearchArray(array $icons_search_array): void {
    $this->cacheSet(
      $this->getIconsSearchArrayCacheId(),
      $icons_search_array,
      $this->getCacheMaxAge(),
      $this->getIconsSearchArrayCacheTags()
    );
    $this->iconsSearchArray = $icons_search_array;
  }

  /**
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
   * @phpstan-return IconsDataArray
   */
  protected static function parseIconsDataArray(array $array): array {
    $data_array = [];
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
      $icon_names = array_merge([$icon_key], $icon_data['aliases']['names']);
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
