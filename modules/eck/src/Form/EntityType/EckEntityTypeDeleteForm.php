<?php

/**
 * @file
 * Contains \Drupal\eck\Form\EntityType\EckEntityTypeDeleteForm.
 */

namespace Drupal\eck\Form\EntityType;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a confirm form for deleting the entity.
 *
 * @ingroup eck
 */
class EckEntityTypeDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete entity type %label?', array(
        '%label' => $this->entity->label(),
      )
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete entity type');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('eck.entity_type.list');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $bundles = entity_get_bundles($this->entity->id());
    if (!empty($bundles) && empty($bundles[$this->entity->id()])) {
      $warning_message = '<p>' . $this->formatPlural(count($bundles), '%type has 1 bundle. Please delete all %type bundles.', '%type has @count bundles. Please delete all %type bundles.', array('%type' => $this->entity->label())) . '</p>';
      $form['description'] = array('#markup' => $warning_message);
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $content_entity = $this->entityManager->getDefinition($this->entity->id());
    $this->entityManager->onEntityTypeDelete($content_entity);
    // Delete the entity type.
    $this->entity->delete();
    // Set a message that the entity type was deleted.
    drupal_set_message(
      t(
        'Entity type %label was deleted.',
        array(
          '%label' => $this->entity->label(),
        )
      )
    );

    // Redirect to list when completed.
    $form_state->setRedirectUrl(new Url('eck.entity_type.list'));
  }

}
