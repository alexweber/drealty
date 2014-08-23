<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ConnectionDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form that handles the removal of DRealty Connection entities.
 */
class ConnectionDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this connection: @name?', array('@name' => $this->entity->name));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('drealty.connection_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    $this->entity->delete();

    $this->logger('user')->notice('Deleted connection: %connection', array('%connection' => $this->entity->label()));
    drupal_set_message($this->t('Connection %connection was deleted', array('%connection' => $this->entity->label())));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
