<?php declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a controller for autocomplete form elements.
 */
class SettingsFormAutocompleteController extends ControllerBase
{
    public function handleAssetCdnUri(string $version, Request $request): JsonResponse
    {
        $well_known_uris = [
            "https://use.fontawesome.com/releases/v{$version}",
            "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/{$version}",
            "https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@{$version}",
        ];

        return new JsonResponse(AutocompleteHelper::filterByLastTag($request, $well_known_uris));
    }

    public function handleMetadataCdnUri(string $version, Request $request): JsonResponse
    {
        $well_known_uris = [
            "https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@{$version}/metadata",
        ];

        return new JsonResponse(AutocompleteHelper::filterByLastTag($request, $well_known_uris));
    }

    public function handleMetadataSelfPath(Request $request): JsonResponse
    {
        return new JsonResponse([]);
    }
}
