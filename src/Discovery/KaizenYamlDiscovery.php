<?php

namespace Drupal\kaizen\Discovery;

use Drupal\Component\Discovery\DiscoveryException;
use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Serialization\Yaml;

/**
 * Provides discovery for files containing FrontMatter.
 */
class KaizenYamlDiscovery implements DiscoveryInterface {

  use DiscoveryTrait;

  /**
   * An array of directories to scan, keyed by the provider.
   *
   * @var array
   */
  protected $directories = [];

  /**
   * The suffix for the file cache key.
   *
   * @var string
   */
  protected $fileCacheKeySuffix;

  /**
   * The regular expression filter used to find relevant files.
   *
   * @var string
   */
  protected $fileFilter;

  /**
   * The key contained in the discovered data that identifies it.
   *
   * @var string
   */
  protected $idKey;

  /**
   * Contains an array of translatable properties passed along to t().
   *
   * @var array
   *
   * @see \Drupal\kaizen\Plugin\Discovery\FrontMatterDiscovery::addTranslatableProperty()
   */
  protected $translatableProperties = [];

  /**
   * Constructs a FrontMatterDiscovery object.
   *
   * @param array $directories
   *   An array of directories to scan, keyed by the provider.
   * @param string $file_cache_key_suffix
   *   The file cache key suffix. This should be unique for each class that
   *   extends this abstract class.
   * @param string $file_filter
   *   (optional) Regular expression pattern to filter file names. Defaults to
   *   a pattern that finds files with extension .kaizen_component.yml.
   * @param string $key
   *   (optional) The key contained in the discovered data that identifies it.
   *   Defaults to 'id'.*.
   */
  public function __construct(array $directories, string $file_cache_key_suffix, string $file_filter = '/\.kaizen_component\.yml$/i', $key = 'id') {
    $this->directories = $directories;
    $this->fileFilter = $file_filter;
    $this->fileCacheKeySuffix = $file_cache_key_suffix;
    $this->idKey = $key;
  }

  /**
   * Set one of the FrontMatter values as being translatable.
   *
   * @param string $value_key
   *   The key corresponding to the value in the FrontMatter that contains a
   *   translatable string.
   * @param string $context_key
   *   (Optional) the translation context for the value specified by the
   *   $value_key.
   *
   * @return $this
   */
  public function addTranslatableProperty($value_key, $context_key = '') {
    $this->translatableProperties[$value_key] = $context_key;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $plugins = $this->findAll();
    // Flatten definitions array.
    $definitions = [];
    foreach ($plugins as $provider => $files_list) {
      foreach ($files_list as $file => $definition) {
        // Add ID and provider.
        $definitions[$definition['id']] = $definition + [
          'provider' => $provider,
          'file' => $file,
        ];
      }
    }
    return $definitions;
  }

  /**
   * Returns an array of discoverable items.
   *
   * @return array
   *   An array of discovered data keyed by provider.
   *
   * @throws \Drupal\Component\Discovery\DiscoveryException
   *   Exception thrown if there is a problem during discovery.
   */
  protected function findAll() {
    $all = [];

    $files = $this->findFiles();

    $file_cache = FileCacheFactory::get('yaml_discovery:' . $this->fileCacheKeySuffix);

    // Try to load from the file cache first.
    foreach ($file_cache->getMultiple(array_keys($files)) as $file => $data) {
      $all[$files[$file]][$this->getIdentifier($file, $data)] = $data;
      unset($files[$file]);
    }

    // If there are files left that were not returned from the cache, load and
    // parse them now. This list was flipped above and is keyed by filename.
    if (!empty($files)) {
      foreach ($files as $file => $provider) {
        try {
          $data = Yaml::decode(file_get_contents($file)) ?: [];
        }
        catch (InvalidDataTypeException $e) {
          throw new DiscoveryException("The $file contains invalid YAML", 0, $e);
        }
        // If a file is empty or its contents are commented out, return an empty
        // array instead of NULL for type consistency.
        list($filename) = explode(".", basename($file));
        $data['id'] = $provider . "_" . $filename;
        $data['name'] = $filename;
        $data['provider_path'] = $this->directories[$provider]['directory'];
        $data['provider_type'] = $this->directories[$provider]['extension_type'];
        $data['component_path'] = pathinfo($file, PATHINFO_DIRNAME);

        // Catch variables.
        $data_variables = FALSE;
        if ($data['variables']) {
          $data_variables = $data['variables'];
        }

        // To know what file provides frontmatter.
        if ($data_variables) {
          foreach ($data['plugins'] as &$list) {
            $list['variables'] = $data_variables;
          }
        }
        $all[$provider][$file] = $data;
        $file_cache->set($file, $data);
      }
    }
    return $all;
  }

  /**
   * Gets the identifier from the data.
   *
   * @param string $file
   *   The filename.
   * @param array $data
   *   The data from the YAML file.
   *
   * @return string
   *   The identifier from the data.
   */
  protected function getIdentifier($file, array $data) {
    if (!isset($data[$this->idKey])) {
      throw new DiscoveryException("The $file contains no data in the identifier key '{$this->idKey}'");
    }
    return $data[$this->idKey];
  }

  /**
   * Returns an array of file paths, keyed by provider.
   *
   * @return array
   *   An array of file paths keyed by provider.
   */
  protected function findFiles() {
    $file_list = [];
    foreach ($this->directories as $provider => $directory_info) {
      $directory = $directory_info['directory'];
      if (is_dir($directory)) {
        /** @var \SplFileInfo $fileInfo */
        foreach ($this->getDirectoryIterator($directory) as $fileInfo) {
          $file_list[$fileInfo->getPathname()] = $provider;
        }
      }
    }
    return $file_list;
  }

  /**
   * Gets an iterator to recursive loop over files in the provided directory.
   *
   * @param string $directory
   *   The directory to scan.
   *
   * @return \Traversable
   *   An \Traversable object or array where the values are \SplFileInfo
   *   objects.
   */
  protected function getDirectoryIterator(string $directory) {
    return new \RegexIterator(
      new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::LEAVES_ONLY
      ), $this->fileFilter
    );
  }

}
