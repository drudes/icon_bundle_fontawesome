<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

namespace Drupal\icon_bundle_fontawesome\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * @RenderElement("fontawesome_icon")
 */
class FontAwesomeIcon extends RenderElement
{
    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        return [
            '#theme' => 'fontawesome_icon',
        ];
    }
}
