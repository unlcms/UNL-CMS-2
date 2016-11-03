<?php

/**
 * @file
 * Contains \Drupal\eck\Form\EntityBundle\EckEntityBundleDeleteConfirm.
 */

namespace Drupal\eck\Form\EntityBundle;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for ECK entity bundle deletion.
 *
 * @ingroup eck
 */
class EckEntityBundleDeleteConfirm extends EntityConfirmFormBase {

  /**
   * The query factory to create entity queries.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new EckEntityBundleDeleteConfirm object.
   *
   * @param QueryFactory $query_factory
   *   The query factory.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the entity bundle %type?', array('%type' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('eck.entity.' . $this->entity->getEntityTypeId() . '.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Check if any entity of this type already exists.
    $content_number = $this->queryFactory->get($this->entity->getEntityType()->getBundleOf())
      ->condition('type', $this->entity->id())
      ->count()
      ->execute();
    if (!empty($content_number)) {
      $warning_message = '<p>' . $this->formatPlural($content_number, '%type is used by 1 entity on your site. You can not remove this entity type until you have removed all of the %type entities.', '%type is used by @count entities on your site. You may not remove %type until you have removed all of the %type entities.', array('%type' => $this->entity->label())) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = array('#markup' => $warning_message);
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $t_args = array('%name' => $this->entity->label());
    drupal_set_message(t('The entity bundle %name has been deleted', $t_args));
    $this->logger($this->entity->getEntityType()->getBundleOf())->notice('Delete entity type %name', $t_args);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
