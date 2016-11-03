<?php

/**
 * @file
 * Contains \Drupal\eck\Form\EntityBundle\EckEntityBundleForm.
 */

namespace Drupal\eck\Form\EntityBundle;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\eck\Entity\EckEntityBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for ECK entity bundle forms.
 *
 * @ingroup eck
 */
class EckEntityBundleForm extends EntityForm {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs the EckEntityBundleForm object.
   *
   * @param EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity_type_id = $this->entity->getEntityType()->getBundleOf();
    $type = $this->entity;
    $entity = $this->entityManager->getStorage($entity_type_id)->create([
        'type' => $this->operation == 'add' ? $type->uuid(): $type->id()]
    );
    $type_label = $entity->getEntityType()->getLabel();

    $form['name'] = array(
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->name,
      '#description' => t(
        'The human-readable name of this entity bundle. This text will be displayed as part of the list on the <em>Add @type content</em> page. This name must be unique.',
      array('@type' => $type_label)),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['type'] = array(
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => $type->isLocked(),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
        'source' => array('name'),
      ),
      '#description' => t(
        'A unique machine-readable name for this entity type bundle. It must only contain lowercase letters, numbers, and underscores. This name will be used for constructing the URL of the Add %type content page, in which underscores will be converted into hyphens.',
        array(
          '%type' => $type_label,
        )
      ),
    );

    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->description,
      '#description' => t(
        'Describe this entity type bundle. The text will be displayed on the <em>Add @type content</em> page.',
        array('@type' => $type_label)
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save bundle');
    $actions['delete']['#value'] = t('Delete bundle');

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    $id = trim($form_state->getValue('type'));
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName(
        'type',
        $this->t(
          "Invalid machine-readable name. Enter a name other than %invalid.",
          array('%invalid' => $id)
        )
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;
    $type->type = trim($type->id());
    $type->name = trim($type->name);

    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(
        t('The entity bundle %name has been updated.', $t_args)
      );
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The entity bundle %name has been added.', $t_args));
      $context = array_merge(
        $t_args,
        array('link' => $this->l(t('View'), new Url('eck.entity.' . $type->getEntityType()->getBundleOf() . '_type.list')))
      );
      $this->logger($this->entity->getEntityTypeId())->notice('Added entity bundle %name.', $context);
    }

    $form_state->setRedirect(
      'eck.entity.' . $type->getEntityType()->getBundleOf() . '_type.list'
    );
  }

  /**
   * Checks for an existing ECK bundle.
   *
   * @param string $type
   *   The bundle type.
   * @param array $element
   *   The form element.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($type, array $element, FormStateInterface $form_state) {
    return EckEntityBundle::load($type);
  }

}
