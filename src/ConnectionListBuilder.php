<?php

/**
 * @file
 * Contains Drupal\drealty\ConnectionListBuilder
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
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    // ID.
    $row['color'] = $entity->id;

    // Label.
    $row['label'] = $this->getLabel($entity);

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = $this->t('There are no DRealty Connections available.');
    return $build;
  }

}
