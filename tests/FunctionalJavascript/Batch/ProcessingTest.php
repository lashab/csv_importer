<?php

namespace Drupal\csv_importer\FunctionalJavascript\Batch;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

/**
 * Tests batch processing in form and non-form workflow.
 *
 * @group csv_importer
 */
class ProcessingTest extends JavascriptTestBase {

  /**
   * List of fields.
   */
  const FIELDS = [
    'boolean',
    'changed',
    'created',
    'decimal',
    'email',
    'entity_reference',
    'float',
    'integer',
    'string',
    'string_long',
    'timestamp',
    'uri',
    'datetime',
    'image',
    'file',
    'link',
    'list_float',
    'list_integer',
    'list_string',
    'telephone',
    'text',
    'text_long',
    'text_with_summary',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['csv_importer', 'node', 'link', 'file', 'pathauto', 'comment', 'datetime', 'image', 'telephone', 'options'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $account = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($account);

    $this->drupalCreateContentType(['type' => 'csv_importer_test']);
    $this->drupalCreateContentType(['type' => 'csv_importer_reference']);

    Node::create([
      'nid' => 1111,
      'title' => 'CSV importer reference node',
      'type' => 'csv_importer_reference',
    ])->save();

    User::create([
      'uid' => 1111,
      'name' => 'John Doe',
      'roles' => [$this->createAdminRole()], 
    ])->save();

    foreach (static::FIELDS as $name) {
      $field_name = 'field_' . $name;

      FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'type' => $name,
      ])->save();

      FieldConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'bundle' => 'csv_importer_test',
      ])->save();

      entity_get_display('node', 'csv_importer_test', 'default')
        ->setComponent($field_name)
        ->save();
      entity_get_form_display('node', 'csv_importer_test', 'default')
        ->setComponent($field_name)
        ->save();
    }
  }

  /**
   * Tests CSV importer add.
   */
  public function testCsvImporterAdd() {
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
    $this->drupalGet('/csv-importer-node-1');

    $this->createScreenshot('/Users/machd/Desktop/screen.jpg');
  }

}

