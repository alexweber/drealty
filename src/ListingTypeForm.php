<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingTypeForm.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\EntityForm;
use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for listing type forms.
 */
class ListingTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = String::checkPlain($this->t('Add listing type'));
    }
    elseif ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit %label listing type', array('%label' => $type->label()));
    }

    $drealty_settings = $type->getModuleSettings('drealty');
    // Prepare listing options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($drealty_settings['options']));
    $drealty_settings['options'] = array_combine($keys, $keys);
    $form['name'] = array(
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->name,
      '#description' => t('The human-readable name of this listing type. This text will be displayed as part of the list on the <em>Add listing</em> page. It is recommended that this name begin with a capital letter and contain only letters, numbers, and spaces. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['type'] = array(
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => $type->isLocked(),
      '#machine_name' => array(
        'exists' => 'drealty_listing_type_load',
        'source' => array('name'),
      ),
      '#description' => t('A unique machine-readable name for this listing type. It must only contain lowercase letters, numbers, and underscores. This name will be used for constructing the URL of the %listing-add page, in which underscores will be converted into hyphens.', array(
        '%listing-add' => t('Add listing'),
      )),
    );

    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
      '#attached' => array(
        'library' => array('node/drupal.content_types'),
      ),
    );

    $form['description_tab'] = array(
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => t('Description & Help'),
    );

    $form['description_tab']['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->description,
      '#description' => t('Describe this listing type. The text will be displayed on the <em>Add listing</em> page.'),
    );

    $form['description_tab']['help']  = array(
      '#type' => 'textarea',
      '#title' => t('Explanation or submission guidelines'),
      '#default_value' => $type->help,
      '#description' => t('This text will be displayed at the top of the page when creating or editing listings of this type.'),
    );

    $form['workflow'] = array(
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'additional_settings',
    );
    $form['workflow']['options'] = array('#type' => 'checkboxes',
      '#title' => t('Default options'),
      '#parents' => array('settings', 'drealty', 'options'),
      '#default_value' => $drealty_settings['options'],
      '#options' => array(
        'status' => t('Published'),
        'promote' => t('Promoted to front page'),
        'sticky' => t('Sticky at top of lists'),
        'revision' => t('Create new revision'),
      ),
      '#description' => t('Users with the <em>Administer DRealty Listings</em> permission will be able to override these options.'),
    );

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = array(
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      );

      $language_configuration = language_get_default_configuration('drealty_listing', $type->id());
      $form['language']['language_configuration'] = array(
        '#type' => 'language_configuration',
        '#entity_information' => array(
          'entity_type' => 'drealty_listing',
          'bundle' => $type->id(),
        ),
        '#default_value' => $language_configuration,
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save listing type');
    $actions['delete']['#value'] = t('Delete listing type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    $id = trim($form_state['values']['type']);
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", array('%invalid' => $id)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;
    $type->type = trim($type->id());
    $type->name = trim($type->name);

    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The listing type %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The listing type %name has been added.', $t_args));
      $context = array_merge($t_args, array('link' => l(t('View'), 'admin/drealty/listing-types')));
      $this->logger('drealty')->notice('Added listing type %name.', $context);
    }

    $form_state->setRedirect('drealty.listing_type_list');
  }

}
