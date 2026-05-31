<?php

namespace Civi\Api4\Utils;

use Civi\Afform\StringVisitor;
use Civi\Api4\TranslationSource;
use Civi\Afform\Utils;

/**
 * Class AfformSaveTrait.
 *
 * @package Civi\Api4\Action\Afform
 */
trait AfformSaveTrait {

  use AfformFormatTrait;

  /**
   *
   */
  protected function writeRecord($item) {
    $entityName = $this->getEntityName();
    /** @var \CRM_Afform_BaseScanner $scanner */
    $scanner = \Civi::service($entityName === 'AfformTemplate' ? 'afform_template_scanner' : 'afform_scanner');

    // If no name given, create a unique name based on the title
    $orig = [];
    $this->checkNameForAfform($item, $orig, $scanner);

    // Check if updating or creating
    if (!$orig) {
      $item['created_id'] = \CRM_Core_Session::getLoggedInContactID();
    }

    // Dispatch hook_civicrm_pre
    \CRM_Utils_Hook::pre($orig ? 'edit' : 'create', $entityName, NULL, $item);

    $item = $this->filterFields($item, $entityName);

    // Create or update layout HTML.
    if (isset($item['layout'])) {
      $layoutPath = $scanner->createSiteLocalPath($item['name'], $scanner->getLayoutFileExtension());
      \CRM_Utils_File::createDir(dirname($layoutPath));
      $html = $this->convertInputToHtml($item['layout']);

      // Are we multilingual.
      if ($entityName === 'Afform' && \CRM_Core_I18n::isMultiLingual()) {
        self::saveTranslations($item, $html, $entityName);
      }
      file_put_contents($layoutPath, $html);
    }

    $meta = $item + (array) $orig;
    unset($meta['layout'], $meta['name']);
    if (isset($meta['permission']) && is_string($meta['permission'])) {
      $meta['permission'] = explode(',', $meta['permission']);
    }
    if (!empty($meta)) {
      $metaPath = $scanner->createSiteLocalPath($item['name'], $scanner->getMetadataJsonExtension());
      \CRM_Utils_File::createDir(dirname($metaPath));
      // Add eof newline to make files git-friendly.
      file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    // We may have changed list of files covered by the cache.
    _afform_clear();

    // If the dashlet or navigation setting changed, managed entities must be reconciled.
    if (Utils::shouldReconcileManaged($item, $orig ?? [])) {
      \CRM_Core_ManagedEntities::singleton()->reconcile(\CRM_Afform_ExtensionUtil::LONG_NAME);
    }

    if (Utils::shouldClearMenuCache($item, $orig ?? [])) {
      \CRM_Core_Menu::store();
    }

    $item['module_name'] = _afform_angular_module_name($item['name'], 'camel');
    $item['directive_name'] = _afform_angular_module_name($item['name'], 'dash');

    $result = $meta + $item;

    // Dispatch hook_civicrm_post
    $nullValue = NULL;
    \CRM_Utils_Hook::post($orig ? 'edit' : 'create', $entityName, NULL, $nullValue, $result);

    return $result;
  }

  /**
   * Filter the content of $params to only have supported fields.
   *
   * @param array $params
   * @param string $entityName
   * @return array
   */
  protected function filterFields($params, $entityName) {
    $result = [];
    $fields = \civicrm_api4($entityName, 'getFields', ['checkPermissions' => FALSE, 'action' => 'create'])->indexBy('name');
    foreach ($fields as $fieldName => $field) {
      if (array_key_exists($fieldName, $params)) {
        $result[$fieldName] = $params[$fieldName];

        if (($field['data_type'] ?? NULL) === 'Boolean' && !is_bool($params[$fieldName])) {
          $result[$fieldName] = \CRM_Utils_String::strtobool($params[$fieldName]);
        }
      }
    }
    return $result;
  }

  /**
   * @param array $item The item being processed.
   * @param array $orig The existing record if already created.
   * @param \CRM_Afform_BaseScanner $scanner
   *
   * @return void
   * @throws \CRM_Core_Exception
   */
  protected function checkNameForAfform(&$item, &$orig, $scanner) {
    $entityName = $this->getEntityName();

    if (empty($item['name'])) {
      $prefix = $entityName === 'Afform' ? 'af' . ($item['type'] ?? '') : 'afTemplate';
      $item['name'] = _afform_angular_module_name($prefix . '-' . \CRM_Utils_String::munge($item['title'], '-'));
      $suffix = '';
      while (
        file_exists($scanner->createSiteLocalPath($item['name'] . $suffix, $scanner->getMetadataJsonExtension()))
        || file_exists($scanner->createSiteLocalPath($item['name'] . $suffix, $scanner->getLayoutFileExtension()))
      ) {
        $suffix++;
      }
      $item['name'] .= $suffix;
      $orig = NULL;
    }
    elseif (!preg_match('/^[a-zA-Z][-_a-zA-Z0-9]*$/', $item['name'])) {
      $actionName = $this->getActionName();
      throw new \CRM_Core_Exception("$entityName.$actionName: name should begin with a letter and only contain alphanumerics underscores and dashes.");
    }
    else {
      // Fetch existing metadata
      $fields = \civicrm_api4($entityName, 'getFields', ['checkPermissions' => FALSE, 'action' => 'create'])->column('name');
      unset($fields[array_search('layout', $fields)]);
      $orig = \civicrm_api4($entityName, 'get', [
        'checkPermissions' => FALSE,
        'where' => [['name', '=', $item['name']]],
        'select' => $fields,
      ])->first();
    }
  }

  /**
   * Save Translation Strings from Form/Template to database
   *
   * @param array $form
   * @param string $html
   * @param string $entityName
   */
  protected static function saveTranslations($form, $html, $entityName = 'Afform') {
    $strings = StringVisitor::extractStrings($form, $html);

    // Save the strings.
    if (!empty($strings)) {
      $entity = strtolower($entityName);
      // Create context hash
      $context_key = \CRM_Core_BAO_TranslationSource::createGuid(':::' . $entity);

      // Build the array for the table.
      $records = [];
      foreach ($strings as $value) {
        $source_key = \CRM_Core_BAO_TranslationSource::createGuid($value);
        $records[] = ['source' => $value, 'source_key' => $source_key, 'context_key' => $context_key, 'entity' => $entity];
      }
      TranslationSource::save(FALSE)
        ->setRecords($records)
        ->setMatch(['source_key'])
        ->execute();
    }
  }

}
