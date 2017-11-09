<?php

namespace Drupal\csv_importer;

/**
 * Csv parser manager interface.
 */
interface ParserInterface {
  
  public function getCsvById($id);

}
