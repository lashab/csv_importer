<?php

namespace Drupal\csv_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Importer settings form.
 */
class ImporterSettingsForm extends ConfigFormBase {

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_importer_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'csv_importer.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('csv_importer.settings');
    $form['delimiter'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Delimiter'),
      '#description' => $this->t('Enter the CSV delimter.'),
      '#default_value' => $config->get('csv.delimiter'),
    );

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
     $this->configFactory->getEditable('csv_importer.settings')
    ->set('csv.delimiter', $form_state->getValue('delimiter'))
    ->save();

    parent::submitForm($form, $form_state);
  }

}
