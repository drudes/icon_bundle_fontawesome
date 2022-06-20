<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

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
}
