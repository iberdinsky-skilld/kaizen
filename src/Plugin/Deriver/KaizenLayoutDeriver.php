<?php

namespace Drupal\kaizen\Plugin\Deriver;

use Drupal\Core\Layout\LayoutDefinition;

/**
 * Makes a kaizen layout for each layout config entity.
 */
class KaizenLayoutDeriver extends KaizenDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->kaizenManager->getDefinitions() as $definition) {
      if (!isset($definition['plugins']['layout'])) {
        continue;
      }
      $layout_definition = [
        'id' => $definition['id'],
        'label' => $definition['title'],
        'template' => substr(pathinfo($definition['file'], PATHINFO_BASENAME), 0, -10),
        'templatePath' => pathinfo($definition['file'], PATHINFO_DIRNAME),
        'library' => $definition['provider'] . '/' . $definition['id'],
        'icon_map' => $definition['plugins']['layout']['icon_map'],
        'regions' => $definition['plugins']['layout']['regions'],
        'provider' => $definition['provider'],
        'class' => $base_plugin_definition->getClass(),
        'variables' => $definition['variables'],
      ];
      $this->derivatives[$layout_definition['id']] = new LayoutDefinition($layout_definition);
    }

    return $this->derivatives;
  }

}
