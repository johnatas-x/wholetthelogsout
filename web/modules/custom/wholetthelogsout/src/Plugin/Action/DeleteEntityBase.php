<?php

namespace Drupal\wholetthelogsout\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Action\Plugin\Action\EntityActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a base class for entity delete actions.
 */
abstract class DeleteEntityBase extends EntityActionBase {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function executeMultiple(array $entities): void {
    $this->entityTypeManager
      ->getStorage($this->getPluginDefinition()['type'])
      ->delete($entities);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function execute(object $object = NULL): void {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function access(mixed $object, AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    return ($object instanceof ActionInterface) ? $object->access('delete', $account, $return_as_object) : FALSE;
  }

}
