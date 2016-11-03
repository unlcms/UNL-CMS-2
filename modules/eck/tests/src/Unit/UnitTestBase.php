<?php

namespace Drupal\Tests\eck\Unit;

use Drupal\Core\Entity\EntityInterface;
use Drupal\eck\Entity\EckEntityType;
use Drupal\Tests\UnitTestCase;


abstract class UnitTestBase extends UnitTestCase {

  protected $entities;

  private $services;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->entities = [];
    $this->prepareContainer();
    $this->registerServiceWithContainerMock('current_user', $this->getNewUserMock());
    $this->registerServiceWithContainerMock('entity.manager', $this->getEntityManagerMock());
  }

  /**
   * Prepares a mocked service container.
   */
  private function prepareContainer() {
    $container_class = 'Drupal\Core\DependencyInjection\Container';
    $methods = get_class_methods($container_class);
    $container = $this->getMockBuilder($container_class)
      ->disableOriginalConstructor()
      ->setMethods($methods)
      ->getMock();
    \Drupal::setContainer($container);

    $container->method('get')->willReturnCallback([
      $this,
      'containerMockGetServiceCallback'
    ]);
  }

  private function getEntityManagerMock() {
    $entity_storage = $this->getMockForAbstractClass('\Drupal\Core\Entity\EntityStorageInterface');
    $entity_storage->method('loadMultiple')->willReturnCallback([$this, 'entityStorageLoadMultiple']);
    $entity_storage->method('load')->willReturnCallback([$this, 'entityStorageLoadMultiple']);
    $definition = $this->getMockForAbstractClass('\Drupal\Core\Entity\EntityTypeInterface');

    $entity_manager = $this->getMockForAbstractClass('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->method('getStorage')->willReturn($entity_storage);
    $entity_manager->method('getDefinition')->willReturn($definition);

    return $entity_manager;
  }

  /**
   * Registers a (mocked) service with the mocked service container.
   *
   * @param string $service_id
   *   The service id.
   * @param mixed $service
   *   The service to be returned when the service_id is requested from the
   *   container.
   */
  protected function registerServiceWithContainerMock($service_id, $service) {
    $this->services[$service_id] = $service;
  }

  /**
   * Creates and returns a mocked user.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The mocked user.
   */
  private function getNewUserMock() {
    $user_mock = $this->getMockForAbstractClass('\Drupal\Core\Session\AccountProxyInterface', ['id']);
    $user_mock->method('id')->willReturn(1);
    return $user_mock;
  }

  /**
   * Callback for the get method on the mocked service container.
   *
   * @param string $service_id
   *   The service identifier being called.
   *
   * @return mixed
   *   A (mocked) service class if one has been set for the given service id
   *   or NULL.
   */
  public function containerMockGetServiceCallback($service_id) {
    if (isset($this->services[$service_id])) {
      return $this->services[$service_id];
    }
    return NULL;
  }

  protected function createLanguageManagerMock() {
    $current_language_mock = $this->getMockForAbstractClass('\Drupal\Core\Language\LanguageInterface');
    $current_language_mock->method('id')->willReturn('en');

    $mock = $this->getMockForAbstractClass('\Drupal\Core\Language\LanguageManagerInterface');
    $mock->method('getCurrentLanguage')->willReturn($current_language_mock);

    return $mock;
  }

  public function entityStorageLoadMultiple($id = '') {
    if (!empty($id)) {
      return $this->entities[$id];
    }
    else {
      return $this->entities;
    }
  }

  protected function addEntityToStorage(EntityInterface $entity) {
    $this->entities[$entity->id()] = $entity;
  }

  /**
   * @param string $entity_type_id
   * @return \Drupal\eck\Entity\EckEntityType
   */
  protected function createEckEntityType($entity_type_id, array $values = []) {
    $values = $values + [
      'label' => ucfirst($entity_type_id),
      'id' => $entity_type_id,
    ];
    return new EckEntityType($values, $entity_type_id);
  }

}
