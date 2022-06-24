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
 *
 * @phpstan-type IconsDataArray array<string,IconDataEntry>
 * @phpstan-type IconsSearchArray array<string,string[]>
 */
interface MetadataProviderInterface {

  /**
   * @phpstan-return IconsDataArray
   */
  public function getIconsDataArray(string $file = NULL): array;

  /**
   * @phpstan-param IconsDataArray $icons_data_array
   * @phpstan-return IconsSearchArray
   */
  public function getIconsSearchArray(array $icons_data_array): array;

}
