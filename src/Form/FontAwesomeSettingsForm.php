<?php

declare(strict_types=1);

namespace Drupal\icon_bundle_fontawesome\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\icon_bundle_fontawesome\Metadata\MetadataLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Drupal\icon_bundle_fontawesome\Form\FontAwesomeSettingsForm.
 */
class FontAwesomeSettingsForm extends ConfigFormBase {
  use FormDefaultsTrait;
  /**
   * @var string
   */
  protected $appRoot;

  /**
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   *
   */
  public function __construct(string $app_root, ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typed_config_manager) {
    $this->appRoot = $app_root;
    $this->typedConfigManager = $typed_config_manager;
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $app_root = $container->get('app.root');
    $config_factory = $container->get('config.factory');
    $typed_config_manager = $container->get('config.typed');

    return new static($app_root, $config_factory, $typed_config_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'icon_bundle_fontawesome_settings_form';
  }

  /**
   *
   */
  public function getAutocompletePrefix(): string {
    return 'icon_bundle_fontawesome.autocomplete.settings_form.';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $definition = $this->typedConfigManager->get($this->getConfigId());
    $config = $this->configFactory->get(static::getConfigId());

    $sample = [
      'version'       => '6.1.1',
      'asset_uri'     => 'https://use.fontawesome.com/releases/v' . $config->get('asset.version'),
      'asset_path'    => 'libraries/fortawesome--fontawesome-free',
      'asset_kit_uri' => 'https://kit.fontawesome.com/deadbeef42.js',
      'metadata_uri'  => 'https://use.fontawesome.com/releases/v' . $config->get('asset.version') . '/metadata',
      'metadata_path' => 'libraries/fortawesome--fontawesome-free/metadata',
    ];

    $asset_version_value = $form_state->getValue(['asset', 'version'], $config->get('asset.version'));
    $metadata_wrapper_id = $this->getFormId() . '-metadata-wrapper';
    $ajax_update_metadata = [
      'callback'        => [self::class, 'updateMetadataSettings'],
      'event'           => 'change',
      'wrapper'         => $metadata_wrapper_id,
      'disable-refocus' => TRUE,
    ];

    $form['method'] = [
      '#type'    => 'select',
      '#options' => [
        'svg'      => 'SVG With JS',
        'webfonts' => 'Web Fonts with CSS',
      ],
    ];

    $form['asset'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#open' => TRUE,
    ];

    $form['asset']['delivery'] = [
      '#type'    => 'select',
      '#options' => [
        'self' => $this->t('Self hosted'),
        'cdn'  => $this->t('Content Delivery Network (CDN)'),
        'kit'  => $this->t('Font Awesome Kit'),
      ],
      '#ajax' => $ajax_update_metadata,
    ];

    $form['asset']['version'] = [
      '#type'        => 'textfield',
      '#placeholder' => $this->t('For example :version', [':version' => '6.1.1']),
      '#states'      => [
        'visible' => [
                  [':input[name="asset[delivery]"]' => ['value' => 'cdn']],
          'or',
                  [':input[name="metadata[delivery]"]' => ['value' => 'cdn']],
        ],
      ],
    ];

    $form['asset']['cdn'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $ac_prefix = $this->getAutocompletePrefix();

    $form['asset']['cdn']['uri'] = [
      '#type'                          => 'textfield',
      '#placeholder'                   => $this->t('For example :uri', [':uri' => $sample['asset_uri']]),
      '#autocomplete_route_name'       => $ac_prefix . 'asset.cdn.uri',
      '#autocomplete_route_parameters' => [
        'version' => $asset_version_value,
      ],
      '#states' => [
        'visible' => [
          ':input[name="asset[delivery]"]' => ['value' => 'cdn'],
        ],
      ],
      '#ajax' => $ajax_update_metadata,
    ];

    $form['asset']['self'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['asset']['self']['path'] = [
      '#type'        => 'textfield',
      '#placeholder' => $this->t('For example :path', [':path' => $sample['asset_path']]),
      '#states'      => [
        'visible' => [
          ':input[name="asset[delivery]"]' => ['value' => 'self'],
        ],
      ],
      '#ajax' => $ajax_update_metadata,
    ];

    $form['asset']['kit'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['asset']['kit']['uri'] = [
      '#type'        => 'textfield',
      '#placeholder' => $this->t('For example :uri', [':uri' => $sample['asset_kit_uri']]),
      '#states'      => [
        'visible' => [
          ':input[name="asset[delivery]"]' => ['value' => 'kit'],
        ],
      ],
    ];

    $form['asset']['use'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['asset']['use']['solid'] = [
      '#type'   => 'checkbox',
      '#states' => [
        'invisible' => [
          ':input[name="asset[delivery]"]' => ['value' => 'kit'],
        ],
      ],
    ];

    $form['asset']['use']['regular'] = [
      '#type'   => 'checkbox',
      '#states' => [
        'invisible' => [
          ':input[name="asset[delivery]"]' => ['value' => 'kit'],
        ],
      ],
    ];

    $form['asset']['use']['light'] = [
      '#type'   => 'checkbox',
      '#states' => [
        'invisible' => [
          ':input[name="asset[delivery]"]' => ['value' => 'kit'],
        ],
      ],
    ];

    $form['asset']['use']['brands'] = [
      '#type'   => 'checkbox',
      '#states' => [
        'invisible' => [
          ':input[name="asset[delivery]"]' => ['value' => 'kit'],
        ],
      ],
    ];

    $form['metadata'] = [
      '#type'   => 'details',
      '#tree'   => TRUE,
      '#open'   => TRUE,
      '#prefix' => '<div id="' . $metadata_wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $form['metadata']['delivery'] = [
      '#type'    => 'select',
      '#options' => [
        'auto' => $this->t('Based on Asset Settings'),
        'self' => $this->t('Local filesystem on server'),
        'cdn'  => $this->t('Remote location'),
      ],
      '#ajax' => $ajax_update_metadata,
    ];

    $metadata_delivery = $form_state->getValue(['metadata', 'delivery']) ?? $config->get('metadata.delivery');
    if ('auto' === $metadata_delivery || 'self' === $metadata_delivery) {
      $metadata_locator = MetadataLocator::createFromConfig($this->appRoot, $this->configFactory, $form_state->getValues());
      $form['metadata']['delivery']['#description'] = $this->t('Current location: :location', [
        ':location' => $metadata_locator->getLocation(),
      ]);
    }

    $form['metadata']['self'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['metadata']['self']['path'] = [
      '#type'        => 'textfield',
      '#placeholder' => $this->t('For example :path', [':path' => $sample['metadata_path']]),
      '#states'      => [
        'visible' => [
          ':input[name="metadata[delivery]"]' => ['value' => 'self'],
        ],
      ],
      '#ajax' => $ajax_update_metadata,
    ];

    $form['metadata']['cdn'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['metadata']['cdn']['uri'] = [
      '#type'                          => 'textfield',
      '#placeholder'                   => $this->t('For example :uri', [':uri' => $sample['metadata_uri']]),
      '#autocomplete_route_name'       => $ac_prefix . 'metadata.cdn.uri',
      '#autocomplete_route_parameters' => [
        'version' => $asset_version_value,
      ],
      '#states' => [
        'visible' => [
          ':input[name="metadata[delivery]"]' => ['value' => 'cdn'],
        ],
      ],
    ];

    $this->setFormDefaults($form);

    return parent::buildForm($form, $form_state);
  }

  /**
   *
   */
  public static function updateMetadataSettings(array &$form, FormStateInterface $form_state): array {
    return $form['metadata'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $asset_delivery = $form_state->getValue(['asset', 'delivery']);
    $asset_cdn_uri = $form_state->getValue(['asset', 'cdn', 'uri']) ?? '';
    $asset_kit_uri = $form_state->getValue(['asset', 'kit', 'uri']) ?? '';

    if ('cdn' === $asset_delivery) {
      // Validate URL.
      if ('' === $asset_cdn_uri || !UrlHelper::isValid($asset_cdn_uri, TRUE)) {
        $title = $form['asset']['cdn']['uri']['#title'] ?? $this->t('Remote asset location');
        $form_state->setError($form['asset']['cdn']['uri'], $this->t('Invalid ') . $title);
      }
    }
    elseif ('kit' === $asset_delivery) {
      // Validate URL.
      if ('' === $asset_kit_uri || !UrlHelper::isValid($asset_kit_uri, TRUE)) {
        $title = $form['asset']['kit']['uri']['#title'] ?? $this->t('Remote asset location');
        $form_state->setError($form['asset']['kit']['uri'], $this->t('Invalid ') . $title);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // --
    // --        // Load fontawesome libraries
    // --        $library = $this->libraryDiscovery->getLibraryByName('icon_bundle_fontawesome', 'icon_bundle_fontawesome.svg');
    // --
    // --        // Default location of the library
    // --        $default_location = 'https://use.fontawesome.com/releases/v'.$library['version'].'/';
    // --
    // --        $default_svg_location = $default_location.'js/all.js';
    // --        $default_webfonts_location = $default_location.'css/all.css';
    // --
    // --        if ($values['asset']['delivery']) {
    // --            if (empty($values['asset']['cdn']['uri'])) {
    // --                $values['asset']['cdn']['uri'] = ('webfonts' == $values['method']) ? $default_webfonts_location : $default_svg_location;
    // --            }
    // --        }
    // Save the settings
    // --        $this->config('icon_bundle_fontawesome.settings')
    $this->configFactory->getEditable(static::getConfigId())
      ->set('method', $form_state->getValue(['method']))
      ->set('asset.delivery', $form_state->getValue(['asset', 'delivery']))
      ->set('asset.cdn.uri', $form_state->getValue(['asset', 'cdn', 'uri']))
      ->set('asset.self.path', $form_state->getValue(['asset', 'self', 'path']))
      ->set('asset.kit.uri', $form_state->getValue(['asset', 'kit', 'uri']))
      ->set('asset.use.solid', $form_state->getValue(['asset', 'use', 'solid']))
      ->set('asset.use.light', $form_state->getValue(['asset', 'use', 'light']))
      ->set('asset.use.regular', $form_state->getValue(['asset', 'use', 'regular']))
      ->set('asset.use.brands', $form_state->getValue(['asset', 'use', 'brands']))
      ->set('metadata.delivery', $form_state->getValue(['metadata', 'delivery']))
      ->set('metadata.cdn.uri', $form_state->getValue(['metadata', 'cdn', 'uri']))
      ->set('metadata.self.path', $form_state->getValue(['metadata', 'self', 'path']))
      ->save();

    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [static::getConfigId()];
  }

  /**
   *
   */
  protected function getConfigDataDefinition(string $name): DataDefinitionInterface {
    return $this->getTypedConfig($name)->getDataDefinition();
  }

  /**
   *
   */
  protected function getTypedConfig(string $name = ''): TypedDataInterface {
    $typed_config = $this->typedConfigManager->get($this->getConfigId());

    return '' === $name ? $typed_config : $typed_config->get($name);
  }

  /**
   *
   */
  protected function getDataLabel(string $name, bool $translate = TRUE) {
    return $this->translateIf($this->getConfigDataDefinition($name)->getLabel(), $translate);
  }

  /**
   *
   */
  protected function getDataDescription(string $name, bool $translate = TRUE) {
    return $this->translateIf($this->getConfigDataDefinition($name)->getDescription(), $translate);
  }

  /**
   *
   */
  protected function translateIf(?string $string, bool $flag) {
    if (NULL !== $string && $flag) {
      $string = $this->t($string);
    }

    return $string;
  }

  /**
   *
   */
  protected static function getConfigId(): string {
    return 'icon_bundle_fontawesome.settings';
  }

  /**
   *
   */
  protected function getTypedConfigManager(): TypedConfigManagerInterface {
    return $this->typedConfigManager;
  }

  /**
   *
   */
  protected function getConfigFactory(): ConfigFactoryInterface {
    return $this->configFactory;
  }

}
