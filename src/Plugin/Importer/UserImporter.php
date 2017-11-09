<?php

namespace Drupal\csv_importer\Plugin\Importer;

use Drupal\csv_importer\Plugin\ImporterBase;
use Drupal\csv_importer\Plugin\ImporterInterface;

/**
 * Class UserImporter.
 *
 * @Importer(
 *   id = "user_importer",
 *   entity_type = "user",
 *   label = @Translation("User importer")
 * )
 */
class UserImporter extends ImporterBase {}