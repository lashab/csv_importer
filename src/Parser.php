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
  public function getCsvById($id) {
    /* @var \Drupal\file\Entity\File $entity */
    $entity = File::load($id);
    return array_map('str_getcsv', file($entity->uri->getString()));
  }

  public function getCsvFieldsById($id) {
    return $this->getCsvById($id)[0];
  }

  public function getCsvKeys() {
    
  }

}
