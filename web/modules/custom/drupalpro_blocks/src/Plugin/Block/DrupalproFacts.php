<?php

namespace Drupal\drupalpro_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Drupalpro Facts' Block.
 *
 * @Block(
 *   id = "drupalpro_facts",
 *   admin_label = @Translation("Drupalpro.dk Facts Block")
 * )
 */
class DrupalproFacts extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The node query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $nodeQuery;

  /**
   * Construct a AIContentArchiveFilterBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\Query\QueryInterface $nodeQuery
   *   The node query.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryInterface $nodeQuery) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeQuery = $nodeQuery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query')->get('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'fact_modules' => 0,
      'fact_developers_worldwide' => 0,
      'fact_jobs_denmark' => 0
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['fact_modules'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fact - Modules'),
      '#description' => $this->t('The number of available modules on drupal.org.'),
      '#default_value' => $this->configuration['fact_modules'],
    ];
    $form['fact_developers_worldwide'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fact - Developers worldwide'),
      '#description' => $this->t('The number of developers registered on Drupal.org.'),
      '#default_value' => $this->configuration['fact_developers_worldwide'],
    ];
    $form['fact_jobs_denmark'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fact - Jobs in Denmark'),
      '#description' => $this->t('The number of jobs related to Drupal in Denmark.'),
      '#default_value' => $this->configuration['fact_jobs_denmark'],
    ];
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
    $this->configuration['fact_modules'] = $form_state->getValue('fact_modules');
    $this->configuration['fact_developers_worldwide'] = $form_state->getValue('fact_developers_worldwide');
    $this->configuration['fact_jobs_denmark'] = $form_state->getValue('fact_jobs_denmark');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#attributes' => [
        'class' => []
      ],
      '#cache' => [
        'contexts' => [
          'languages:language_interface',
          'url'
        ],
      ],
      'fact_modules' => $this->configuration['fact_modules'],
      'fact_developers_worldwide' => $this->configuration['fact_developers_worldwide'],
      'fact_jobs_denmark' => $this->configuration['fact_jobs_denmark'],
      'fact_drupalpro_members' => $this->getMemberCount(),
    ];
    return $build;
  }

  /**
   * Find the number of active Drupalpro members.
   *
   * @return int
   *   The number of members.
   */
  function getMemberCount() {
    $query = $this->nodeQuery;
    $query->condition('status', 1);
    $query->condition('type', 'member');
    $query->count();
    return (int) $query->execute();
  }

}
