<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

namespace Drupal\icon_bundle_fontawesome\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\icon_bundle_api\Element\ElementHelper;
use Drupal\icon_bundle_fontawesome\Metadata\MetadataProvider;

/**
 * @FormElement("fontawesome_icon_picker")
 */
class FontAwesomeIconPicker extends FormElement
{
    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        $class = static::class;

        return [
            '#input'   => true,
            '#process' => [
                [$class, 'processFontAwesomeIconPicker'],
            ],
            '#value_callback' => [$class, 'valueCallback'],
        ];
    }

    public static function processFontAwesomeIconPicker(&$element, FormStateInterface $form_state, &$form)
    {
        $element_wrapper_id = implode('-', $element['#parents']).'-fontawesome_icon_spec-wrapper';

        $element['#tree'] = true;
        $element['#prefix'] = ($element['#prefix'] ?? '').'<div id="'.$element_wrapper_id.'">';
        $element['#suffix'] = '</div>'.($element['#suffix'] ?? '');

        $defaults = static::getDefaultValues($element);
        $names = static::getNestedElementNames($element);
        $inputs = $form_state->getValue($element['#parents'], []);
        $values = $inputs + $defaults;

        $icons_data_array = static::getIconsDataArray();

        if (empty($icons_data_array)) {
            $icon_placeholder = t('Metadata unavailable, autocompletion will not work');
            $style_empty_option = t('- Metadata unavailable -');
            // If metadata is unavailable, all styles are temporarily enabled
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
        } else {
            $icon_placeholder = t('Start typying to select an icon');
            $style_empty_option = $values['icon'] ? t('- Invalid icon -') : t('- Select icon first -');
            $style_options = static::iconStyleOptions($icons_data_array, $values['icon']);
        }

        if (empty($style_options)) {
            $values['style'] = null;
            $style_value_changed = true;
        } else {
            $style_value_changed = false;
            if (null === $values['style'] || !array_key_exists($values['style'], $style_options)) {
                $values['style'] = array_key_first($style_options);
                $style_value_changed = true;
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
                'disable-refocus' => true,
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
                'disable-refocus' => true,
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
                'disable-refocus' => true,
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
                'disable-refocus' => true,
            ],
        ] + ($element['#wrapper_class'] ?? []) + [
            '#title'       => t('Icon Wrapper Classes'),
            '#description' => t('A space separated list of CSS classes.'),
        ];

        $element['preview'] = [
            '#type'  => 'details',
            '#open'  => true,
            '#title' => t('Icon Preview'),
            'icon'   => [
                '#type'          => 'fontawesome_icon',
                '#icon'          => $values['icon'],
                '#style'         => $values['style'],
                '#wrapper'       => $values['wrapper'],
                '#wrapper_class' => $values['wrapper_class'],
            ],
        ];

        return $element;
    }

    public static function valueCallback(&$element, $input, FormStateInterface $form_state)
    {
        if (false === $input) {
            return static::getDefaultValues($element);
        }

        return $input;
    }

    public static function updateFormElement(array &$form, FormStateInterface $form_state): array
    {
        // $triggering_element is a first-level sub-element of the $element ($element['icon'] in fact)
        $triggering_element = $form_state->getTriggeringElement();

        // acces the $element, the one returned from $this->formElement().
        $element_parents = array_slice($triggering_element['#array_parents'] ?? [], 0, -1);
        $element = NestedArray::getValue($form, $element_parents);

        return $element;
    }

    protected static function getNestedElementNames(array $element): array
    {
        $names = [];
        foreach (static::getFallbackValues() as $key => $value) {
            $names[$key] = ElementHelper::nestedElementName($element['#parents'], $key);
        }

        return $names;
    }

    protected static function getDefaultValues(array $element): array
    {
        $fallback_values = self::getFallbackValues();
        $default_values = $element['#default_value'] ?? [];

        return array_intersect_key($default_values + $fallback_values, $fallback_values);
    }

    protected static function getFallbackValues(): array
    {
        return [
            'icon'          => '',
            'style'         => '',
            'wrapper'       => 'i',
            'wrapper_class' => '',
        ];
    }

    protected static function getIconsDataArray(): array
    {
        $metadata_provider = \Drupal::classResolver(MetadataProvider::class);

        return $metadata_provider->getIconsDataArray();
    }

    protected static function iconStyleOptions(array $icons_data_array, $icon_key): array
    {
        $options = [];
        if (is_string($icon_key) && '' !== $icon_key) {
            if (null !== ($icon_data = $icons_data_array[$icon_key] ?? null)) {
                foreach ($icon_data['styles'] as $style) {
                    $style_label = ucfirst($style);
                    $options[$style] = t($style_label);
                }
            }
        }

        return $options;
    }
}
