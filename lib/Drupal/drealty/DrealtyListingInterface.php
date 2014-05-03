<?php

/**
 * @file
 * Contains \Drupal\drealty\DrealtyListingInterface.
 */

namespace Drupal\drealty;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a drealty listing entity.
 */
interface DrealtyListingInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the drealty listing type.
   *
   * @return string
   *   The drealty listing type.
   */
  public function getType();

  /**
   * Returns the drealty listing title.
   *
   * @return string
   *   Title of the drealty listing.
   */
  public function getTitle();

  /**
   * Sets the drealty listing title.
   *
   * @param string $title
   *   The drealty listing title.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called drealty listing entity.
   */
  public function setTitle($title);

  /**
   * Returns the drealty listing creation timestamp.
   *
   * @return int
   *   Creation timestamp of the drealty listing.
   */
  public function getCreatedTime();

  /**
   * Sets the drealty listing creation timestamp.
   *
   * @param int $timestamp
   *   The drealty listing creation timestamp.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called drealty listing entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the drealty listing published status indicator.
   *
   * Unpublished drealty listings are only visible to their authors and to
   * administrators.
   *
   * @return bool
   *   TRUE if the drealty listing is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a drealty listing.
   *
   * @param bool $published
   *   TRUE to set this drealty listing to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called drealty listing entity.
   */
  public function setPublished($published);

  /**
   * Returns the drealty listing revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the drealty listing revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called drealty listing entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Returns the drealty listing revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the drealty listing revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called drealty listing entity.
   */
  public function setRevisionAuthorId($uid);

  /**
   * Prepares the langcode for a drealty listing.
   *
   * @return string
   *   The langcode for this drealty listing.
   */
  public function prepareLangcode();

}
