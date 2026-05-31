<?php

namespace Civi\Api4\Utils;

use Civi\Afform\Utils;
use Civi\Api4\Generic\Result;

/**
 * Trait AfformRevertTrait
 *
 * Shared logic for the Revert action of Afform and AfformTemplate.
 */
trait AfformRevertTrait {

  /**
   * @var bool
   */
  private $flushManaged = FALSE;

  /**
   * @var bool
   */
  private $flushMenu = FALSE;

  /**
   * Revert every record, and flush caches at the end.
   */
  protected function processBatch(Result $result, array $items) {
    parent::processBatch($result, $items);

    // We may have changed list of files covered by the cache.
    _afform_clear();

    if ($this->flushManaged) {
      \CRM_Core_ManagedEntities::singleton()->reconcile(\CRM_Afform_ExtensionUtil::LONG_NAME);
    }
    if ($this->flushMenu) {
      \CRM_Core_Menu::store();
    }
  }

  /**
   * Revert (delete) a record.
   */
  protected function doTask($item) {
    $entityName = $this->getEntityName();
    // Dispatch hook_civicrm_pre
    \CRM_Utils_Hook::pre('delete', $entityName, NULL, $item);

    /** @var \CRM_Afform_BaseScanner $scanner */
    $scanner = \Civi::service($entityName === 'AfformTemplate' ? 'afform_template_scanner' : 'afform_scanner');
    $files = [
      $scanner->getMetadataJsonExtension(),
      $scanner->getLayoutFileExtension(),
    ];

    foreach ($files as $file) {
      $metaPath = $scanner->createSiteLocalPath($item['name'], $file);
      if (file_exists($metaPath)) {
        if (!@unlink($metaPath)) {
          throw new \CRM_Core_Exception("Failed to remove $entityName overrides in $file");
        }
      }
    }

    $original = (array) $scanner->getMeta($item['name']);

    // If the dashlet setting changed, managed entities must be reconciled
    if (Utils::shouldReconcileManaged($item, $original)) {
      $this->flushManaged = TRUE;
    }

    // If the server_route changed, reset menu cache
    if (Utils::shouldClearMenuCache($item, $original)) {
      $this->flushMenu = TRUE;
    }

    // Dispatch hook_civicrm_post
    $nullValue = NULL;
    \CRM_Utils_Hook::post('delete', $entityName, NULL, $nullValue, $item);

    return $item;
  }

  /**
   * Adds extra return params so caches can be conditionally flushed.
   *
   * @return string[]
   */
  protected function getSelect() {
    return ['name', 'title', 'placement', 'server_route', 'created_id'];
  }

}
