<?php

/**
 * @file
 * Contains Drupal\drealty\Controller\RetsResourceListBuilder.
 */

namespace Drupal\drealty\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a list builder for RETS Resources.
 */
class RetsResourceListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Internal ID');
    $header['label'] = $this->t('System name');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['label'] = $this->getLabel($entity);

    return $row + parent::buildRow($entity);
  }
}
