<?php

/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

namespace Civi\Api4\Action\SearchDisplay;

use Civi\Api4\Generic\Result;

/**
 * @inheritDoc
 */
class Get extends \Civi\Api4\Generic\DAOGetAction {

  /**
   * @param \Civi\Api4\Generic\Result $result
   */
  protected function getObjects(Result $result) {
    parent::getObjects($result);

    // Check permissions before presenting fields as editable
    if ($this->checkPermissions) {
      foreach ($result as $idx => $display) {
        foreach ($display['settings']['columns'] ?? [] as $order => $column) {
          if (!empty($column['editable'])) {
            if (!$this->checkEditPermission($column['editable']['entity'])) {
              unset($result[$idx]['settings']['columns'][$order]['editable']);
            }
          }
        }
      }
    }
  }

  /**
   * @param string $entity
   * @return bool
   */
  private function checkEditPermission(string $entity): bool {
    switch ($entity) {
      // FIXME: This errs on the side of being overly restrictive for contacts, as the user may not have "edit all contacts"
      // permission, but be granted edit rights to a set of contacts via ACLs
      case 'Contact':
        return \CRM_Core_Permission::check('edit all contacts');

      // FIXME: Other entities have the opposite problem, and may be under-restrictive, as the user may have permission
      // to access the "Update" api but be limited in the ones they may update
      default:
        return (bool) civicrm_api4($entity, 'getActions', ['where' => [['name', '=', 'update']]])->count();
    }
  }

}
