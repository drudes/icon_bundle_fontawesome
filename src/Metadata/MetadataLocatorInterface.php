<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

namespace Drupal\icon_bundle_fontawesome\Metadata;

interface MetadataLocatorInterface
{
    /**
     * Returns a path to $file withing metadata path.
     *
     * The metadata path is determined by config settings such as the asset
     * delivery method (``self``, ``cdn``, ``kit``), asset path/uri and few
     * others. There is no way to reliably determine metadata location in case of
     * ``kit``, as we have no version information nor base location for assets.
     *
     * @return Full path or url of the $file within metadata or ``null`` if the
     *              path can't be determined
     */
    public function getLocation(string $file = ''): ?string;
}
