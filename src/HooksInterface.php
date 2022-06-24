<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome;

/**
 *
 */
interface HooksInterface {

  /**
   * Implements the hook_page_attachments().
   *
   * @phpstan-param array<array-key,mixed> $page
   */
  public function pageAttachments(array &$page): void;

  /**
   * Implements the hook_library_info_alter().
   *
   * @phpstan-param array<array-key,mixed> $libraries
   */
  public function libraryInfoAlter(array &$libraries, string $extension): void;

  /**
   * Implements the hook_theme().
   *
   * @param mixed $existing
   * @param mixed $type
   * @param mixed $theme
   * @param mixed $path
   *
   * @phpstan-return array<array-key,mixed> $libraries
   */
  public function theme($existing, $type, $theme, $path): array;

  /**
   * Implements the hook_preprocess_HOOK().
   *
   * @phpstan-param array<array-key,mixed> $variables
   */
  public function preprocessFontAwesomeIcon(array &$variables): void;

}
