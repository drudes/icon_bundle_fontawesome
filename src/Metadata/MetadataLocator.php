<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

//--use Drupal\Component\Utility\NestedArray;
//--use Drupal\Core\Config\ConfigFactoryInterface;
//--use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
//--use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
final class MetadataLocator implements MetadataLocatorInterface {

  /**
   * @var string
   */
  protected $appRoot;

  /**
   * @var array
   * @phpstan-var array<array-key,mixed>
   */
  protected $settings;

  /**
   * @phpstan-param array<array-key,mixed> $settings
   */
  public function __construct(string $app_root, array $settings) {
    $this->appRoot = $app_root;
    $this->settings = $settings;
  }

//--  /**
//--   * @phpstan-param array<array-key,mixed> $overrides
//--   */
//--  public static function create(ContainerInterface $container, array $overrides = []): static {
//--    $app_root = $container->getParameter('app.root');
//--    $config_factory = $container->get('config.factory');
//--
//--    return static::createFromConfig($app_root, $config_factory, $overrides);
//--  }
//--
//--  /**
//--   *
//--   * @phpstan-param array<array-key,mixed> $overrides
//--   */
//--  public static function createFromConfig(string $app_root, ConfigFactoryInterface $config_factory, array $overrides = []): static {
//--    $config_key = 'icon_bundle_fontawesome.settings';
//--    $config = $config_factory->get($config_key);
//--    $settings = $config->getRawData();
//--    if (!empty($overrides)) {
//--      $locator = new self($app_root, NestedArray::mergeDeep($settings, $overrides));
//--      $locator->mergeCacheMaxAges(0); // uncacheable
//--    } else {
//--      $locator = new self($app_root, $settings);
//--    }
//--    $locator->addCacheTags(['config:' . $config_key]);
//--    return $locator;
//--  }

  /**
   * {@inheritdoc}
   */
  public function getLocation(string $file = ''): ?string {
    switch ($this->settings['metadata']['delivery']) {
      case 'cdn':
        return $this->getLocationCdn($file);

      case 'self':
        return $this->getLocationSelf($file);

      case 'auto':
        return $this->getLocationAsset($file);
    }

    return NULL;
  }

  /**
   *
   */
  protected function getLocationCdn(string $file): string {
    // FIXME: path joining method! below is just an idea what I mean.
    return $this->settings['metadata']['cdn']['uri'] . '/' . $file;
  }

  /**
   *
   */
  protected function getLocationSelf(string $file): string {
    $path = $this->settings['metadata']['self']['path'];
    if (0 !== strpos($path, '/')) {
      // Relative $path provided.
      $path = self::joinPaths($this->appRoot, $path);
    }

    return self::joinPaths($path, $file);
  }

  /**
   *
   */
  protected function getLocationAsset(string $file): ?string {
    switch ($this->settings['asset']['delivery']) {
      case 'cdn':
        return $this->getLocationAssetCdn($file);

      case 'self':
        return $this->getLocationAssetSelf($file);

      case 'kit':
        return $this->getLocationAssetKit($file);
    }

    return NULL;
  }

  /**
   *
   */
  protected function getLocationAssetCdn(string $file): string {
    $uri = $this->settings['asset']['cdn']['uri'];

    return self::joinPaths($uri, 'metadata', $file);
  }

  /**
   *
   */
  protected function getLocationAssetSelf(string $file): string {
    $path = $this->settings['asset']['self']['path'];
    if (0 !== strpos($path, '/')) {
      $path = '/' . $path;
    }

    $url = Url::fromUserInput($path);
    $dir = $url->isRouted() ? $url->getInternalPath() : $path;

    return self::joinPaths($this->appRoot, $dir, 'metadata', $file);
  }

  /**
   *
   */
  protected function getLocationAssetKit(string $file): string {
    return '';
  }

  /**
   *
   */
  protected static function joinPaths(string ...$elements): string {
    if (empty($elements)) {
      return '';
    }

    $path = array_shift($elements);

    foreach ($elements as $element) {
      if ('' === $path) {
        $path = $element;
      }
      elseif ('' !== $element) {
        $path = rtrim($path, '/') . '/' . ltrim($element, '/');
      }
    }

    return $path;
  }

}
