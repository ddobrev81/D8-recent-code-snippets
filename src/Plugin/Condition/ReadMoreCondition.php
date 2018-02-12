<?php

namespace Drupal\Mymodule\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the 'ReadMore condition' condition.
 *
 * @Condition(
 *   id = "read_more_condition",
 *   label = @Translation("Article: Read More Visibility"),
 *   context = {
 *     "node" = @ContextDefinition(
 *        "entity:node",
 *        required = TRUE ,
 *        label = @Translation("node")
 *     )
 *   }
 * )
 */
class ReadMoreCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['read_moreActive'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Show Read More block'),
      '#default_value' => $this->configuration['read_moreActive'],
      '#description'   => $this->t('Enable this block when the read_more field on the node is active.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['read_moreActive'] = $form_state->getValue('read_moreActive');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['read_moreActive' => 0] + parent::defaultConfiguration();
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    $status = $this->getContextValue('read_moreActive') ? t('enabled') : t('disabled');
    return t(
      'The node has read_more block @status.',
      ['@status' => $status]);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['read_moreActive']) && !$this->isNegated()) {
      return TRUE;
    }
    if ($node = $this->getContextValue('node')) {
      if ($node->hasField('field_show_read_more') && $node->field_show_read_more->value) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
