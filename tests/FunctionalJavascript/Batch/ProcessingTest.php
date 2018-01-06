<?php

namespace Drupal\csv_importer\FunctionalJavascript\Batch;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Tests batch processing in form and non-form workflow.
 *
 * @group Batch
 */
class ProcessingTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['csv_importer'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  public function testCsvImporterPage() {
    $account = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($account);
    $this->drupalGet('admin/config/development/csv-importer');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getSession()->getPage();
    $page->hasSelect('entity_type');
    // $page->selectFieldOption('entity_type', 'Content');
    // $page->waitForElement();
    // $page->selectFieldOption('entity_type_bundle', 'Event');

    // $page->findField('update');
    // $page->checkField('update');
    // $page->fillField('update_field', 'tid');
    // $this->createScreenshot('/Users/machd/Desktop/screen.jpg');

    // $this->getSession()->getPage()->fillField('entity_type', 'node');
    // // $this->getSession()->getPage()->waitForElement();
    // // $this->getSession()->getPage()->fillField('entity_type_bundle', 'event');

    // $this->createScreenshot('/Users/machd/Desktop/screen.jpg');

    $this->assertRaw(t('View'));
    $this->assertRaw(t('Settings'));
  }

}

