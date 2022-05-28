<?php

namespace Drupal\kaizen\Plugin\Deriver;

/**
 * Makes a kaizen formatter for each formatter config entity.
 */
class KaizenFormatterDeriver extends KaizenDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinition($derivative_id, $base_plugin_definition) {
    if (!empty($this->derivatives) && !empty($this->derivatives[$derivative_id])) {
      return $this->derivatives[$derivative_id];
    }
    $this->getDerivativeDefinitions($base_plugin_definition);
    return $this->derivatives[$derivative_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $definitions = $this->kaizenManager->getDefinitions();
    foreach ($definitions as $definition) {
      if (isset($definition['plugins']['formatter'])) {
        $formatter = $definition['plugins']['formatter'];
        $instance_id = $definition['id'];
        $formatter_definition = [
          'label' => $definition['title'],
          'field_types' => $formatter['field_types'],
          'variables' => $definition['variables'],
          'provider_source' => $definition['provider'],
          'provider_source_type' => $definition['provider_type'],
        ] + $base_plugin_definition;
        $this->derivatives[$instance_id] = $formatter_definition;
      }
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
