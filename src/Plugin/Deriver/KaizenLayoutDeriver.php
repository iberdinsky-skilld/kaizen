<?php

namespace Drupal\kaizen\Plugin\Deriver;

use Drupal\Core\Layout\LayoutDefinition;
use Drupal\kaizen\Plugin\Deriver\KaizenDeriverBase;
use Drupal\kaizen\Plugin\Layout\KaizenLayout;
use Drupal\kaizen\Plugin\Discovery\FrontMatterDiscovery;

/**
 * Makes a kaizen layout for each layout config entity.
 */
class KaizenLayoutDeriver extends KaizenDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $discovery = new FrontMatterDiscovery($this->themeHandler->getThemeDirectories(), 'layouts', ['plugins', 'layouts'], '/\.frontmatter\.html\.twig$/i');
    $discovery
      ->addTranslatableProperty('label')
      ->addTranslatableProperty('description')
      ->addTranslatableProperty('category');


    $layout_definitions = $discovery->getDefinitions();
    foreach ($layout_definitions as $layout_definition) {
      $layout_definition['class'] = KaizenLayout::class;
      $this->derivatives[$layout_definition['id']] = new LayoutDefinition($layout_definition);
    }
    return $this->derivatives;
  }

}
