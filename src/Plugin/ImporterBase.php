<?php

namespace Drupal\csv_importer\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\csv_importer\ParserInterface;
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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Parser service.
   *
   * @var \Drupal\csv_importer\Parser\CsvParserInterface $csvParser
   */
  protected $parser;

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
   * @param \Drupal\csv_importer\ParserInterface $parser
   *   Parser manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ParserInterface $parser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->parser = $parser;
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
      $container->get('csv_importer.parser')
    );
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
   * {@inheritdoc}
   */
  public function getProcessData() {
    $csv = $this->parser->getCsvById($this->configuration['cid']);
    $entity_type = $this->configuration['entity_type'];
    $entity_definition = $this->entityTypeManager->getDefinition($entity_type);
    $return = [];

    if ($csv && is_array($csv)) {
      $keys = $csv[0];
      unset($csv[0]);

      foreach ($csv as $index => $data) {
        foreach ($data as $key => $content) {
          if ($content) {
            $content = Unicode::convertToUtf8($content, mb_detect_encoding($content));
            $fields = explode('|', $keys[$key]);

            if (count($fields) > 1) {
              $field = $fields[0];
              unset($fields[0]);

              foreach ($fields as $in) {
                $return[$index][$field][$in] = $content; 
              }
            }
            else {
              $return[$index][current($fields)] = $content;
            }
          }
        }

        $return[$index] = array_intersect_key($return[$index], array_flip($this->configuration['fields']));

        if ($entity_definition->hasKey('bundle')) {
          $return[$index][$entity_definition->getKey('bundle')] = $this->configuration['entity_type_bundle'];
        }
      }
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations() {
    $operations = [];
    foreach ($this->getProcessData() as $content) {
      $operations[] = [
        [$this, 'addContent'],
        [$this->configuration, $content],
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function addContent($configuration, $content, &$context) {
    $entity = $this->entityTypeManager->getStorage($configuration['entity_type'], $configuration['entity_type_bundle'])->create($content);
    $this->preSave($entity, $configuration, $context);
    $entity->save();

    $context['results'][] = $content;
  }

  /**
   * {@inheritdoc}
   */
  public function finished($success, $contents, $operations) {
    $message = '';

    if ($success) {
      $message = $this->t('@count content added', ['@count' => count($contents)]);
    }

    drupal_set_message($message);
  }

  protected function preSave(&$entity, $configuration, $context) {}

}
