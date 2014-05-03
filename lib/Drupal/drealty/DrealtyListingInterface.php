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
 * Provides an interface defining a node entity.
 */
interface DrealtyListingInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the node type.
   *
   * @return string
   *   The node type.
   */
  public function getType();

  /**
   * Returns the node title.
   *
   * @return string
   *   Title of the node.
   */
  public function getTitle();

  /**
   * Sets the node title.
   *
   * @param string $title
   *   The node title.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called node entity.
   */
  public function setTitle($title);

  /**
   * Returns the node creation timestamp.
   *
   * @return int
   *   Creation timestamp of the node.
   */
  public function getCreatedTime();

  /**
   * Sets the node creation timestamp.
   *
   * @param int $timestamp
   *   The node creation timestamp.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called node entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the node published status indicator.
   *
   * Unpublished nodes are only visible to their authors and to administrators.
   *
   * @return bool
   *   TRUE if the node is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a node..
   *
   * @param bool $published
   *   TRUE to set this node to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called node entity.
   */
  public function setPublished($published);

  /**
   * Returns the node revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the node revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called node entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Returns the node revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the node revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\drealty\DrealtyListingInterface
   *   The called node entity.
   */
  public function setRevisionAuthorId($uid);

  /**
   * Prepares the langcode for a node.
   *
   * @return string
   *   The langcode for this node.
   */
  public function prepareLangcode();

}
