<?php declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\icon_bundle_fontawesome\Metadata\MetadataProvider;
use Drupal\icon_bundle_fontawesome\Metadata\MetadataProviderInterface;
use Drupal\icon_bundle_fontawesome\Utility\IconHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class IconPickerAutocompleteController extends ControllerBase implements ContainerInjectionInterface
{
    /**
     * @var string
     */
    protected $metadataProvider;

    public function __construct(MetadataProviderInterface $metadata_provider)
    {
        $this->metadataProvider = $metadata_provider;
    }

    public static function create(ContainerInterface $container): static
    {
        $metadata_provider = $container->get('class_resolver')->getInstanceFromDefinition(MetadataProvider::class);

        return new static($metadata_provider);
    }

    public function handleIcon(Request $request): JsonResponse
    {
        if ('' === ($typed_word = AutocompleteHelper::getLastTag($request))) {
            return new JsonResponse([]);
        }

        $response = [];

        // Get the icon array
        $icon_data_array = $this->metadataProvider->getIconsDataArray();
        $icon_search_array = $this->metadataProvider->getIconsSearchArray($icon_data_array);
        $icon_keys = $icon_search_array[$typed_word] ?? [];

        foreach ($icon_keys as $icon_key) {
            if (null !== ($icon_data = $icon_data_array[$icon_key] ?? null)) {
                $label_styles = [];
                foreach ($icon_data['styles'] as $style) {
                    $label_styles[] = t('<i class=":prefix fa-:icon fa-fw"></i>', [
                        ':prefix' => IconHelper::cssStyleClass($style),
                        ':icon'   => $icon_key,
                    ]);
                }
                $response[] = [
                    'value' => $icon_key,
                    'label' => $icon_key.' '.implode(' ', $label_styles),
                ];
            }
        }

        return new JsonResponse($response);
    }

    public function handleWrapperClass(Request $request): JsonResponse
    {
        // https://fontawesome.com/docs/web/style/style-cheatsheet
        $classes = [
            // General
            'fa-inverse' => 'general',
            // Sizing Icons
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
            // Fixed-Width Icons
            'fa-fw' => 'fixed',
            // Icons in a List
            'fa-ul' => 'lists',
            'fa-li' => 'lists',
            // Rotating Icons
            'fa-rotate-90' => 'rotating',
            'fa-rotate-180' => 'rotating',
            'fa-rotate-270' => 'rotating',
            'fa-flip-horizontal' => 'rotating',
            'fa-flip-vertical' => 'rotating',
            'fa-flip-both' => 'rotating',
            // 'fa-rotate-by',
            // Animating icons
            'fa-spin' => 'animating',
            'fa-spin-pulse' => 'animating',
            'fa-spin-reverse' => 'animating',
            'fa-beat' => 'animating',
            'fa-fade' => 'animating',
            'fa-flip' => 'animating',
            // Bordered Icons
            'fa-border' => 'bordered',
            // Pulled Icons
            'fa-pull-left' => 'pulled',
            'fa-pull-right' => 'pulled',
            // Stacking Icons
            'fa-stack-1x' => 'stacking',
            'fa-stack-2x' => 'stacking',
            // Duotone Icons
            'fa-swap-opacity' => 'duotone',
            // Accessibility
            'fa-sr-only' => 'accessibility',
            'fa-sr-only-focusable' => 'accessibility',
        ];

        if (empty($input = AutocompleteHelper::getInput($request))) {
            return new JSonReponse([]);
        }

        $words = array_map('trim', explode(' ', $input));

        $last = array_pop($words);

        $already_selected_classes = array_intersect_key($classes, array_combine($words, $words));
        $already_defined_stylings = array_unique(array_values($already_selected_classes));
        $available_classes = array_diff($classes, $already_defined_stylings);

        $response = [];

        foreach ($available_classes as $class => $styling) {
            $matched = mb_strstr($class, $last);
            if (false === $matched || 0 === mb_strlen($matched)) {
                $matched = mb_strstr($styling, $last);
            }
            if (false !== $matched && mb_strlen($matched) > 0) {
                $value = implode(' ', array_merge($words, [$class]));
                $response[] = [
                    'value' => $value,
                    'label' => t($value),
                ];
            }
        }
        return new JsonResponse($response);
    }
}
