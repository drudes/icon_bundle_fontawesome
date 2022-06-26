<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Encapsulates Font Awesome Icons metadata.
 *
 * @phpstan-type IconData array{
 *  name: string,
 *  type: string,
 *  label: string,
 *  styles: string[],
 *  aliases: array{
 *    names: string[],
 *  }
 * }
 * @phpstan-type IconsDataArray array<array-key,IconData>
 */
interface FontAwesomeIconsMetadataInterface extends CacheableDependencyInterface {
  /**
   * Returns the id.
   */
  public function id(): string;
  /**
   * @phpstan-return IconsDataArray
   */
  public function getIconsDataArray(): array;
}
