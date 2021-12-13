<?php

namespace Drupal\kaizen\Plugin\Deriver;

use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\kaizen\KaizenManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class KaizenDeriverBase.
 */
class KaizenDeriverBase extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Kaizen manager.
   *
   * @var \Drupal\kaizen\KaizenManager
   */
  protected $kaizenManager;

  /**
   * Constructs a new KaizenDeriverBase object.
   *
   * @param \Drupal\kaizen\KaizenManager $kaizenManager
   *   Kaizen manager.
   */
  public function __construct(KaizenManager $kaizenManager) {
    $this->kaizenManager = $kaizenManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('kaizen.manager'),
    );
  }

}
