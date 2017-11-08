<?php


namespace Drupal\csv_importer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\csv_importer\Parser\CsvParserInterface;
use Drupal\Component\Utility\Unicode;

class CsvImporter implements CsvImporterInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Csv parser service.
   *
   * @var \Drupal\csv_importer\Parser\CsvParserInterface $csvParser
   */
  protected $csvParser;

  /**
   * CsvImporter class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CsvParserInterface $csv_parser) {
    $this->entityTypeManager = $entity_type_manager;
    $this->csvParser = $csv_parser;
  }

  public function process($cid, $entity_type, $entity_type_bundle, $fields) {

    $csv = $this->csvParser->getCsvById($cid);

    if ($csv && is_array($csv)) {
      $operations = [];
      $prev = NULL;

      foreach ($csv as $index => $content) {
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
          $content = array_intersect_key($content, array_flip($fields));
        }

        $operations[] = [
          get_class($this) . '::create',
          [$entity_type, $entity_type_bundle, $content],
        ];
      }

      array_shift($operations);

      batch_set([
        'operations' => $operations,
        'finished' => [get_class($this) . '::finish'],
      ]);
    }
  }

  public static function create($entity_type, $entity_type_bundle, $content, &$context) {
    $content = \Drupal::entityTypeManager()->getStorage($entity_type, $entity_type_bundle)->create($content);
    $content->enforceIsNew();
    $content->save();

    $context['results'][] = $content;
  }


  public static function finish($success, $contents, $operations) {
    $message = '';

    if ($success) {
      $message = t('@count content created', ['@count' => count($contents)]);
    }

    drupal_set_message($message);
  }


}