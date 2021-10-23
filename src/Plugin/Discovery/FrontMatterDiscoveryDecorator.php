<?php

namespace Drupal\kaizen\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;

/**
 * Enables FrontMatter discovery for plugin definitions.
 *
 * You should normally extend this class to add validation for the values in the
 * FrontMatter data or to restrict use of the class or derivatives keys.
 */
class FrontMatterDiscoveryDecorator extends FrontMatterDiscovery {

  /**
   * The Discovery object being decorated.
   *
   * @var \Drupal\Component\Plugin\Discovery\DiscoveryInterface
   */
  protected $decorated;

  /**
   * Constructs a FrontMatterDiscoveryDecorator object.
   *
   * @param \Drupal\Component\Plugin\Discovery\DiscoveryInterface $decorated
   *   The discovery object that is being decorated.
   * @param array $directories
   *   An array of directories to scan.
   * @param string $file_cache_key_suffix
   *   The file cache key suffix. This should be unique for each class that
   *   extends this abstract class.
   * @param array $array_position
   *   Depth of plugin in FrontMatter array.
   * @param string $file_filter
   *   The file name suffix to use for discovery; for instance, 'test' will
   *   become 'MODULE.test.yml'.
   */
  public function __construct(DiscoveryInterface $decorated, array $directories, string $file_cache_key_suffix, array $array_position, string $file_filter) {
    parent::__construct($directories, $file_cache_key_suffix, $array_position, $file_filter);

    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return parent::getDefinitions() + $this->decorated->getDefinitions();
  }

  /**
   * Passes through all unknown calls onto the decorated object.
   *
   * @param string $method
   *   The method to call on the decorated plugin discovery.
   * @param array $args
   *   The arguments to send to the method.
   *
   * @return mixed
   *   The method result.
   */
  public function __call($method, array $args) {
    return call_user_func_array([$this->decorated, $method], $args);
  }

}
