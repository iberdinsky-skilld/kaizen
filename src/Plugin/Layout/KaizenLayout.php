<?php

namespace Drupal\kaizen\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
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

    $additional =  $this->pluginDefinition->get('additional');
    $build['#attributes'] = new Attribute($additional['variables']['attributes'] ? $additional['variables']['attributes'] : []);
    foreach ($configuration['modifiers'] as $modifier) {
      $build['#attributes']->addClass($modifier);
    }
    $build['#attributes']->addClass($configuration['extra_classes']);

    foreach ($additional['variables']['region_attributes'] as $region_name => $region) {
      $build[$region_name]['#attributes'] = new Attribute($region['attributes'] ? $region['attributes'] : []);
      $build[$region_name]['#attributes']->addClass($configuration[$region_name]['modifiers']);
    }

    foreach ($additional['variables']['template_settings'] as $setting_name => $setting) {
      $build['template_settings'][$setting_name] = $configuration['template_settings'][$setting_name];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $additional =  $this->pluginDefinition->get('additional');
    $configuration = [
      'modifiers' => [],
      'extra_classes' => '',
    ];
    foreach ($additional['variables']['region_attributes'] as $region_name => $region) {
      if ($region['modifiers']) {
        $configuration[$region_name]['modifiers'] = [];
      }
    }

    foreach ($additional['variables']['template_settings'] as $setting_name => $setting) {
      $configuration['template_settings'][$setting_name] = '';
    }

    return parent::defaultConfiguration() + $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $configuration = $this->getConfiguration();
    $additional =  $this->pluginDefinition->get('additional');
    if ($additional['variables']) {
      if ($additional['variables']['modifiers']) {
        $form['modifiers'] = [
          '#type' => 'select',
          '#title' => $this->t('Block modifiers'),
          '#options' => array_combine($additional['variables']['modifiers'], $additional['variables']['modifiers']),
          '#description' => $this->t('Optionally add modifier CSS classes'),
          '#default_value' => $configuration['modifiers'],
          '#multiple' => TRUE,
        ];
      }

      $region_labels = $this->pluginDefinition->getRegionLabels();

      foreach ($additional['variables']['region_attributes'] as $region_name => $region) {
        if ($region['modifiers']) {
          $form[$region_name]['modifiers'] = [
            '#type' => 'select',
            '#title' => $region_labels[$region_name] . ' ' . $this->t('element modifiers'),
            '#options' => array_combine($region['modifiers'], $region['modifiers']),
            '#description' => $this->t('Optionally add modifier CSS classes for') . ' ' . $region_labels[$region_name],
            '#default_value' => $configuration[$region_name]['modifiers'],
            '#multiple' => TRUE,
          ];
        }
      }

      foreach ($additional['variables']['template_settings'] as $setting_name => $setting) {
        $form['template_settings'][$setting_name] = [
          '#type' => 'select',
          '#title' => $setting['label'],
          '#options' => array_combine($setting['modifiers'], $setting['modifiers']),
          '#description' => $this->t('Select custom setting'),
          '#default_value' => $configuration['template_settings'][$setting_name],
          '#multiple' => FALSE,
        ];
      }
    }

    $form['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('Optionally add extra CSS classes with SPACE delimeter'),
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
    $this->configuration['modifiers'] = $form_state->getValue('modifiers');
    $this->configuration['extra_classes'] = $form_state->getValue('extra_classes');
    $additional =  $this->pluginDefinition->get('additional');
    foreach ($additional['variables']['region_attributes'] as $region_name => $region) {
      if ($form[$region_name]['modifiers']) {
        $this->configuration[$region_name]['modifiers'] = $form_state->getValue([$region_name, 'modifiers']);
      }
    }

    foreach ($additional['variables']['template_settings'] as $setting_name => $setting) {
      if ($form['template_settings'][$setting_name]) {
        $this->configuration['template_settings'][$setting_name] = $form_state->getValue(['template_settings', $setting_name]);
      }
    }

    parent::submitConfigurationForm($form, $form_state);
  }
}
