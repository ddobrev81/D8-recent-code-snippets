<?php

namespace Drupal\Mymodule\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Mymodule\Form\MymoduleMailchimpSubscriptionForm;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a Mailchimp Subscription block.
 *
 * @Block(
 *   id = "Mymodule_mailchimp_block",
 *   admin_label = @Translation("Mailchimp Subscription block"),
 *   category = "Mailchimp"
 * )
 */
class MymoduleMailchimpBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = new MymoduleMailchimpSubscriptionForm();
    $form_id = 'mymodule_mailchimp_subscription_form';
    $form->setFormId($form_id);
    $globalConfig = \Drupal::config('mailchimp_descriptions_per_list.settings');
    $config = $this->getConfiguration();
    $mailchimpLists = mymodule_get_mailchimp_lists();
    $listId = $mailchimpLists[0]['value'];

    $title = '';
    if (!empty($config['label'])) {
      $title = $config['label'];
    }
    else {
      $titleText = $globalConfig->get('list_id-' . $listId . '.title');
      if (strlen($titleText) > 0) {
        $title = $titleText;
      }
    }
    $this->setConfigurationValue('label', $title);

    $description = '';
    if (!empty($config['description'])) {
      $description = $config['description'];
    }
    else {
      $descriptionText = $globalConfig->get('list_id-' . $listId . '.description');
      if (strlen($descriptionText) > 0) {
        $description = $descriptionText;
      }
    }
    $form->setDescriptionText($description);

    $content = \Drupal::formBuilder()->getForm($form);
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description text'),
      '#default_value' => isset($config['description']) ? $config['description'] : '',
    ];

    $form['background_image'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Background Image'),
      '#target_type' => 'media',
      '#validate_reference' => TRUE,
      '#maxlength' => '60',
      '#description' => $this->t('Choose a media image to upload.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['description'] = $values['description'];
    $this->configuration['background_image'] = $values['background_image'];
  }

  /**
   * {@inheritdoc}
   *
   * Using this to override #required setting for the 'label' field.
   *
   * @see \Drupal\Core\Block\BlockBase::blockForm()
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $definition = $this->getPluginDefinition();
    $form['provider'] = [
      '#type' => 'value',
      '#value' => $definition['provider'],
    ];

    $form['admin_label'] = [
      '#type' => 'item',
      '#title' => $this->t('Block description'),
      '#plain_text' => $definition['admin_label'],
    ];
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $this->label(),
      '#required' => FALSE,
    ];
    $form['label_display'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display title'),
      '#default_value' => ($this->configuration['label_display'] === static::BLOCK_LABEL_VISIBLE),
      '#return_value' => static::BLOCK_LABEL_VISIBLE,
    ];

    // Add context mapping UI form elements.
    $contexts = $form_state->getTemporaryValue('gathered_contexts') ?: [];
    $form['context_mapping'] = $this->addContextAssignmentElement($this, $contexts);
    // Add plugin-specific settings for this block type.
    $form += $this->blockForm($form, $form_state);
    return $form;
  }

  /**
   * Add the background image setting to the configuration of the block.
   *
   * @return array
   *   An associative array with the default configuration.
   */
  protected function baseConfigurationDefaults() {
    return parent::baseConfigurationDefaults() + [
      'background_image' => '',
    ];
  }

}
