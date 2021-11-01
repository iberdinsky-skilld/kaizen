<?php

namespace Drupal\kaizen\Plugin\Deriver;

use Drupal\Component\Plugin\Definition\PluginDefinition;
use Drupal\kaizen\Plugin\Deriver\KaizenDeriverBase;
use Drupal\kaizen\Plugin\Field\FieldFormatter\KaizenFormatter;
use Drupal\kaizen\Plugin\Discovery\FrontMatterDiscovery;

/**
 * Makes a kaizen formatter for each formatter config entity.
 */
class KaizenFormatterDeriver extends KaizenDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $discovery = new FrontMatterDiscovery($this->themeHandler->getThemeDirectories(), 'formatters', ['plugins', 'formatters'], '/\.frontmatter\.html\.twig$/i');
    $discovery
      ->addTranslatableProperty('label');

    $formatter_definitions = $discovery->getDefinitions();
    foreach ($formatter_definitions as $formatter_definition) {
      // Theme providers ignored in DefaultPluginManager.
      $formatter_definition['provider'] = 'kaizen';
      $formatter_definition['class'] = KaizenFormatter::class;
      $this->derivatives[$formatter_definition['id']] = $formatter_definition;
    }
    return $this->derivatives;
  }

}
