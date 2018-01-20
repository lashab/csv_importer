<?php

namespace Drupal\csv_importer;

use Drupal\file\Entity\File;

/**
 * Parser manager.
 */
class Parser implements ParserInterface {

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
      return File::load($id);
    }

    return NULL;
  }

}
