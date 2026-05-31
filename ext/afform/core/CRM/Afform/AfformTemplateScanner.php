<?php

/**
 * Class CRM_Afform_AfformTemplateScanner
 *
 * The AfformTemplateScanner searches the `ang` directory of extensions and `civicrm.files` for files
 * named `*.aft.*`. Each item is interpreted as a form template instance.
 *
 * @service afform_template_scanner
 */
class CRM_Afform_AfformTemplateScanner extends CRM_Afform_BaseScanner {

  const METADATA_JSON = 'aft.json';

  const METADATA_PHP = 'aft.php';

  const LAYOUT_FILE = 'aft.html';

  const FILE_REGEXP = '/\.aft\.(json|html|php)$/';

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
    return 'afformTemplateAllPaths';
  }

  public function getCoreSubfolder(): string {
    return 'afformTemplate';
  }

  public function getHookNameSuffix(): string {
    return 'afformTemplate';
  }

}
