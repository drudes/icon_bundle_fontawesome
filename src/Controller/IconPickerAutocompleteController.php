<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\icon_bundle_fontawesome\Metadata\MetadataProvider;
use Drupal\icon_bundle_fontawesome\Metadata\MetadataProviderInterface;
use Drupal\icon_bundle_fontawesome\Utility\IconHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
final class IconPickerAutocompleteController extends ControllerBase implements ContainerInjectionInterface {
  /**
   * @var \Drupal\icon_bundle_fontawesome\Metadata\MetadataProviderInterface
   */
  protected $metadataProvider;

  /**
   *
   */
  public function __construct(MetadataProviderInterface $metadata_provider) {
    $this->metadataProvider = $metadata_provider;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container): static {
    $metadata_provider = $container->get('class_resolver')->getInstanceFromDefinition(MetadataProvider::class);

    return new self($metadata_provider);
  }

  /**
   *
   */
  public function handleIcon(Request $request): JsonResponse {
    if ('' === ($typed_word = AutocompleteHelper::getLastWord($request))) {
      return new JsonResponse([]);
    }

    $response = [];

    // Get the icon array.
    $icons_data_array = $this->metadataProvider->getIconsDataArray();
    $icons_search_array = $this->metadataProvider->getIconsSearchArray();
    $icons_keys = $icons_search_array[$typed_word] ?? [];

    foreach ($icons_keys as $icon_key) {
      if (NULL !== ($icon_data = $icons_data_array[$icon_key] ?? NULL)) {
        $label_styles = [];
        foreach ($icon_data['styles'] as $style) {
          $label_styles[] = t('<i class=":prefix fa-:icon fa-fw"></i>', [
            ':prefix' => IconHelper::cssStyleClass($style),
            ':icon'   => $icon_key,
          ]);
        }
        $response[] = [
          'value' => $icon_key,
          'label' => $icon_key . ' ' . implode(' ', $label_styles),
        ];
      }
    }

    return new JsonResponse($response);
  }

  /**
   *
   */
  public function handleWrapperClass(Request $request): JsonResponse {
    // https://fontawesome.com/docs/web/style/style-cheatsheet
    $classes_stylings = [
          // General.
      'fa-inverse' => 'general',
          // Sizing Icons.
      'fa-1x' => 'sizing',
      'fa-2x' => 'sizing',
      'fa-3x' => 'sizing',
      'fa-4x' => 'sizing',
      'fa-5x' => 'sizing',
      'fa-6x' => 'sizing',
      'fa-7x' => 'sizing',
      'fa-8x' => 'sizing',
      'fa-9x' => 'sizing',
      'fa-10x' => 'sizing',
      'fa-2xs' => 'sizing',
      'fa-xs' => 'sizing',
      'fa-sm' => 'sizing',
      'fa-lg' => 'sizing',
      'fa-xl' => 'sizing',
      'fa-2xl' => 'sizing',
          // Fixed-Width Icons.
      'fa-fw' => 'fixed',
          // Icons in a List (used on a <ul>, <ol> or <li> elements, not on icons).
      // 'fa-ul' => 'lists',
      // 'fa-li' => 'lists',
          // Rotating Icons.
      'fa-rotate-90' => 'rotating',
      'fa-rotate-180' => 'rotating',
      'fa-rotate-270' => 'rotating',
      'fa-flip-horizontal' => 'rotating',
      'fa-flip-vertical' => 'rotating',
      'fa-flip-both' => 'rotating',
      'fa-rotate-by' => 'rotating',
          // Animating icons.
      'fa-spin' => 'animating',
      'fa-spin-pulse' => 'animating',
      'fa-spin-reverse' => 'animating-spin-direction',
      'fa-beat' => 'animating',
      'fa-fade' => 'animating',
      'fa-flip' => 'animating',
          // Bordered Icons.
      'fa-border' => 'bordered',
          // Pulled Icons.
      'fa-pull-left' => 'pulled',
      'fa-pull-right' => 'pulled',
          // Stacking Icons.
      'fa-stack-1x' => 'stacking',
      'fa-stack-2x' => 'stacking',
          // Duotone Icons.
      'fa-swap-opacity' => 'duotone',
          // Accessibility.
      'fa-sr-only' => 'accessibility',
      'fa-sr-only-focusable' => 'accessibility',
    ];

    if (!($words = AutocompleteHelper::getWords($request))) {
      return new JsonResponse([]);
    }

    $last_word = array_pop($words);

    $already_selected_classes = array_intersect_key($classes_stylings, array_combine($words, $words));
    $already_defined_stylings = array_unique(array_values($already_selected_classes));
    $available_classes = array_diff($classes_stylings, $already_defined_stylings);

    $response = [];

    foreach ($available_classes as $class => $styling) {
      $matched = mb_strstr($class, $last_word);
      if (FALSE === $matched || 0 === mb_strlen($matched)) {
        $matched = mb_strstr($styling, $last_word);
      }
      if (FALSE !== $matched && mb_strlen($matched) > 0) {
        $value = implode(' ', array_merge($words, [$class]));
        $response[] = [
          'value' => $value,
          'label' => t($value),
        ];
      }
    }
    return new JsonResponse($response);
  }

  /**
   *
   */
  public function handleWrapperStyle(Request $request): JsonResponse {
    // https://fontawesome.com/docs/web/style/style-cheatsheet
    $styles_defaults = [
      // General.
      '--fa-display' => 'inline-block',
      '--fa-inverse' => '#fff',
      // Icons in a list.
      '--fa-li-margin' => '0',
      '--fa-li-width' => '0',
      // Rotation.
      '--fa-rotate-angle' => '0',
      // Animating.
      '--fa-animation-delay' => '0s',
      '--fa-animation-direction' => 'normal',
      '--fa-animation-duration' => 'unset',
      '--fa-animation-iteration-count' => 'unset',
      '--fa-animation-timing' => 'unset',
      '--fa-beat-scale' => '1.5',
      '--fa-fade-opacity' => '0',
      '--fa-beat-fade-opacity' => '0',
      '--fa-beat-fade-scale' => '1.5',
      '--fa-flip-x' => '0.5',
      '--fa-flip-y' => '0.5',
      '--fa-flip-z' => '0.5',
      '--fa-flip-angle' => '',
      // Bordered Icons.
      '--fa-border-color' => 'black',
      '--fa-border-padding' => '0',
      '--fa-border-radius' => '0',
      '--fa-border-style' => 'solid',
      '--fa-border-width' => '1px',
      // Pulled Icons.
      '--fa-pull-margin' => '.3em',
      // Stacking icons.
      '--fa-stack-z-index' => 'auto',
      // Duotone Icons.
      '--fa-primary-color' => '#a3adba',
      '--fa-primary-opacity' => '1.0',
      '--fa-secondary-color' => '#183153',
      '--fa-secondary-opacity' => '1.0',
      // Pseudo-elements.
      '--fa-font-solid' => 'normal 900 1em/1 "Font Awesome 6 Solid"',
      '--fa-font-regular' => 'normal 400 1em/1 "Font Awesome 6 Regular"',
      '--fa-font-light' => 'normal 300 1em/1 "Font Awesome 6 Light"',
      '--fa-font-thin' => 'normal 100 1em/1 "Font Awesome 6 Thin"',
      '--fa-font-duotone' => 'normal 900 1em/1 "Font Awesome 6 Duotone"',
      '--fa-font-brands' => 'normal 400 1em/1 "Font Awesome 6 Brands"',
    ];

    if (!($input = AutocompleteHelper::getInput($request))) {
      return new JsonResponse([]);
    }

    $lines = preg_split('/\s*;\s*/', mb_strtolower($input));
    $last_line = array_pop($lines);
    preg_match('/^\s*(?P<style>[^:]+)(?:\s*:\s*(?P<value>.+)?\s*)?$/', $last_line, $last, PREG_UNMATCHED_AS_NULL);

    $styles_values = [];
    foreach ($lines as $line) {
      if (preg_match('/^\s*(?P<style>[^:]+)\s*:\s*(?P<value>.+)\s*$/', $line, $matches, PREG_UNMATCHED_AS_NULL)) {
        $key = $matches['style'];
        $value = $matches['value'];
        if ('' !== $key) {
          $styles_values[$key] = $value;
        }
      }
    }

    $available_styles = array_diff_key($styles_defaults, $styles_values);
    $response = [];

    foreach ($available_styles as $style => $default) {
      $matched = mb_strstr($style, ($last['style'] ?? $last_line));
      if (FALSE !== $matched && mb_strlen($matched) > 0) {
        $value = implode('; ', array_merge($lines, [$style . ': ' . ($last['value'] ?? $default)]));
        $response[] = [
          'value' => $value,
          'label' => t($value),
        ];
      }
    }
    return new JsonResponse($response);
  }

}
