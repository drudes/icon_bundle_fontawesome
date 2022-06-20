<?php declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome;

interface HooksInterface
{
    /**
     * Implements the hook_page_attachments().
     */
    public function pageAttachments(array &$page): void;

    /**
     * Implements the hook_library_info_alter().
     */
    public function libraryInfoAlter(array &$libraries, string $extension): void;

    /**
     * Implements the hook_theme().
     *
     * @param mixed $existing
     * @param mixed $type
     * @param mixed $theme
     * @param mixed $path
     */
    public function theme($existing, $type, $theme, $path): array;

    /**
     * Implements the hook_preprocess_HOOK().
     */
    public function preprocessFontAwesomeIcon(array &$variables): void;
}
