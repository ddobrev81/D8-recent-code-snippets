<?php

namespace Drupal\Mymodule\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the 'Related Events condition' condition.
 *
 * @Condition(
 *   id = "related_event_condition",
 *   label = @Translation("Event: Related Event Visibility"),
 *   context = {
 *     "node" = @ContextDefinition(
 *        "entity:node",
 *        required = TRUE ,
 *        label = @Translation("node")
 *     )
 *   }
 * )
 */
class RelatedEventCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

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
    $form['related_eventsActive'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Show Related Events block'),
      '#default_value' => $this->configuration['related_eventsActive'],
      '#description'   => $this->t('Enable this block when the related_events field on the node is active.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['related_eventsActive'] = $form_state->getValue('related_eventsActive');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['related_eventsActive' => 0] + parent::defaultConfiguration();
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    $status = $this->getContextValue('related_eventsActive') ? t('enabled') : t('disabled');
    return t(
      'The node has related_events block @status.',
      ['@status' => $status]);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['related_eventsActive']) && !$this->isNegated()) {
      return TRUE;
    }
    if ($node = $this->getContextValue('node')) {
      if ($node->hasField('field_show_related_events') && $node->field_show_related_events->value) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
