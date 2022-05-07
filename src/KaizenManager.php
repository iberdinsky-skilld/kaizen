<?php

namespace Drupal\kaizen;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\kaizen\Discovery\KaizenYamlDiscovery;

/**
 * Class KaizenManager.
 */
class KaizenManager {

  /**
   * Root path of application.
   *
   * @var string
   */
  protected $root;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * KaizenManager constructor.
   *
   * @param string $root
   *   Root path of application.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   Theme handler.
   */
  public function __construct($root, ModuleHandlerInterface $moduleHandler, ThemeHandlerInterface $themeHandler) {
    $this->root = $root;
    $this->moduleHandler = $moduleHandler;
    $this->themeHandler = $themeHandler;
  }

  /**
   * Gets front matter definitions.
   *
   * @return array|mixed[]
   *   List of frontmatter definitions.
   */
  public function getDefinitions() {
    $directories = $this->getExtensionsDirectories();
    $discovery = new KaizenYamlDiscovery($directories, 'kaizen_discovery');
    return $discovery->getDefinitions();
  }

  /**
   * Gets array of enabled exntesions.
   *
   * @return array
   *   Array of enabled extensions.
   */
  protected function getExtensionsDirectories() {
    $list = array_merge($this->moduleHandler->getModuleDirectories(), $this->themeHandler->getThemeDirectories());
    foreach ($list as &$dir) {
      $dir = str_replace($this->root . '/', "", $dir);
    }
    return $list;
  }

}
