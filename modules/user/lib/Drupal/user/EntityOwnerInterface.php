<?php

/**
 * @file
 * Contains \Drupal\user\EntityOwnerInterface.
 */

namespace Drupal\user;

/**
 * Defines a common interface for entities that have an owner.
 *
 * An owner is someone who has primary control over an entity, similar to
 * owners in Unix file system access. This may or may not be the entity's
 * original author. The owner may also have less permissions than other users,
 * such as administrators.
 */
interface EntityOwnerInterface {

  /**
   * Returns the entity owner's user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The owner user entity.
   */
  public function getOwner();

  /**
   * Sets the entity owner's user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The owner user entity.
   *
   * @return self
   *   The called entity.
   */
  public function setOwner(UserInterface $account);

  /**
   * Returns the entity owner's user ID.
   *
   * @return int
   *   The owner user ID.
   */
  public function getOwnerId();

  /**
   * Sets the entity owner's user ID.
   *
   * @param int $uid
   *   The owner user id.
   *
   * @return self
   *   The called entity.
   */
  public function setOwnerId($uid);

}
