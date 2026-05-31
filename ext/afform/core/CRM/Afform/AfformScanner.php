<?php

/**
 * Class CRM_Afform_AfformScanner
 *
 * The AfformScanner searches the `ang` directory of extensions and `civicrm.files` for files
 * named `*.aff.*`. Each item is interpreted as a form instance.
 *
 * @service afform_scanner
 */
class CRM_Afform_AfformScanner extends CRM_Afform_BaseScanner {

  const METADATA_JSON = 'aff.json';

  const METADATA_PHP = 'aff.php';

  const LAYOUT_FILE = 'aff.html';

  const FILE_REGEXP = '/\.aff\.(json|html|php)$/';

  public function getMetadataJsonExtension(): string {
    return self::METADATA_JSON;
  }

  public function getMetadataPhpExtension(): string {
    return self::METADATA_PHP;
  }

  public function getLayoutFileExtension(): string {
    return self::LAYOUT_FILE;
  }

  public function getFileRegexp(): string {
    return self::FILE_REGEXP;
  }

  public function getCacheKey(): string {
    return 'afformAllPaths';
  }

  public function getCoreSubfolder(): string {
    return 'afform';
  }

  public function getHookNameSuffix(): string {
    return 'afform';
  }

}
