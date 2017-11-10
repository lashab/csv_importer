<?php

namespace Drupal\csv_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\UpdateBuildIdCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\csv_importer\ParserInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\csv_importer\Plugin\ImporterManager;

/**
 * Provides CSV importer form.
 */
class ImporterForm extends FormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   */
  protected $entityFieldManager;

  /**
   * Parser manager.
   *
   * @var \Drupal\csv_importer\Parser\ParserInterface $parser
   */
  protected $parser;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface $renderer
   */
  protected $renderer;

  /**
   * Importer plugin manager.
   *
   * @var \Drupal\csv_importer\Plugin\ImporterInterface $importer
   */
  protected $importer;

  /**
   * ImporterForm class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ParserInterface $parser, RendererInterface $renderer, ImporterManager $importer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->parser = $parser;
    $this->renderer = $renderer;
    $this->importer = $importer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('csv_importer.parser'),
      $container->get('renderer'),
      $container->get('plugin.manager.importer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_importer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose entity type'),
      '#required' => TRUE,
      '#options' => $this->getEntityTypeOptions(),
      '#ajax' => [
        'callback' => [$this, 'getContentEntityTypesAjaxForm'],
        'wrapper' => 'entity-types-container',
        'event' => 'change',
      ],
    ];

    $form['entity_types_container'] = [
      '#type' => 'container',
      '#prefix' => '<div id="entity-types-container">',
      '#suffix' => '</div>',
    ];

    if ($form_state->getValue('entity_type') && $this->getEntityTypeBundleOptions($form_state->getValue('entity_type'))) {

      $form['entity_types_container']['entity_type_bundle'] = [
        '#type' => 'select',
        '#title' => t('Choose entity bundle'),
        '#options' => $this->getEntityTypeBundleOptions($form_state->getValue('entity_type')),
        '#required' => TRUE,
      ];

    }

    $form['csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Choose CSV file'),
      '#required' => TRUE,
      '#autoupload' => TRUE,
      '#upload_validators' => ['file_validate_extensions' => ['csv']],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['view'] = [
      '#type' => 'button',
      '#value' => $this->t('View fields'),
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'getContentEntityFieldsAjaxForm'],
        'event' => 'click',
        'prevent' => 'submit',
      ],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('CSV import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  protected function getEntityTypeOptions() {
    $options = [];
    $plugin_definitions = $this->importer->getDefinitions();

    if ($plugin_definitions && is_array($plugin_definitions)) {
      foreach ($plugin_definitions as $definition) {
        $entity_type = $definition['entity_type'];
        if ($this->entityTypeManager->hasDefinition($entity_type)) {
          $entity = $this->entityTypeManager->getDefinition($entity_type);
          $options[$entity_type] = $entity->getLabel();
        }
      }
    }

    return $options;
  }

  protected function getEntityTypeBundleOptions($entity_type) {
    $entity = $this->entityTypeManager->getDefinition($entity_type);
    $options = [];

    if ($entity && $type = $entity->getBundleEntityType()) {
      $types = $this->entityTypeManager->getStorage($type)->loadMultiple();

      if ($types && is_array($types)) {
        foreach ($types as $type) {
          $options[$type->id()] = $type->label();
        }
      }
    }

    return $options;
  }

  public function getContentEntityTypesAjaxForm(array &$form, FormStateInterface $form_state) {
    return $form['entity_types_container'];
  }

  public function getContentEntityFieldsAjaxForm(array &$form, FormStateInterface $form_state) {
    $render = '';

    if ($form_state->getValue('entity_type')) {
      $fields = $this->getEntityTypeFields($form_state->getValue('entity_type'), $form_state->getUserInput()['entity_type_bundle']);

      $fields['csv'] = [];
      if ($csv = current($form_state->getValue('csv'))) {
        $fields['csv'] = $this->parser->getCsvFieldsById(current($csv));
      }

      $header = [
        'field' => $this->t('List of fields'), 
        'field_required' => $this->t('List of required fields'),
        'field_csv' => $this->t('List of CSV fields'),
      ];

      $rows = array_map(function ($field, $required, $csv) {
        return [
          'field' => $field,
          'field_required' => $required,
          'field_csv' => $csv,
        ];
      }, $fields['fields'], $fields['required'], $fields['csv']);

      $element = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
      ];

      $render = $this->renderer->render($element);
    }

    $response = new AjaxResponse();
    $response->addCommand(new RemoveCommand('.csv-importer-form table'));
    $response->addCommand(new AppendCommand('.csv-importer-form', $render));

    return $response;
  }

  protected function getEntityTypeFields($entity_type, $entity_type_bundle = NULL) {
    $fields = [];
    $entity_fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $entity_type_bundle);
    $entity_definition = $this->entityTypeManager->getDefinition($entity_type);
    foreach ($entity_fields as $machine_name => $entity_field) {
      $fields['fields'][] = $entity_field->getName();

      if ($entity_field->isRequired()) {
        $fields['required'][] = $entity_field->getName();
      }
    }

    return $fields;
  }

  protected function getContentEntityTypes($entity_type = NULL) {
    if ($entity_type) {
      return $this->entityTypeManager->getDefinition($entity_type);
    }

    $entities = $this->entityTypeManager->getDefinitions();
    return array_filter($entities, function ($entity) {
      return $entity->getGroup() === 'content';
    });
  }

  protected function getFieldsIntersection($cid, $entity_type, $entity_type_bundle = NULL, $index) {
    $fields_csv = $this->parser->getCsvFieldsById($cid);

    $fields = $this->getEntityTypeFields($entity_type, $entity_type_bundle)[$index];

    return array_intersect($fields_csv, $fields);
  }

  protected function getFieldsMissing($cid, $entity_type, $entity_type_bundle) {
    $fields_intersect = $this->getFieldsIntersection($cid, $entity_type, $entity_type_bundle, 'required');
    $fields = $this->getEntityTypeFields($entity_type, $entity_type_bundle)['required'];
    $return = [];

    foreach ($fields as $field) {
      if (!in_array($field, $fields_intersect)) {
        $return[] = $field;
      }
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_type = $form_state->getValue('entity_type');
    $entity_type_bundle = NULL;

    if (isset($form_state->getUserInput()['entity_type_bundle'])) {
      $entity_type_bundle = $form_state->getUserInput()['entity_type_bundle'];
    }

    $cid = current($form_state->getValue('csv'));
    $required = $this->getFieldsMissing($cid, $form_state->getValue('entity_type'), $entity_type_bundle, 'required');

    $this->importer->createInstance($entity_type . '_importer', [
      'cid' => $cid,
      'entity_type' => $entity_type,
      'entity_type_bundle' => $entity_type_bundle,
      'fields' => $this->getEntityTypeFields($entity_type, $entity_type_bundle)['fields'],
    ])->process();
  }

}