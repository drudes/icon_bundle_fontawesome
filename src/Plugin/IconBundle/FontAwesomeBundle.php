<?php

declare(strict_types=1);

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
class FontAwesomeBundle extends IconBundleBase {
}
