<?php declare(strict_types=1);

/*
 * This file is part of ptomulik/icon_bundle_fontawesome.
 *
 * Copyright (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * View the LICENSE file for full copyright and license information.
 */

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
