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
      // Files are layout templates itself so we need to catch file path and
      // prepare LayoutDefinition variables.
      // TODO: Find good way to get
      // FROM "/Users/ivan/Sites/experiments/drupal-original/core/themes/bartik/packages/components/molecules/bartik-block.frontmatter.html.twig"
      // TO packages/components/molecules and bartik-block.frontmatter
      // WITH known provider 'bartik'
      $file_absolute_path = $layout_definition['file'];
      $theme = $this->themeHandler->getTheme($layout_definition['provider']);
      $theme_path = $theme->getPath();
      $file_info = pathinfo(substr($file_absolute_path, strpos($file_absolute_path, $theme_path) + strlen($theme_path) + 1));

      // Cut .html.twig file extensiions.
      $layout_definition['template'] = substr($file_info['basename'], 0, -10);
      $layout_definition['path'] = $file_info['dirname'];

      $layout_definition['library'] = str_replace('COMPONENT', $layout_definition['provider'], $layout_definition['library']);

      // ENDTODO

      $layout_definition['class'] = KaizenLayout::class;

      $this->derivatives[$layout_definition['id']] = new LayoutDefinition($layout_definition);
    }
    return $this->derivatives;
  }

}
