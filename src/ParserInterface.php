<?php

namespace Drupal\csv_importer;

/**
 * Csv parser manager interface.
 */
interface ParserInterface {

  /**
   * Get CSV by id.
   *
   * @param string $id
   *   CSV id.
   *
   * @return array|null
   *   Parsed CSV.
   */
  public function getCsvById($id);

  /**
   * Get CSV fields (first row).
   *
   * @param string $id
   *   CSV id.
   *
   * @return array|null
   *   CSV field names.
   */
  public function getCsvFieldsById($id);

  /**
   * Load CSV.
   *
   * @param string $id
   *   CSV id.
   *
   * @return \Drupal\file\Entity\File|null
   *   Entity object.
   */
  public function getCsvEntity($id);

}
