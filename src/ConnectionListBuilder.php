<?php

/**
 * @file
 * Contains \Drupal\drealty\ConnectionListBuilder
 */

namespace Drupal\drealty;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;


class ConnectionListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Name');
    $header['status'] = $this->t('Active');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    // ID.
    $row['id'] = $entity->id;

    // Label.
    $row['label'] = $this->getLabel($entity);

    // Status.
    $row['status'] = $entity->status ? t('Yes') : t('No');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = $this->t('There are no connections available.');
    return $build;
  }

}
