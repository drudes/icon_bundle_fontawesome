<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

namespace Drupal\icon_bundle_fontawesome\Plugin\IconBundle;

use Drupal\icon_bundle_api\IconBundleBase;

/**
 * @IconBundle(
 *  id = "icon_bundle_fontawesome",
 *  label = @Translation("Font Awesome Bundle"),
 *  description = @Translation("Font Awesome icon bundle compatible with Icon Bundle API."),
 *  config_route = "icon_bundle_fontawesome.settings",
 *  icon_picker = "fontawesome_icon_picker",
 *  icon_element = "fontawesome_icon",
 * )
 */
class FontAwesomeBundle extends IconBundleBase
{
}
