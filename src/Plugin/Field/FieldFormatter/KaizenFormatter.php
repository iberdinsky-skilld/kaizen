<?php

namespace Drupal\kaizen\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'inline_template',
        '#template' => 'test{{ value|nl2br }}',
        '#context' => ['value' => $item->value],
      ];
    }

    return $elements;
  }
}
