<?php

namespace Drupal\csv_importer\Plugin\Importer;

use Drupal\csv_importer\Plugin\ImporterBase;

/**
 * Class UserImporter.
 *
 * @Importer(
 *   id = "user_importer",
 *   entity_type = "user",
 *   label = @Translation("User importer")
 * )
 */
class UserImporter extends ImporterBase {

  /**
   * {@inheritdoc}
   */
  public function preSave(&$entity, array $content, array &$context) {
    if (user_load_by_name($entity->getUsername())) {
      /* @var \Drupal\user\Entity\User $entity */
      $entity = user_load_by_name($entity->getUsername());

      unset($content['name']);
      foreach ($content as $key => $data) {
        $entity->set($key, $data);
      }
    }
  }

}
