<?php

namespace Drupal\kaizen\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;

/**
 * Plugin implementation of the 'kaizen_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "kaizen_formatter",
 *   deriver = "Drupal\kaizen\Plugin\Deriver\KaizenFormatterDeriver"
 * )
 */
class KaizenFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'attributes' => [],
      'modifiers' => [],
      'template_settings' => [],
      'extra_classes' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $variables = $this->pluginDefinition['variables'];
    if (isset($variables['modifiers'])) {
      $elements['modifiers'] = [
        '#type' => 'select',
        '#title' => $this->t('Block modifiers'),
        '#options' => array_combine($variables['modifiers'], $variables['modifiers']),
        '#description' => $this->t('Optionally add modifier CSS classes'),
        '#default_value' => $this->getSetting('modifiers'),
        '#multiple' => TRUE,
      ];
    }
    foreach ($variables['template_settings'] as $setting_name => $setting) {
      $elements['template_settings'][$setting_name] = [
        '#type' => 'select',
        '#title' => $setting['label'],
        '#options' => array_combine($setting['modifiers'], $setting['modifiers']),
        '#description' => $setting['description'],
        '#default_value' => $this->getSetting('template_settings')[$setting_name] ? $this->getSetting('template_settings')[$setting_name] : $setting[0],
        '#multiple' => FALSE,
      ];
    }
    $elements['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('Optionally add extra CSS classes with SPACE delimeter'),
      '#default_value' => $this->getSetting('extra_classes'),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $attributes = new Attribute($this->pluginDefinition['variables']['attributes'] ? $this->pluginDefinition['variables']['attributes'] : []);
    $attributes->addClass($this->getSetting('modifiers'));
    $attributes->addClass($this->getSetting('extra_classes'));
    list(, $formatter_id) = explode(":", $this->getPluginId());
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'kaizen_' . $formatter_id,
        '#attributes' => $attributes,
        '#content' => [
          'content' => ['#markup' => $item->value],
          'template_settings' => $this->getSetting('template_settings'),
        ],

        // @todo -  Need to check at first if lib exists.
        '#attached' => [
          'library' => ['elements/' . $formatter_id],
        ],
      ];
    }
    return $elements;
  }

}
