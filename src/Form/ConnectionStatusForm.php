<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ConnectionDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;

/**
 * Form that handles the removal of DRealty Connection entities.
 */
class ConnectionStatusForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $connection = $this->entity;

    // Display connection info.
    $header = array(t('Property'), t('Value'));
    $rows = array(
      array(
        t('Name'),
        $connection->name,
      ),
      array(
        t('Internal ID'),
        $connection->id,
      ),
      array(
        t('Enabled'),
        $connection->status ? t('Yes') : t('No'),
      ),
      array(
        t('RETS Version'),
        $connection->rets,
      ),
      array(
        t('Login URL'),
        $connection->url,
      ),
      array(
        t('Login Username'),
        $connection->username,
      ),
      array(
        t('Login Password'),
        $connection->password,
      ),
      array(
        t('User Agent String'),
        $connection->ua_string,
      ),
      array(
        t('User Agent Password'),
        $connection->ua_password,
      ),
      array(
        t('Use Interealty Authentication'),
        $connection->use_interealty_auth ? t('Yes') : t('No'),
      ),
      array(
        t('Force Basic Authentication'),
        $connection->force_basic_auth ? t('Yes') : t('No'),
      ),
      array(
        t('Use Compression'),
        $connection->use_compression ? t('Yes') : t('No'),
      ),
      array(
        t('Disable Encoding Fix'),
        $connection->disable_encoding_fix ? t('Yes') : t('No'),
      ),
      array(
        t('Debug Mode'),
        $connection->debug_mode ? t('Enabled') : t('Disabled'),
      ),
      array(
        t('"No mapping" Mode'),
        $connection->nomap_mode ? t('Enabled') : t('Disabled'),
      ),
    );

    $form['connection'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = array();

    $actions['back'] = array(
      '#type' => 'link',
      '#title' => $this->t('Back to connections'),
      '#attributes' => array(
//        'class' => array('button', 'button--danger'),
      ),
    );

    $route_info = new Url('drealty.connection_list');
    $actions['back'] += $route_info->toRenderArray();

    return $actions;
  }

}
