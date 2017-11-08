<?php

namespace Drupal\csv_importer\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\csv_importer\ParserInterface;

/**
 * Provides a base class for ImporterBase plugins.
 *
 * @see \Drupal\csv_importer\Annotation\Importer
 * @see \Drupal\csv_importer\Plugin\ImporterManager
 * @see \Drupal\csv_importer\Plugin\ImporterInterface
 * @see plugin_api
 */
abstract class ImporterBase extends PluginBase implements ImporterInterface {

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

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ParserInterface $parser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->parser = $parser;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('csv_importer.parser')
    );
  }

  public function process() {
    batch_set([
      'operations' => $this->getOperations(),
      'finished' => [$this, 'finished'],
    ]);
  }

  protected function preparedData() {
    $csv = $this->parser->getCsvById($this->configuration['cid']);
    $entity_type = $this->configuration['entity_type'];
    $entity_definition = $this->entityTypeManager->getDefinition($entity_type);

    if ($csv && is_array($csv)) {
      $operations = [];
      $prev = NULL;

      foreach ($csv as $index => &$content) {
        array_walk($content, function (&$value, $key) use ($index) {
          if ($index == 0) {
            $value = Unicode::strtolower($value);
            $value = preg_replace('/[^a-z0-9_]+/', '_', $value);
            $value = preg_replace('/_+/', '_', $value);
          }
          else {
            $value = Unicode::convertToUtf8($value, mb_detect_encoding($value));
          }
        });

        if ($index == 0) {
          $prev = $content;
        }

        if ($prev && $index) {
          $content = array_combine($prev, $content);
          $content = array_intersect_key($content, array_flip($this->configuration['fields']));
      
          if ($entity_definition->hasKey('bundle')) {
            $content[$entity_definition->getKey('bundle')] = $this->configuration['entity_type_bundle'];
          }

        }
      }
    }

    array_shift($csv);

    return $csv;
  }

  protected function getOperations() {
    $operations = [];
    $contents = $this->preparedData();

    foreach ($contents as $content) {
      $operations[] = [
        [$this, 'addContent'],
        [$this->configuration, $content],
      ];
    }

    return $operations;
  }

  public function addContent($configuration, $content, &$context) {
    $entity = $this->entityTypeManager->getStorage($configuration['entity_type'], $configuration['entity_type_bundle'])->create($content);
    $entity->enforceIsNew();
    $entity->save();

    $context['results'][] = $content;
  }


  public function finished($success, $contents, $operations) {
    $message = '';

    if ($success) {
      $message = t('@count content added', ['@count' => count($contents)]);
    }

    drupal_set_message($message);
  }

}
