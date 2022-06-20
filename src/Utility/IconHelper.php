<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

namespace Drupal\icon_bundle_fontawesome\Utility;

/**
 * Provides functions to determine the icon style.
 */
class IconHelper
{
    /**
     * @var array
     */
    public static $cssStyleClassAliases = [
        'solid'   => 'fas',
        'regular' => 'far',
        'light'   => 'fal',
        'thin'    => 'fat',
        'duotone' => 'fad',
        'brands'  => 'fab',
    ];

    public static function cssStyleClass(string $style, string $default = 'fas'): string
    {
        if ('' === $style) {
            return $default;
        }

        return self::$cssStyleClassAliases[$style] ?? "fa-{$style}";
    }
}
