<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\Entity\DecoupledAuthUser.
 */

namespace Drupal\decoupled_auth\Entity;

use Drupal\decoupled_auth\DecoupledAuthUserInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\Entity\User;

/**
 * Defines the decoupled user authentication user entity class.
 */
class DecoupledAuthUser extends User implements DecoupledAuthUserInterface {

  /**
   * Flag to indicate whether this user has decoupled authentication.
   *
   * @var bool
   */
  protected $decoupled = FALSE;

  /**
   * {@inheritdoc}
   */
  public function postCreate(EntityStorageInterface $storage) {
    parent::postCreate($storage);
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    /** @var $entities DecoupledAuthUser[] */
    parent::postLoad($storage, $entities);
    foreach ($entities as $entity) {
      $entity->calculateDecoupled();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDecoupled() {
    $this->decoupled = $this->name->value === NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isCoupled() {
    return !$this->decoupled;
  }

  /**
   * {@inheritdoc}
   */
  public function isDecoupled() {
    return $this->decoupled;
  }

  /**
   * {@inheritdoc}
   */
  public function couple() {
    $this->decoupled = FALSE;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function decouple() {
    $this->decoupled = TRUE;
    $this->name = NULL;
    $this->pass = NULL;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Make name not required at a database level and swap the constraint.
    $constraints = $fields['name']->getConstraints();
    unset($constraints['UserName']);
    unset($constraints['NotNull']);
    $constraints['DecoupledAuthUserName'] = array();
    $fields['name']
      ->setRequired(FALSE)
      ->setConstraints($constraints);

    // Make adjustments to mail.
    $constraints = $fields['mail']->getConstraints();

    // Swap to our own unique constraint for mail.
    unset($constraints['UserMailUnique']);
    $constraints['DecoupledAuthUserMailUnique'] = array();

    // Swap to our own required constraint for mail.
    unset($constraints['UserMailRequired']);
    $constraints['DecoupledAuthUserMailRequired'] = array();

    $fields['mail']->setConstraints($constraints);

    return $fields;
  }

}
