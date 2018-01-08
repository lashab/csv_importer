<?php

namespace Drupal\csv_importer\FunctionalJavascript\Batch;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Tests batch processing in form and non-form workflow.
 *
 * @group csv_importer
 */
class ProcessingTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['csv_importer', 'node', 'link', 'file', 'views', 'pathauto'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $account = $this->drupalCreateUser(['access content overview', 'administer site configuration']);
    $this->drupalLogin($account);
    $this->drupalCreateContentType(['type' => 'csv_importer_test']);

    FieldStorageConfig::create([
      'field_name' => 'field_link',
      'entity_type' => 'node',
      'type' => 'link',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_link',
      'entity_type' => 'node',
      'bundle' => 'csv_importer_test',
    ])->save();

    entity_get_display('node', 'csv_importer_test', 'default')
      ->setComponent('field_link')
      ->save();
    entity_get_form_display('node', 'csv_importer_test', 'default')
      ->setComponent('field_link')
      ->save();
  }

  public function testCsvImporterPage() {
    $this->drupalGet('admin/config/development/csv-importer');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('entity_type', 'node');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->selectFieldOption('entity_type_bundle', 'csv_importer_test');
    $page->attachFileToField('files[csv]', drupal_get_path('module', 'csv_importer') . '/tests/csv_example_node_test.csv');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('CSV import');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->drupalGet('/csv-importer-test-1');
    $this->assertSession()->linkExists('CSV importer test link 1');
  }

}

