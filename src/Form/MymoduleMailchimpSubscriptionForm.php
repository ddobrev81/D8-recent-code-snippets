<?php

namespace Drupal\Mymodule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Subscribe to a MailChimp list.
 */
class MymoduleMailchimpSubscriptionForm extends FormBase {

  /**
   * The ID for this form.
   *
   * @var string
   */
  private $formId = 'mymodule_mailchimp_subscription_form';

  /**
   * Description text for this form.
   *
   * @var string
   */
  private $descriptionText = '';

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return $this->formId;
  }

  /**
   * Set the form id.
   *
   * @param mixed $formId
   *   The form id.
   */
  public function setFormId($formId) {
    $this->formId = $formId;
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['#attributes'] = ['class' => ['mymodule-mailchimp-subscribtion-form']];
    $form['description'] = [
      '#markup' => '<p class="newsletter__text">' . $this->descriptionText . '</p>',
    ];

    $form['email'] = [
      '#required' => TRUE,
      '#default_value' => '',
      '#title' => '',
      '#placeholder' => t('Email adresse'),
      '#element_validate' => ['mymodule_validate_email'],
      '#attributes' => [
        'class' => ['newsletter__email'],
      ],
      '#type' => 'email',
      '#prefix' => '<div class="newsletter__form">',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => ['newsletter__button'],
      ],
      '#sufix' => '</div>',
    ];
    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mailchimp_lists = $this->getMailchimpLists();
    if (!$mailchimp_lists) {
      drupal_set_message(t('There was a problem with your newsletter signup.'), 'warning');
    }
    $email = $form_state->getValue('email');
    $mergevars = ['EMAIL' => $email];

    foreach ($mailchimp_lists as $list_id) {
      $result = mailchimp_subscribe($list_id['value'], $email, $mergevars, [], FALSE);
    }
    if (empty($result)) {
      drupal_set_message(t('There was a problem with your newsletter signup.'), 'warning');
    }

  }

  /**
   * Set the descriptionText.
   *
   * @param string $descriptionText
   *   The text to set.
   */
  public function setDescriptionText(string $descriptionText) {
    if (!empty($descriptionText)) {
      $this->descriptionText = $descriptionText;
    }
  }

}
