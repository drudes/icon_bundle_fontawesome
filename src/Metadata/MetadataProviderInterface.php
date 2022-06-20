<?php declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Metadata;

interface MetadataProviderInterface
{
    public function getIconsDataArray(string $file = null): array;

    public function getIconsSearchArray(array $icons_data_array): array;
}
