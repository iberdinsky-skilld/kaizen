<?php

namespace Drupal\kaizen\Plugin\Discovery;

use Drupal\Component\Discovery\DiscoveryException;
use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use Drupal\Component\FrontMatter\FrontMatter;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides discovery for files containing FrontMatter.
 */
class FrontMatterDiscovery implements DiscoveryInterface {

  use DiscoveryTrait;

  /**
   * Plugin may appear not on top level of FrontMatter.
   *
   * @var array
   *
   * @code
   * plugins:
   *   layouts:
   *     layout_onecolumn:
   * @endcode
   */
  protected $arrayPosition = [];

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
   * @param array $array_position
   *   Depth of plugin in FrontMatter array.
   * @param string $file_filter
   *   (optional) Regular expression pattern to filter file names. Defaults to
   *   a pattern that finds files with extension .frontmatter.html.twig.
   */
  public function __construct(array $directories, string $file_cache_key_suffix, array $array_position = [], string $file_filter = '/\.frontmatter\.html\.twig$/i') {
    $this->arrayPosition = $array_position;
    $this->directories = $directories;
    $this->fileFilter = $file_filter;
    $this->fileCacheKeySuffix = $file_cache_key_suffix;
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
      foreach ($files_list as $file => $list) {
        foreach ($list as $id => $definition) {
          // Add TranslatableMarkup.
          foreach ($this->translatableProperties as $property => $context_key) {
            if (isset($definition[$property])) {
              $options = [];
              // Move the t() context from the definition to the translation
              // wrapper.
              if ($context_key && isset($definition[$context_key])) {
                $options['context'] = $definition[$context_key];
                unset($definition[$context_key]);
              }
              $definition[$property] = new TranslatableMarkup($definition[$property], [], $options);
            }
          }
          // Add ID and provider.
          $definitions[$id] = $definition + [
            'provider' => $provider,
            'id' => $id,
            'file' => $file,
          ];
        }
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
    // $provider_by_files = array_flip($files);

    // $file_cache = FileCacheFactory::get('frontmatter_discovery:' . $this->fileCacheKeySuffix);
    // // Try to load from the file cache first.
    // foreach ($file_cache->getMultiple($files) as $file => $data) {
    //   $all[$provider_by_files[$file]] = $data;
    //   unset($provider_by_files[$file]);
    // }

    // If there are files left that were not returned from the cache, load and
    // parse them now. This list was flipped above and is keyed by filename.
    if ($files) {
      foreach ($files as $file => $provider) {
        try {
          $front_matter = FrontMatter::create(file_get_contents($file), Yaml::class)->getData();
        }
        catch (InvalidDataTypeException $e) {
          throw new DiscoveryException(sprintf('Malformed FrontMatter in frontmatter "%s": %s.', $file, $e->getMessage()));
        }
        // If a file is empty or its contents are commented out, return an empty
        // array instead of NULL for type consistency.
        if ($front_matter) {

          // catch variables
          $front_matter_variables = false;
          if ($front_matter['variables']) {
            $front_matter_variables = $front_matter['variables'];
          }

          // If plugin defined deeper in FrontMatter tree.
          for ($i = 0; $i < count($this->arrayPosition); $i++) {
            if(isset($front_matter[$this->arrayPosition[$i]])) {
              $front_matter = $front_matter[$this->arrayPosition[$i]];
            } else {
              $front_matter = false;
            }
          }
          if ($front_matter) {

            // To know what file provides frontmatter.
            foreach ($front_matter as $plugin => $list) {
              if($front_matter_variables) {
                $front_matter[$plugin]['variables'] = $front_matter_variables;
              }
            }
            $all[$provider][$file] = $front_matter;
            // $file_cache->set($file, $front_matter);
          }
        }
      }
    }
    return $all;
  }

  /**
   * Returns an array of file paths, keyed by provider.
   *
   * @return array
   *   An array of file paths keyed by provider.
   */
  protected function findFiles() {
    $file_list = [];
    foreach ($this->directories as $provider => $directories) {
      $directories = (array) $directories;
      foreach ($directories as $directory) {
        if (is_dir($directory)) {
          /** @var \SplFileInfo $fileInfo */
          foreach ($this->getDirectoryIterator($directory, $this->fileFilter) as $fileInfo) {
            $file_list[$fileInfo->getPathname()] = $provider;
          }
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
