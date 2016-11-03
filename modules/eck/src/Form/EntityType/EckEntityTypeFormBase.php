<?php

/**
 * @file
 * Contains \Drupal\eck\Form\EntityType\EckEntityTypeFormBase.
 */

namespace Drupal\eck\Form\EntityType;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class EckEntityTypeFormBase.
 *
 * @ingroup eck
 */
class EckEntityTypeFormBase extends EntityForm {

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * Construct the EckEntityTypeFormBase.
   *
   * @param QueryFactory $query_factory
   *   The query factory.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->entityQueryFactory = $query_factory;
  }

  /**
   * Factory method for EckEntityTypeFormBase.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.query'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the from from the base class.
    $form = parent::buildForm($form, $form_state);

    $eck_entity_type = $this->entity;

    // Build the form.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $eck_entity_type->label(),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $eck_entity_type->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ),
      '#disabled' => !$eck_entity_type->isNew(),
    );

    $form['base_fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Available base fields'),
    ];

    $config = \Drupal::config('eck.eck_entity_type.' . $eck_entity_type->id());
    foreach (['created', 'changed', 'uid', 'title'] as $field) {
      $title = $field === 'uid' ? 'author' : $field;

      $form['base_fields'][$field] = [
        '#type' => 'checkbox',
        '#title' => t('%field field', ['%field' => ucfirst($title)]),
        '#default_value' => $config->get($field),
      ];
    }

    return $form;
  }

  /**
   * Checks for an existing ECK entity type.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element, FormStateInterface $form_state) {
    // Use the query factory to build a new event entity query.
    $query = $this->entityQueryFactory->get('eck_entity_type');

    // Query the entity ID to see if its in use.
    $result = $query->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();

    // We don't need to return the ID, only if it exists or not.
    return (bool) $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actions from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // The entity object is already populated with the values from the form.
    $status = $this->entity->save();

    if ($status == SAVED_UPDATED) {
      drupal_set_message(
        $this->t(
          'Entity type %label has been updated.',
          array('%label' => $this->entity->label())
        )
      );
    }
    else {
      drupal_set_message(
        $this->t(
          'Entity type %label has been added.',
          array('%label' => $this->entity->label())
        )
      );
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('eck.entity_type.list');
  }

}
