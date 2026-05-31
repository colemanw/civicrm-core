<?php

/**
 * Class CRM_Afform_BaseScanner
 *
 * Base class for scanning ang directories and civicrm.files for form/template definitions.
 */
abstract class CRM_Afform_BaseScanner extends \Civi\Core\Service\AutoService {

  const DEFAULT_REQUIRES = 'afCore';

  /**
   * @var CRM_Utils_Cache_Interface
   */
  protected $cache;

  abstract public function getMetadataJsonExtension(): string;

  abstract public function getMetadataPhpExtension(): string;

  abstract public function getLayoutFileExtension(): string;

  abstract public function getFileRegexp(): string;

  abstract public function getCacheKey(): string;

  abstract public function getCoreSubfolder(): string;

  abstract public function getHookNameSuffix(): string;

  /**
   * CRM_Afform_BaseScanner constructor.
   *
   * @param \CRM_Utils_Cache_Interface $cache
   */
  public function __construct($cache = NULL) {
    $this->cache = $cache ?: Civi::cache('long');
  }

  /**
   * Get a list of all forms/templates and their file paths.
   *
   * @return array
   */
  public function findFilePaths(): array {
    if ($this->isUseCachedPaths()) {
      $formPaths = $this->cache->get($this->getCacheKey());
      if ($formPaths !== NULL) {
        return $formPaths;
      }
    }

    $basePaths = [];
    $formPaths = [];

    $mapper = CRM_Extension_System::singleton()->getMapper();
    foreach ($mapper->getModules() as $module) {
      try {
        if ($module->is_active) {
          $basePaths[] = [
            'weight' => 0,
            'path' => dirname($mapper->keyToPath($module->name)) . DIRECTORY_SEPARATOR . 'ang',
            'module' => $module->name,
          ];
        }
      }
      catch (CRM_Extension_Exception_MissingException $e) {
        // If the extension is missing skip & continue.
      }
    }

    // Scan core ang/[coreSubfolder] directory
    $basePaths[] = [
      'weight' => 100,
      'path' => Civi::paths()->getPath('[civicrm.root]/ang/' . $this->getCoreSubfolder()),
      'module' => 'civicrm',
    ];
    // Scan uploads/files directory
    $basePaths[] = [
      'weight' => 200,
      'path' => $this->getSiteLocalPath(),
      'module' => '',
    ];

    $event = \Civi\Core\Event\GenericHookEvent::create(['paths' => &$basePaths]);
    \Civi::dispatcher()->dispatch('civi.' . $this->getHookNameSuffix() . '.searchPaths', $event);

    usort($basePaths, fn($a, $b) =>
      $a['weight'] === $b['weight']
        ? $b['module'] <=> $a['module']
        : $b['weight'] <=> $a['weight']
    );
    foreach ($basePaths as $basePath) {
      $this->appendFilePaths($formPaths, $basePath['path'], $basePath['module']);
    }

    if ($this->isUseCachedPaths()) {
      $this->cache->set($this->getCacheKey(), $formPaths);
    }
    return $formPaths;
  }

  /**
   * Is the cache to be used.
   *
   * @return bool
   */
  private function isUseCachedPaths(): bool {
    return !CRM_Core_Config::singleton()->debug;
  }

  /**
   * Get the absolute path to the given file.
   *
   * @param string $formName
   * @param string $suffix
   * @return string|NULL
   */
  public function findFilePath(string $formName, string $suffix): ?string {
    $paths = $this->findFilePaths();

    if (isset($paths[$formName])) {
      foreach ($paths[$formName] as $path) {
        if (file_exists($path . '.' . $suffix)) {
          return $path . '.' . $suffix;
        }
      }
    }

    return NULL;
  }

  /**
   * Determine the path where we can write our own customized/overridden
   * version of a file.
   *
   * @param string $formName
   * @param string $fileType
   * @return string
   */
  public function createSiteLocalPath(string $formName, string $fileType): string {
    return $this->getSiteLocalPath() . DIRECTORY_SEPARATOR . $formName . '.' . $fileType;
  }

  public function clear(): void {
    $this->cache->delete($this->getCacheKey());
  }

  /**
   * Get metadata and optionally the layout for a file-based form/template.
   *
   * @param string $name
   * @param bool $getLayout
   * @return array|null
   */
  public function getMeta(string $name, bool $getLayout = FALSE): ?array {
    $defn = [];
    $mtime = NULL;

    $jsonFile = $this->findFilePath($name, $this->getMetadataJsonExtension());
    $htmlFile = $this->findFilePath($name, $this->getLayoutFileExtension());

    if ($jsonFile !== NULL) {
      $defn = json_decode(file_get_contents($jsonFile), 1);
      $mtime = filemtime($jsonFile);
    }
    else {
      $phpFile = $this->findFilePath($name, $this->getMetadataPhpExtension());
      if ($phpFile !== NULL) {
        $defn = include $phpFile;
        $mtime = filemtime($phpFile);
      }
    }
    if ($htmlFile !== NULL) {
      $mtime = max($mtime, filemtime($htmlFile));
      if ($getLayout) {
        $defn['layout'] = file_get_contents($htmlFile);
      }
    }
    elseif (!$defn) {
      return NULL;
    }
    $defn['name'] = $name;
    $defn['modified_date'] = date('Y-m-d H:i:s', $mtime);
    return $defn;
  }

  /**
   * Adds base_module, has_local & has_base to metadata record
   *
   * @param array $record
   */
  public function addComputedFields(array &$record) {
    $name = $record['name'];
    $allPaths = $this->findFilePaths()[$name] ?? [];
    $record['has_local'] = isset($allPaths['']);
    if (!isset($record['has_base'])) {
      $record['base_module'] = \CRM_Utils_Array::first(array_filter(array_keys($allPaths)));
      $record['has_base'] = !empty($record['base_module']);
    }
  }

  /**
   * Get the effective metadata for all file-based items.
   *
   * @return array
   */
  public function getMetas(): array {
    $result = [];
    foreach (array_keys($this->findFilePaths()) as $name) {
      $result[$name] = $this->getMeta($name);
    }
    return $result;
  }

  /**
   * @param array[] $formPaths
   * @param string $parent
   * @param string $module
   */
  private function appendFilePaths(array &$formPaths, string $parent, string $module) {
    $files = preg_grep($this->getFileRegexp(), (array) glob("$parent/*"));

    foreach ($files as $file) {
      $fileBase = preg_replace($this->getFileRegexp(), '', $file);
      $name = basename($fileBase);
      $formPaths[$name][$module] = $fileBase;
    }
  }

  /**
   * Get the path where site-local form customizations are stored.
   *
   * @return string
   */
  public function getSiteLocalPath(): string {
    return Civi::paths()->getPath('[civicrm.files]/ang');
  }

}
