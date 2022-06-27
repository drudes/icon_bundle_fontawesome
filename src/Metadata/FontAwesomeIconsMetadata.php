<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

/**
 * @phpstan-import-type IconsDataArray from FontAwesomeIconsMetadataInterface
 */
final class FontAwesomeIconsMetadata implements FontAwesomeIconsMetadataInterface {
  /**
   * @var string
   */
  private $id;

  /**
   * @var array
   * @phpstan-var IconsDataArray
   */
  private $iconsDataArray;

  /**
   * @phpstan-param IconsDataArray $icons_data_array
   */
  public function __construct(string $id, array $icons_data_array) {
    $this->id = $id;
    $this->iconsDataArray = $icons_data_array;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconsDataArray(): array {
    return $this->iconsDataArray;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return [];
  }
}
