<?php

namespace Drupal\csv_importer\FunctionalJavascript\Batch;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Tests CSV importer.
 *
 * @group csv_importer
 */
class ImporterTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['csv_importer', 'csv_importer_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $account = $this->drupalCreateUser(['administer site configuration', 'administer users', 'access user profiles']);
    $this->drupalLogin($account);

    Node::create([
      'nid' => 1111,
      'title' => 'CSV importer reference node',
      'type' => 'csv_importer_test_content',
    ])->save();

    User::create([
      'uid' => 1111,
      'name' => 'John Doe',
      'roles' => [$this->createAdminRole()], 
    ])->save();

    Term::create([
      'tid' => 1111,
      'name' => 'CSV importer taxonomy reference',
      'vid' => 'csv_importer_taxonomy',
    ]);
  }

  /**
   * Test node importer.
   */
  // public function testNodeCsvImporter() {
  //   $this->drupalGet('admin/config/development/csv-importer');
  //   $this->assertSession()->statusCodeEquals(200);

  //   $page = $this->getSession()->getPage();
  //   $page->selectFieldOption('entity_type', 'node');
  //   $this->assertSession()->assertWaitOnAjaxRequest();

  //   $page->selectFieldOption('entity_type_bundle', 'csv_importer_test_content');
  //   $page->attachFileToField('files[csv]', drupal_get_path('module', 'csv_importer') . '/tests/csv_example_node_test.csv');
  //   $this->assertSession()->assertWaitOnAjaxRequest();
  //   $page->pressButton('CSV import');

  //   $this->assertSession()->assertWaitOnAjaxRequest();

  //   $this->drupalGet('/csv-importer-node-1');

  //   $this->assertSession()->elementTextContains('css', '.field--name-title', 'CSV importer node 1');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-boolean', 'On');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-email', 'example@example.com');
  //   $this->assertSession()->elementContains('css', '.field--name-field-link', '<a href="http://example.com">CSV importer link title</a>');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-timestamp', 'Fri, 01/12/2018 - 21:45');

  //   $this->assertSession()->elementTextContains('css', '.field--name-field-list-float', '17.1');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-list-integer', '18');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-list-text', 'List text 3');

  //   $this->assertSession()->elementTextContains('css', '.field--name-field-number-decimal', '17.10');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-float-number', '17.20');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-integer-number', '17');

  //   $this->assertSession()->elementContains('css', '.field--name-field-content-reference', '<a href="/node/1111" hreflang="en">CSV importer reference node</a>');
  //   $this->assertSession()->elementContains('css', '.field--name-field-user-reference', '<a href="/user/1111" hreflang="en">John Doe</a>');

  //   $this->assertSession()->elementContains('css', '.field--name-field-text-formatted', '<strong>Formatted text</strong>');
  //   $this->assertSession()->elementContains('css', '.field--name-field-text-formatted-long', '<strong>Formatted text long</strong>');
  //   $this->assertSession()->elementContains('css', '.field--name-field-text-formatted-summary', '<strong>Formatted text summary</strong>');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-text-plain', 'Plain text');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-text-plain-long', 'Plain text long');
  // }

  // /**
  //  * Test taxonomy term importer.
  //  */
  // public function testTaxonomyTermCsvImporter() {
  //   $this->drupalGet('admin/config/development/csv-importer');
  //   $this->assertSession()->statusCodeEquals(200);

  //   $page = $this->getSession()->getPage();
  //   $page->selectFieldOption('entity_type', 'taxonomy_term');
  //   $this->assertSession()->assertWaitOnAjaxRequest();

  //   $page->selectFieldOption('entity_type_bundle', 'csv_importer_taxonomy');
  //   $page->attachFileToField('files[csv]', drupal_get_path('module', 'csv_importer') . '/tests/csv_example_taxonomy_term_test.csv');
  //   $this->assertSession()->assertWaitOnAjaxRequest();
  //   $page->pressButton('CSV import');

  //   $this->assertSession()->assertWaitOnAjaxRequest();
  //   $this->drupalGet('/csv-importer-term-1');
  //   $this->assertSession()->statusCodeEquals(200);

  //   $this->assertSession()->elementTextContains('css', '.field--name-name', 'CSV importer term 1');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-boolean', 'On');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-email', 'example@example.com');
  //   $this->assertSession()->elementContains('css', '.field--name-field-link', '<a href="http://example.com">CSV importer link title</a>');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-timestamp', 'Fri, 01/12/2018 - 21:45');

  //   $this->assertSession()->elementTextContains('css', '.field--name-field-list-float', '17.1');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-list-integer', '18');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-list-text', 'List text 3');

  //   $this->assertSession()->elementTextContains('css', '.field--name-field-number-decimal', '17.10');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-float-number', '17.20');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-integer-number', '17');

  //   $this->assertSession()->elementContains('css', '.field--name-field-content-reference', '<a href="/node/1111" hreflang="en">CSV importer reference node</a>');
  //   $this->assertSession()->elementContains('css', '.field--name-field-user-reference', '<a href="/user/1111" hreflang="en">John Doe</a>');

  //   $this->assertSession()->elementContains('css', '.field--name-field-text-formatted', '<strong>Formatted text</strong>');
  //   $this->assertSession()->elementContains('css', '.field--name-field-text-formatted-long', '<strong>Formatted text long</strong>');
  //   $this->assertSession()->elementContains('css', '.field--name-field-text-formatted-summary', '<strong>Formatted text summary</strong>');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-text-plain', 'Plain text');
  //   $this->assertSession()->elementTextContains('css', '.field--name-field-text-plain-long', 'Plain text long');
  // }

  /**
   * Test user importer.
   */
  public function testUserCsvImporter() {
    $this->drupalGet('admin/config/development/csv-importer');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('entity_type', 'user');
    $this->assertSession()->assertWaitOnAjaxRequest();

    //$page->selectFieldOption('entity_type_bundle', 'csv_importer_taxonomy');
    $page->attachFileToField('files[csv]', drupal_get_path('module', 'csv_importer_test') . '/content/csv_example_user_test.csv');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->pressButton('CSV import');

    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->drupalGet('/user/7');
    $this->assertSession()->statusCodeEquals(200);
    $this->createScreenshot('/Users/machd/Desktop/screen.jpg');

    // $this->assertSession()->elementTextContains('css', '.field--name-name', 'CSV importer user 1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-boolean', 'On');
    $this->assertSession()->elementTextContains('css', '.field--name-field-email', 'example@example.com');
    $this->assertSession()->elementContains('css', '.field--name-field-link', '<a href="http://example.com">CSV importer link title</a>');
    $this->assertSession()->elementTextContains('css', '.field--name-field-timestamp', 'Fri, 01/12/2018 - 21:45');

    $this->assertSession()->elementTextContains('css', '.field--name-field-list-float', '17.1');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-integer', '18');
    $this->assertSession()->elementTextContains('css', '.field--name-field-list-text', 'List text 3');

    $this->assertSession()->elementTextContains('css', '.field--name-field-number-decimal', '17.10');
    $this->assertSession()->elementTextContains('css', '.field--name-field-float-number', '17.20');
    $this->assertSession()->elementTextContains('css', '.field--name-field-integer-number', '17');

    $this->assertSession()->elementContains('css', '.field--name-field-content-reference', '<a href="/node/1111" hreflang="en">CSV importer reference node</a>');
    $this->assertSession()->elementContains('css', '.field--name-field-user-reference', '<a href="/user/1111" hreflang="en">John Doe</a>');

    $this->assertSession()->elementContains('css', '.field--name-field-text-formatted', '<strong>Formatted text</strong>');
    $this->assertSession()->elementContains('css', '.field--name-field-text-formatted-long', '<strong>Formatted text long</strong>');
    $this->assertSession()->elementContains('css', '.field--name-field-text-formatted-summary', '<strong>Formatted text summary</strong>');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text-plain', 'Plain text');
    $this->assertSession()->elementTextContains('css', '.field--name-field-text-plain-long', 'Plain text long');
  }

}
