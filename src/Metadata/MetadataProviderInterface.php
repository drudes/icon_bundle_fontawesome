<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

/**
 * @phpstan-type IconDataEntry array{
 *  name: string,
 *  type: string,
 *  label: string,
 *  styles: string[],
 *  aliases: array{
 *    names: string[],
 *  }
 * }
 *
 * @phpstan-type IconsDataArray array<array-key,IconDataEntry>
 * @phpstan-type IconsSearchArray array<array-key,string[]>
 */
interface MetadataProviderInterface {

  /**
   * @phpstan-return IconsDataArray
   */
  public function getIconsDataArray(): array;

  /**
   * @phpstan-return IconsSearchArray
   */
  public function getIconsSearchArray(): array;

}
