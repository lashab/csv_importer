<?php

namespace Drupal\csv_importer\Plugin\Importer;

use Drupal\csv_importer\Plugin\ImporterBase;
use Drupal\csv_importer\Plugin\ImporterInterface;

/**
 * Class NodeImporter.
 *
 * @Importer(
 *   id = "node_importer_custom",
 *   entity_type = "node",
 *   label = @Translation("Node importer custom")
 * )
 */
class NodeImporterCustom extends ImporterBase {}