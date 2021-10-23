<?php

namespace Drupal\kaizen\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'kaizen_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "kaizen_formatter",
 *   label = @Translation("Kaizen Formatter"),
 *   field_types = {
 *     "text_with_summary"
 *   },
 * )
 */
class KaizenFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      // The text value has no text format assigned to it, so the user input
      // should equal the output, including newlines.
      $elements[$delta] = [
        '#type' => 'inline_template',
        '#template' => '{{ value|nl2br }}',
        '#context' => ['value' => $item->value],
      ];
    }

    return $elements;
  }

}
