<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Template\Attribute;
use Drupal\icon_bundle_fontawesome\Utility\IconHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
final class Hooks implements HooksInterface, ContainerInjectionInterface {
  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   *
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container): static {
    return new self($container->get('config.factory'));
  }

  /**
   * Implements the hook_page_attachments().
   *
   * @phpstan-param array<array-key,mixed> $page
   */
  public function pageAttachments(array &$page): void {
    $config = $this->getConfig();
    $delivery = $config->get('asset.delivery');
    if ('self' === $delivery || 'cdn' === $delivery) {
      $this->pageAttachmentsOld($page, $delivery);
    }
    elseif ('kit' === $delivery) {
      $this->pageAttachmentsKit($page);
    }
  }

  /**
   * Implements the hook_library_info_alter().
   *
   * @phpstan-param array<array-key,mixed> $libraries
   */
  public function libraryInfoAlter(array &$libraries, string $extension): void {
    if ('icon_bundle_fontawesome' === $extension) {
      [$patterns, $replacements] = $this->makeSubstArrays($this->getDotkeyedSettings());
      self::substitute($libraries, $patterns, $replacements);
    }
  }

  /**
   * Implements the hook_theme().
   *
   * @param mixed $existing
   * @param mixed $type
   * @param mixed $theme
   * @param mixed $path
   *
   * @phpstan-return array<array-key,mixed>
   */
  public function theme($existing, $type, $theme, $path): array {
    return [
      'fontawesome_icon' => [
        'render element' => 'element',
      ],
    ];
  }

  /**
   * Implements the hook_preprocess_HOOK().
   *
   * @phpstan-param array<array-key,mixed> $variables
   */
  public function preprocessFontAwesomeIcon(array &$variables): void {
    $icon = $variables['element']['#icon'] ?? '';
    $style = $variables['element']['#style'] ?? '';
    $variables['wrapper'] = $variables['element']['#wrapper'];
    $variables['attributes'] = new Attribute([]);

    if ('' !== $icon && '' !== $style) {
      $icon_class = 'fa-' . $icon;
      $style_class = IconHelper::cssStyleClass($style);
      $variables['attributes']->addClass($style_class, $icon_class);

      if (NULL !== ($wrapper_class = $variables['element']['#wrapper_class'] ?? NULL)) {
        $variables['attributes']->addClass($wrapper_class);
      }

      if (NULL !== ($wrapper_style = $variables['element']['#wrapper_style'] ?? NULL)) {
        $variables['attributes']['style'] = $wrapper_style;
      }
    }
  }

  /**
   *
   */
  protected function getConfigId(): string {
    return 'icon_bundle_fontawesome.settings';
  }

  /**
   *
   */
  protected function getConfig(): ImmutableConfig {
    return $this->configFactory->get($this->getConfigId());
  }

  /**
   * @phpstan-return array<string,mixed>
   */
  protected function getStyles(): array {
    $config = $this->getConfig();

    return [
      'solid'   => $config->get('asset.use.solid'),
      'regular' => $config->get('asset.use.regular'),
      'light'   => $config->get('asset.use.light'),
      'brands'  => $config->get('asset.use.brands'),
    ];
  }

  /**
   * @phpstan-param array<array-key,mixed> $styles
   */
  protected function worthUsingAll(array $styles): bool {
    // FIXME: consider different behavior for free/pro ("all.css" may contain extra styles in pro version)
    return (bool) array_product($styles);
  }

  /**
   * @phpstan-param array<array-key,mixed> $page
   */
  protected function pageAttachmentsOld(array &$page, string $delivery): void {
    $method = $this->getConfig()->get('method');

    $lib_rel_prefix = 'icon_bundle_fontawesome.' . $delivery;
    $lib_abs_prefix = 'icon_bundle_fontawesome/' . $lib_rel_prefix;

    $styles = $this->getStyles();
    if ($this->worthUsingAll($styles)) {
      $page['#attached']['library'][] = implode('.', [$lib_abs_prefix, $method, 'all']);
    }
    else {
      foreach ($styles as $style => $enabled) {
        if ($enabled) {
          $page['#attached']['library'][] = implode('.', [$lib_abs_prefix, $method, $style]);
        }
      }
    }
  }

  /**
   * @phpstan-param array<array-key,mixed> $page
   */
  protected function pageAttachmentsKit(array &$page): void {
    $page['#attached']['library'][] = 'icon_bundle_fontawesome/icon_bundle_fontawesome.kit';
  }

  /**
   * Recursively substitute variables in string keys and values of &$array.
   *
   * @phpstan-template StringsArrayType of string[]
   * @phpstan-param array<array-key,mixed> $array
   * @phpstan-param StringsArrayType $patterns
   * @phpstan-param StringsArrayType $replacements
   */
  protected static function substitute(array &$array, array $patterns, array $replacements, int $depth = 0): void {
    if ($depth > 64) {
      // Prevent endless recursion, yaml's are never so deep...
      return;
    }

    // Handle strings first (keys and leaf values)
    foreach ($array as $key => &$item) {
      if (is_string($item)) {
        $item = preg_replace($patterns, $replacements, $item);
      }

      if (is_string($key)) {
        $new_key = preg_replace($patterns, $replacements, $key);
        if ($new_key !== $key) {
          $array[$new_key] = $item;
          unset($array[$key]);
        }
      }
    }

    // Then make a recursion.
    foreach ($array as $key => &$item) {
      if (is_array($item)) {
        self::substitute($item, $patterns, $replacements, $depth + 1);
      }
    }
  }

  /**
   * @phpstan-param array<array-key,mixed> $flat_array
   * @phpstan-return array{0:string[],1:string[]}
   */
  protected static function makeSubstArrays(array $flat_array): array {
    $subst_arrays = [[], []];
    foreach ($flat_array as $key => $value) {
      if (is_string($key) && '_' !== substr($key, 0, 1) && is_string($value)) {
        $pattern = preg_quote($key, '/');
        $replacement = str_replace(['\\', '$'], ['\\\\', '\\$'], $value);

        $subst_arrays[0][] = '/(?:\\$' . $pattern . '\\b)|(?:\\${' . $pattern . '})/';
        $subst_arrays[1][] = $replacement;
      }
    }

    return $subst_arrays;
  }

  /**
   * @phpstan-return array<string,mixed>
   */
  protected function getDotkeyedSettings(): array {
    return self::dotkeyed($this->getConfig()->getRawData());
  }

  /**
   * @phpstan-param array<array-key,mixed> $array
   * @phpstan-return array<string,mixed>
   */
  protected static function dotkeyed(array $array, string $parents = ''): array {
    $dotkeyed = [];
    foreach ($array as $key => $item) {
      $path = '' === $parents ? (string) $key : ($parents . '.' . (string) $key);
      $dotkeyed[$path] = $item;
      if (is_array($item)) {
        $dotkeyed = array_merge($dotkeyed, self::dotkeyed($item, $path));
      }
    }

    return $dotkeyed;
  }

}
