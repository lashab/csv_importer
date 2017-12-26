<?php

namespace Drupal\csv_importer\Plugin\Importer;

use Drupal\csv_importer\Plugin\ImporterBase;

/**
 * Class BlockImporter.
 *
 * @Importer(
 *   id = "block_importer",
 *   entity_type = "block_content",
 *   label = @Translation("Block importer")
 * )
 */
class BlockImporter extends ImporterBase {}
