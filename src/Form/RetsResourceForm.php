<?php

/**
 * @file
 * Contains Drupal\drealty\Form\RetsResourceForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

class RetsResourceForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $drealty_resource = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('System Name'),
      '#maxlength' => 255,
      '#default_value' => $drealty_resource->label(),
      '#description' => $this->t("RETS Resource system name."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $drealty_resource->id(),
      '#machine_name' => array(
        'exists' => 'drealty_resource_load',
      ),
      '#disabled' => !$drealty_resource->isNew(),
    );

    // @TODO Additional form elements for additional custom properties.

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $drealty_resource = $this->entity;
    $status = $drealty_resource->save();

    if ($status) {
      drupal_set_message($this->t('Saved RETS Resource %label.', array(
        '%label' => $drealty_resource->label(),
      )));
    }
    else {
      drupal_set_message($this->t('RETS Resource %label was not saved.', array(
        '%label' => $drealty_resource->label(),
      )));
    }
    $form_state->setRedirect('drealty_resource.list');
  }
}
