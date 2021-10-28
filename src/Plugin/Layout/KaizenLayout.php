<?php

namespace Drupal\kaizen\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * A Basic configurable Layout.
 *
 * @Layout(
 *   id = "kaizen_layout",
 *   deriver = "Drupal\kaizen\Plugin\Deriver\KaizenLayoutDeriver"
 * )
 */
class KaizenLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);

    $configuration = $this->configuration;
    $build['#attributes'] = $configuration['attributes'];
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      if (array_key_exists($region_name, $configuration['region_attributes'])) {
        $build[$region_name]['#attributes'] = $configuration['region_attributes'][$region_name]['attributes'];
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // dd($this->getPluginDefinition(), $this->getConfiguration(), $this->getPluginDefinition()->get('variables'));
    $configuration = [];
    if ($additional = $this->getPluginDefinition()->get('variables')) {

    }
    return parent::defaultConfiguration() + [
      'extra_classes' => '',
      'attributes' => $additional['attributes'],
      'region_attributes' => $additional['region_attributes'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $form['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#default_value' => $configuration['extra_classes'],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // any additional form validation that is required
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['extra_classes'] = $form_state->getValue('extra_classes');
  }
}
