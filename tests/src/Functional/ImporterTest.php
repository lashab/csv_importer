<?php

namespace Drupal\Tests\csv_importer\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\FunctionalJavaScriptTests\JavascriptTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests node field filters with translations.
 *
 * @group csv_importer
 */
class ImporterTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // $this->drupalCreateContentType(['type' => 'csv_content', 'name' => 'CSV Content']);
  }

}
