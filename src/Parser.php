<?php

namespace Drupal\csv_importer;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Parser manager.
 */
class Parser implements ParserInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs Parser object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getCsvById(int $id) {
    /* @var \Drupal\file\Entity\File $entity */
    $entity = $this->getCsvEntity($id);

    if ($entity && !empty($entity)) {
      return array_map('str_getcsv', file($entity->uri->getString()));
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCsvFieldsById(int $id) {
    $csv = $this->getCsvById($id);

    if ($csv && is_array($csv)) {
      return $csv[0];
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCsvEntity(int $id) {
    if ($id) {
      return $this->entityTypeManager->getStorage('file')->load($id);
    }

    return NULL;
  }

}
