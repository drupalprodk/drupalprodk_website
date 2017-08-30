<?php

namespace Drupal\drupalpro_blocks\Plugin\Block;

use Drupal\Core\Render\Markup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Drupalpro Top Image' Block.
 *
 * @Block(
 *   id = "drupalpro_top_image",
 *   admin_label = @Translation("Drupalpro.dk Top Image Block"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", required = FALSE)
 *   }
 * )
 */
class DrupalproTopImage extends BlockBase implements ContainerFactoryPluginInterface {

  /**
  * The entity type manager.
  *
  * @var \Drupal\Core\Entity\EntityTypeManagerInterface
  */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Construct a AIContentArchiveFilterBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current node object.
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getContextValue('node');

    // Find image and text from context.
    $image = '';
    $text = '';
    if (is_a($node, '\Drupal\node\NodeInterface')) {
      // Finding image
      if ($node->hasField('field_top_image')) {
        if (!$node->get('field_top_image')->isEmpty()) {
          $image = $node->get('field_top_image')->referencedEntities();
          $image = is_array($image) ? $image[0] : NULL;
        }
      }

      // Find image text.
      if ($node->hasField('field_top_image_text') && !$node->get('field_top_image_text')->isEmpty()) {
        $text = $node->get('field_top_image_text')->first()->getValue()['value'];
      }
    }

    // Find default image.
    if (!is_a($image, '\Drupal\file\FileInterface')) {
      $fields = $this->entityFieldManager->getFieldDefinitions('node', 'page');
      $default_image = $fields['field_top_image']->getSetting('default_image');
      $image = $this->entityTypeManager->getStorage('file')->loadByProperties(['uuid' => $default_image['uuid']]);
      $image = is_array($image) ? current($image) : NULL;
    }

    // Find image URI.
    $image_uri = '';
    if (is_a($image, '\Drupal\file\FileInterface')) {
      $image_uri = $image->getFileUri();
    }

    // Prepare build.
    $build = [
      '#attributes' => [
        'class' => []
      ],
      '#cache' => [
        'contexts' => [
          'languages:language_interface',
          'url'
        ],
      ]
    ];
    $build['site_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
      '#alt' => $this->t('Home'),
    ];
    $build['top_text'] = [
      '#markup' => Markup::create(trim($text)),
    ];
    $build['top_image'] = [
      '#theme' => 'image',
      '#uri' => $image_uri,
      '#alt' => '',
    ];

    return $build;
  }

}
