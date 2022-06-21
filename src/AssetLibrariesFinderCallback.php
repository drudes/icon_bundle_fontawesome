<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 *
 */
class AssetLibrariesFinderCallback {
  protected static $requiredFiles = [
    'js/all.js',
    'js/all.min.js',
    'js/fontawesome.js',
    'css/all.css',
    'css/all.min.css',
    'css/fontawesome.css',
    'css/fontawesome.min.css',
    'metadata/icons.yml',
    'webfonts/',
  ];

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   *
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config;
  }

  /**
   *
   */
  public function __invoke(string $path): bool {
    return TRUE;
  }

}
