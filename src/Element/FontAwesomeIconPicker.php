<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\icon_bundle_fontawesome\Metadata\MetadataProvider;

/**
 * @FormElement("fontawesome_icon_picker")
 *
 * @phpstan-import-type IconsDataArray from \Drupal\icon_bundle_fontawesome\Metadata\MetadataProviderInterface
 *
 * @phpstan-type GetInfoRetval array{
 *  '#input': bool,
 *  '#process': callable[],
 *  '#value_callback': callable,
 * }
 *
 * @phpstan-type GetDefaultValuesElement array{
 *  '#default_value'?: array{
 *    icon?: string,
 *    style?: string,
 *    wrapper?: 'i'|'span',
 *    wrapper_class?: string,
 *    wrapper_style?: string,
 *  },
 * }
 *
 * @phpstan-type GetDefaultValuesRetval array{
 *  icon: string,
 *  style: string,
 *  wrapper: string,
 *  wrapper_class: string,
 *  wrapper_style: string,
 * }
 *
 * @phpstan-type ProcessFontAwesomeIconPickerElement array{
 *  '#parents': string[],
 *  '#default_value'?: array{
 *    icon?: string,
 *    style?: string,
 *    wrapper?: 'i'|'span',
 *    wrapper_class?: string,
 *    wrapper_style?: string,
 *  },
 *  '#prefix'?: string,
 *  '#suffix'?: string,
 *  '#icon'?: array<string,mixed>,
 *  '#style'?: array<string,mixed>,
 *  '#wrapper'?: array<string,mixed>,
 *  '#wrapper_class'?: array<string,mixed>,
 *  '#wrapper_style'?: array<string,mixed>,
 * }
 *
 * @phpstan-type ProcessFontAwesomeIconPickerRetval array{
 *  '#parents': string[],
 *  '#default_value'?: array{
 *    icon?: string,
 *    style?: string,
 *    wrapper?: 'i'|'span',
 *    wrapper_class?: string,
 *    wrapper_style?: string,
 *  },
 *  '#tree': bool,
 *  '#prefix': string,
 *  '#suffix': string,
 *  '#icon'?: array<string,mixed>,
 *  '#style'?: array<string,mixed>,
 *  '#wrapper'?: array<string,mixed>,
 *  '#wrapper_class'?: array<string,mixed>,
 *  '#wrapper_style'?: array<string,mixed>,
 * }
 */
class FontAwesomeIconPicker extends FormElement {

  /**
   * {@inheritdoc}
   *
   * @phpstan-return GetInfoRetval
   */
  public function getInfo(): array {
    $class = static::class;

    return [
      '#input'   => TRUE,
      '#process' => [
              [$class, 'processFontAwesomeIconPicker'],
      ],
      '#value_callback' => [$class, 'valueCallback'],
    ];
  }

  /**
   * @phpstan-param ProcessFontAwesomeIconPickerElement $element
   * @phpstan-return ProcessFontAwesomeIconPickerRetval
   */
  public static function processFontAwesomeIconPicker(array $element, FormStateInterface $form_state) {
    $element_wrapper_id = implode('-', $element['#parents']) . '-fontawesome_icon_spec-wrapper';

    $element['#tree'] = TRUE;
    $element['#prefix'] = ($element['#prefix'] ?? '') . '<div id="' . $element_wrapper_id . '">';
    $element['#suffix'] = '</div>' . ($element['#suffix'] ?? '');

    $defaults = static::getDefaultValues($element);
    $names = static::getNestedElementNames($element);
    $inputs = $form_state->getValue($element['#parents'], []);
    $values = $inputs + $defaults;

    $icons_data_array = static::getIconsDataArray();

    if (empty($icons_data_array)) {
      $icon_placeholder = t('Metadata unavailable, autocompletion will not work');
      $style_empty_option = t('- Metadata unavailable -');
      // If metadata is unavailable, all styles are temporarily enabled.
      $style_options = [
        'solid'   => t('Solid'),
        'regular' => t('Regular'),
        'light'   => t('Light'),
        'duotone' => t('Duotone'),
        'thin'    => t('Thin'),
        'brands'  => t('Brands'),
      ];
      if (!array_key_exists($values['style'], $style_options)) {
        $style_options[$values['style']] = t(ucfirst($values['style']));
      }
    }
    else {
      $icon_placeholder = t('Start typying to select an icon');
      $style_empty_option = $values['icon'] ? t('- Invalid icon -') : t('- Select icon first -');
      $style_options = static::iconStyleOptions($icons_data_array, $values['icon']);
    }

    if (empty($style_options)) {
      $values['style'] = NULL;
      $style_value_changed = TRUE;
    }
    else {
      $style_value_changed = FALSE;
      if (NULL === $values['style'] || !array_key_exists($values['style'], $style_options)) {
        $values['style'] = array_key_first($style_options);
        $style_value_changed = TRUE;
      }
    }

    $element['icon'] = [
      '#type'                    => 'textfield',
      '#name'                    => $names['icon'],
      '#default_value'           => $defaults['icon'],
      '#autocomplete_route_name' => 'icon_bundle_fontawesome.autocomplete.icon_picker.icon',
      '#ajax'                    => [
        'callback'        => [self::class, 'updateFormElement'],
        'event'           => 'change',
        'wrapper'         => $element_wrapper_id,
        'disable-refocus' => TRUE,
      ],
    ] + ($element['#icon'] ?? []) + [
      '#title'       => t('Icon Name'),
      '#placeholder' => $icon_placeholder,
      '#description' => t('Select icon from Font Awesome Icon Bundle'),
    ];

    $element['style'] = [
      '#type'          => 'select',
      '#name'          => $names['style'],
      '#default_value' => $defaults['style'],
      '#ajax'          => [
        'callback'        => [self::class, 'updateFormElement'],
        'event'           => 'change',
        'wrapper'         => $element_wrapper_id,
        'disable-refocus' => TRUE,
      ],
      '#options'      => $style_options,
      '#empty_option' => $style_empty_option,
    ] + ($element['#style'] ?? []) + [
      '#title' => t('Icon Style'),
    ];

    if ($style_value_changed) {
      $element['style']['#value'] = $values['style'];
    }

    if (!empty($style_options) && !empty($icons_data_array)) {
      unset($element['style']['#empty_option']);
    }

    $element['wrapper'] = [
      '#type'    => 'select',
      '#name'    => $names['wrapper'],
      '#options' => [
        'i'    => t('i'),
        'span' => t('span'),
      ],
      '#default_value' => $defaults['wrapper'],
      '#ajax'          => [
        'callback'        => [self::class, 'updateFormElement'],
        'event'           => 'change',
        'wrapper'         => $element_wrapper_id,
        'disable-refocus' => TRUE,
      ],
    ] + ($element['#wrapper'] ?? []) + [
      '#title'       => t('Icon Wrapper'),
      '#description' => t('Choose an HTML element to wrap the icon with.'),
    ];

    $element['wrapper_class'] = [
      '#type'          => 'textfield',
      '#name'          => $names['wrapper_class'],
      '#default_value' => $defaults['wrapper_class'],
      '#ajax'          => [
        'callback'        => [self::class, 'updateFormElement'],
        'event'           => 'change',
        'wrapper'         => $element_wrapper_id,
        'disable-refocus' => TRUE,
      ],
      '#autocomplete_route_name' => 'icon_bundle_fontawesome.autocomplete.icon_picker.wrapper_class',
    ] + ($element['#wrapper_class'] ?? []) + [
      '#title'       => t('Icon CSS Classes'),
      '#description' => t('A space separated list of CSS classes to be set to icon wrapper.'),
      '#placeholder' => t('Start typying to autocomplete.'),
    ];

    $element['wrapper_style'] = [
      '#type' => 'textfield',
      '#name' => $names['wrapper_style'],
      '#default_value' => $defaults['wrapper_style'],
      '#ajax'          => [
        'callback'        => [self::class, 'updateFormElement'],
        'event'           => 'change',
        'wrapper'         => $element_wrapper_id,
        'disable-refocus' => TRUE,
      ],
      '#autocomplete_route_name' => 'icon_bundle_fontawesome.autocomplete.icon_picker.wrapper_style',
    ] + ($element['#wrapper_style'] ?? []) + [
      '#title'       => t('Icon CSS Style'),
      '#description' => t('A semicolon separated list of CSS styles to be assigned to icon wrapper.'),
      '#placeholder' => t('Start typying to autocomplete.'),
    ];

    $element['preview'] = [
      '#type'  => 'details',
      '#open'  => TRUE,
      '#title' => t('Icon Preview'),
      'icon'   => [
        '#type'          => 'fontawesome_icon',
        '#icon'          => $values['icon'],
        '#style'         => $values['style'],
        '#wrapper'       => $values['wrapper'],
        '#wrapper_class' => $values['wrapper_class'],
        '#wrapper_style' => $values['wrapper_style'],
      ],
    ];

    return $element;
  }

  /**
   * @phpstan-template InputType of mixed
   * @phpstan-param GetDefaultValuesElement $element
   * @phpstan-param InputType $input
   * @phpstan-return GetDefaultValuesRetval|InputType
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (FALSE === $input) {
      return static::getDefaultValues($element);
    }

    return $input;
  }

  /**
   * @phpstan-param array<string,mixed> $form
   * @phpstan-return mixed
   */
  public static function updateFormElement(array &$form, FormStateInterface $form_state) {
    // $triggering_element is a first-level sub-element of the $element ($element['icon'] in fact)
    $triggering_element = $form_state->getTriggeringElement();

    // Acces the $element, the one returned from $this->formElement().
    $element_parents = array_slice($triggering_element['#array_parents'] ?? [], 0, -1);
    $element = NestedArray::getValue($form, $element_parents);

    return $element;
  }

  /**
   * @phpstan-param array{'#parents': string[]} $element
   * @phpstan-return array<string, string>
   */
  protected static function getNestedElementNames(array $element): array {
    $names = [];
    foreach (static::getFallbackValues() as $key => $value) {
      $names[$key] = static::nestedElementName($element['#parents'], $key);
    }

    return $names;
  }

  /**
   * @phpstan-param GetDefaultValuesElement $element
   * @phpstan-return GetDefaultValuesRetval
   */
  protected static function getDefaultValues(array $element): array {
    $fallback_values = self::getFallbackValues();
    $default_values = $element['#default_value'] ?? [];

    return array_intersect_key($default_values + $fallback_values, $fallback_values);
  }

  /**
   * @phpstan-return array{
   *  icon: '',
   *  style: '',
   *  wrapper: 'i',
   *  wrapper_class: '',
   *  wrapper_style: '',
   * }
   */
  protected static function getFallbackValues(): array {
    return [
      'icon'          => '',
      'style'         => '',
      'wrapper'       => 'i',
      'wrapper_class' => '',
      'wrapper_style' => '',
    ];
  }

  /**
   * @phpstan-return IconsDataArray
   */
  protected static function getIconsDataArray(): array {
    $metadata_provider = \Drupal::classResolver(MetadataProvider::class);

    return $metadata_provider->getIconsDataArray();
  }

  /**
   * @phpstan-param IconsDataArray $icons_data_array
   * @phpstan-param mixed $icon_key
   * @phpstan-return array<string, \Drupal\Core\StringTranslation\TranslatableMarkup>
   */
  protected static function iconStyleOptions(array $icons_data_array, $icon_key): array {
    $options = [];
    if (is_string($icon_key) && '' !== $icon_key) {
      if (NULL !== ($icon_data = $icons_data_array[$icon_key] ?? NULL)) {
        foreach ($icon_data['styles'] as $style) {
          $style_label = ucfirst($style);
          $options[$style] = t($style_label);
        }
      }
    }

    return $options;
  }

  /**
   * @phpstan-param string[] $parents
   */
  protected static function nestedElementName(array $parents, string $child): string {
    if (empty($parents)) {
      return $child;
    }

    $parents[] = $child;

    $root = array_shift($parents);

    return $root . '[' . implode('][', $parents) . ']';
  }

}
