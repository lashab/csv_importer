<?php

namespace Drupal\csv_importer\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Importer manager interface.
 */
interface ImporterInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {}