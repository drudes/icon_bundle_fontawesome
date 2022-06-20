<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

namespace Drupal\icon_bundle_fontawesome\Metadata;

interface MetadataProviderInterface
{
    public function getIconsDataArray(string $file = null): array;

    public function getIconsSearchArray(array $icons_data_array): array;
}
