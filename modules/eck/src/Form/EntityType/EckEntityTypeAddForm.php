<?php

/**
 * @file
 * Contains \Drupal\eck\Form\EntityType\EckEntityTypeAddForm.
 */

namespace Drupal\eck\Form\EntityType;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the add form for ECK Entity Type.
 *
 * @ingroup eck
 */
class EckEntityTypeAddForm extends EckEntityTypeFormBase {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    // Change the submit button value.
    $actions['submit']['#value'] = $this->t('Create entity type');

    return $actions;
  }

}
