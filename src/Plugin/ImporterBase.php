<?php

namespace Drupal\csv_importer\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a base class for ImporterBase plugins.
 *
 * @see \Drupal\csv_importer\Annotation\Importer
 * @see \Drupal\csv_importer\Plugin\ImporterManager
 * @see \Drupal\csv_importer\Plugin\ImporterInterface
 * @see plugin_api
 */
abstract class ImporterBase extends PluginBase implements ImporterInterface {

  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs ImporterBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function data() {
    $csv = $this->configuration['csv'];
    $return = [];

    if ($csv && is_array($csv)) {
      $csv_fields = $csv[0];
      unset($csv[0]);
      foreach ($csv as $index => $data) {
        foreach ($data as $key => $content) {
          if ($content) {
            $content = Unicode::convertToUtf8($content, mb_detect_encoding($content));
            $fields = explode('|', $csv_fields[$key]);

            if (count($fields) > 1) {
              $field = $fields[0];
              foreach ($fields as $in) {
                $return[$index][$field][$in] = $content;
              }
            }
            else {
              $return[$index][current($fields)] = $content;
            }
          }
        }

        if (isset($return[$index])) {
          $return[$index] = array_intersect_key($return[$index], array_flip($this->configuration['fields']));
        }
      }
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function add($content, array &$context) {
    if (!$content) {
      return NULL;
    }

    $entity_type = $this->configuration['entity_type'];
    $entity_type_bundle = $this->configuration['entity_type_bundle'];
    $entity_definition = $this->entityTypeManager->getDefinition($entity_type);

    $added = 0;

    foreach ($content as $data) {
      if ($entity_definition->hasKey('bundle') && $entity_type_bundle) {
        $data[$entity_definition->getKey('bundle')] = $this->configuration['entity_type_bundle'];
      }

      $entity = $this->entityTypeManager->getStorage($this->configuration['entity_type'], $this->configuration['entity_type_bundle'])->create($data);
      $this->preSave($entity, $data, $context);
  
      try {
        $added += $entity->save();
      }
      catch (\Exception $e) {
      }
    }

    if ($added) {
      $context['results'] = $added;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getOperations() {
    $operations[] = [
      [$this, 'add'],
      [$this->data()],
    ];

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function finished($success, $results, array $operations) {
    $message = '';

    if ($success) {
      $message = $this->t('@count content added.', ['@count' => $results]);
    }

    drupal_set_message($message);
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $process = [];
    if ($operations = $this->getOperations()) {
      $process['operations'] = $operations;
    }

    $process['finished'] = [$this, 'finished'];

    batch_set($process);
  }

  /**
   * Override entity before run Entity::save().
   *
   * @param mixed $entity
   *   The entity object.
   * @param array $content
   *   The content array to be saved.
   * @param array $context
   *   The batch context array.
   */
  public function preSave(&$entity, array $content, array &$context) {}

}
