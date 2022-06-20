<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

namespace Drupal\icon_bundle_fontawesome\Controller;

use Drupal\Component\Utility\Tags;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteHelper
{
    public static function filterByLastTag(Request $request, array $strings): array
    {
        if ('' === ($typed_tag = self::getLastTag($request))) {
            return [];
        }
        $response = [];

        foreach ($strings as $string) {
            $matched = mb_strstr($string, $typed_tag);
            if (false !== $matched && mb_strlen($matched) > 0) {
                $response[] = [
                    'value' => $string,
                    'label' => t($string),
                ];
            }
        }

        return $response;
    }

    public static function getInput(Request $request): string
    {
        if (is_string($input = $request->query->get('q', ''))) {
            return $input;
        }

        return '';
    }

    public static function getLastTag(Request $request): string
    {
        $input = self::getInput($request);

        // Get the value of q from the query string
        if (empty($tags = Tags::explode($input))) {
            return '';
        }

        return mb_strtolower(array_pop($tags));
    }
}
