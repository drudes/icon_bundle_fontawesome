<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\TypedData\TraversableTypedDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 *
 */
trait FormDefaultsTrait {

  /**
   * @phpstan-param array<array-key,mixed> $form
   */
  protected function setFormDefaults(array &$form): void {
    $this->defaultsRecursiveWalkConfig($form);
  }

  /**
   * @phpstan-param array<array-key,mixed> $form
   */
  protected function defaultsRecursiveWalkConfig(array &$form): void {
    $typed_config = $this->getTypedConfigManager()->get($this->getConfigId());
    $this->defaultsRecursiveWalkMapping($form, $typed_config);
  }

  /**
   * @phpstan-param array<array-key,mixed> $element
   */
  protected function defaultsRecursiveWalkMapping(array &$element, TraversableTypedDataInterface $mapping): void {
    foreach ($mapping as $key => $property) {
      if ('_core' !== $key && array_key_exists($key, $element)) {
        if ($property instanceof TraversableTypedDataInterface && is_array($element[$key])) {
          $this->defaultsVisitNonLeafNode($element[$key], $property);
          $this->defaultsRecursiveWalkMapping($element[$key], $property);
        }
        else {
          $this->defaultsVisitLeafNode($element[$key], $property);
        }
      }
    }
  }

  /**
   * @phpstan-param array<array-key,mixed> $element
   */
  protected function defaultsVisitLeafNode(array &$element, TypedDataInterface $data): void {
    $this->defaultsVisitNode($element, $data);
    if (!array_key_exists('#default_value', $element)) {
      $config = $this->getConfigFactory()->get(static::getConfigId());
      $path = $data->getPropertyPath();
      if (NULL !== ($value = $config->get($path))) {
        $element['#default_value'] = $value;
      }
    }
  }

  /**
   * @phpstan-param array<array-key,mixed> $element
   */
  protected function defaultsVisitNonLeafNode(array &$element, TypedDataInterface $data): void {
    $this->defaultsVisitNode($element, $data);
  }

  /**
   * @phpstan-param array<array-key,mixed> $element
   */
  protected function defaultsVisitNode(array &$element, TypedDataInterface $data): void {
    $definition = $data->getDataDefinition();
    $this->defaultsSetTranslatableProperty($element, '#title', $definition->getLabel());
    $this->defaultsSetTranslatableProperty($element, '#description', $definition->getDescription());
  }

  /**
   * @param mixed $value
   * @phpstan-param array<array-key,mixed> $element
   */
  protected function defaultsSetTranslatableProperty(array &$element, string $key, $value): void {
    if ('' !== (string) $value && !array_key_exists($key, $element)) {
      $element[$key] = $this->t((string)$value);
    }
  }

  /**
   *
   */
  abstract protected static function getConfigId(): string;

  /**
   *
   */
  abstract protected function getTypedConfigManager(): TypedConfigManagerInterface;

  /**
   *
   */
  abstract protected function getConfigFactory(): ConfigFactoryInterface;

}
