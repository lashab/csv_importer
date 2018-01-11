<?php

namespace Drupal\csv_importer\FunctionalJavascript\Batch;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Vocabulary;

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
  protected static $modules = ['csv_importer', 'node', 'link', 'file', 'pathauto', 'comment', 'datetime', 'image', 'telephone', 'options', 'taxonomy'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $account = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($account);

    $this->drupalCreateContentType(['type' => 'csv_importer_test']);
    $this->drupalCreateContentType(['type' => 'csv_importer_reference']);

    Vocabulary::create([
      'name' => 'csv_importer_vocab',
    ])->save();

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
      $this->createFields($name, 'node', 'csv_importer_test');
      $this->createFields($name, 'taxonomy_term', 'csv_importer_vocab');
    }
  }

  /**
   * Test node importer.
   */
  public function testCsvImporterNodeAdd() {
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
    $this->assertSession()->statusCodeEquals(200);

    // $this->createScreenshot('/Users/machd/Desktop/screen.jpg');

    $this->assertSession()->elementTextContains('css', '.field--name-title', 'CSV importer test 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-boolean', 'On');
    $this->assertSession()->elementTextContains('css', '.field--name-field-changed', 'Thu, 01/11/2018 - 20:36');
    $this->assertSession()->elementTextContains('css', '.field--name-field-created', 'Thu, 01/11/2018 - 20:36');
    $this->assertSession()->elementTextContains('css', '.field--name-field-decimal', '17.10');
    $this->assertSession()->elementTextContains('css', '.field--name-field-email', 'example@field_email.com');
    $this->assertSession()->elementTextContains('css', '.field--name-field-float', '19.70');
    $this->assertSession()->elementTextContains('css', '.field--name-field-integer', '1777');
    $this->assertSession()->elementTextContains('css', '.field--name-field-string', 'String 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-string-long', 'Long string 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-timestamp', 'Sun, 12/31/2017 - 06:50');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-float', '17.5');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-integer', '1117');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-string', 'List string 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-telephone', '11111111111');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text', 'Text 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text-long', 'Long text 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text-with-summary', 'Text with summary 1');
    $this->assertSession()->linkByHrefExists('/node/1111');
    $this->assertSession()->linkByHrefExists('http://example_field_uri.com');
    $this->assertSession()->linkByHrefExists('http://example_field_link.com');
  }

  /**
   * Test taxonomy term importer.
   */
  public function testCsvImporterTaxonomyTermAdd() {
    $this->drupalGet('admin/config/development/csv-importer');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('entity_type', 'taxonomy_term');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->selectFieldOption('entity_type_bundle', 'csv_importer_taxonomy');
    $page->attachFileToField('files[csv]', drupal_get_path('module', 'csv_importer') . '/tests/csv_example_taxonomy_term_test.csv');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('CSV import');

    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->drupalGet('/term/csv-importer-node-1');
    $this->assertSession()->statusCodeEquals(200);

    
    $this->assertSession()->elementTextContains('css', '.field--name-title', 'CSV importer term test 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-boolean', 'On');
    $this->assertSession()->elementTextContains('css', '.field--name-field-changed', 'Thu, 01/11/2018 - 20:36');
    $this->assertSession()->elementTextContains('css', '.field--name-field-created', 'Thu, 01/11/2018 - 20:36');
    $this->assertSession()->elementTextContains('css', '.field--name-field-decimal', '17.10');
    $this->assertSession()->elementTextContains('css', '.field--name-field-email', 'example@field_email.com');
    $this->assertSession()->elementTextContains('css', '.field--name-field-float', '19.70');
    $this->assertSession()->elementTextContains('css', '.field--name-field-integer', '1777');
    $this->assertSession()->elementTextContains('css', '.field--name-field-string', 'String 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-string-long', 'Long string 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-timestamp', 'Sun, 12/31/2017 - 06:50');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-float', '17.5');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-integer', '1117');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-string', 'List string 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-telephone', '11111111111');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text', 'Text 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text-long', 'Long text 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text-with-summary', 'Text with summary 1');
    $this->assertSession()->linkByHrefExists('/node/1111');
    $this->assertSession()->linkByHrefExists('http://example_field_uri.com');
    $this->assertSession()->linkByHrefExists('http://example_field_link.com');
  }

  /**
   * Test user importer.
   */
  // public function testCsvImporterUserAdd() {
  // }

  /**
   * Test block importer.
   */
  // public function testCsvImporterBlockAdd() {

  // }

  protected function createFields($name, $entity_type, $entity_type_bundle = NULL) {
    FieldStorageConfig::create([
      'field_name' => 'field_' . $name,
      'entity_type' => $entity_type,
      'type' => $name,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_' . $name,
      'entity_type' => $entity_type,
      'bundle' => $entity_type_bundle,
    ])->save();

    entity_get_display($entity_type, $entity_type_bundle, 'default')
      ->setComponent('field_' . $name)
      ->save();
  }

  protected function formSubmit() {

  }

}

