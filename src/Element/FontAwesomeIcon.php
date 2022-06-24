<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * @RenderElement("fontawesome_icon")
 */
class FontAwesomeIcon extends RenderElement {

  /**
   * {@inheritdoc}
   *
   * @phpstan-return array{'#theme': string}
   */
  public function getInfo(): array {
    return [
      '#theme' => 'fontawesome_icon',
    ];
  }

}
