<?php

namespace Civi\Api4;

use Civi\Api4\Generic\AutocompleteAction;
use Civi\Api4\Generic\BasicGetFieldsAction;
use CRM_Afform_ExtensionUtil as E;

/**
 * User-configurable form templates.
 *
 * This API provides actions for managing Form Templates.
 *
 * @see https://lab.civicrm.org/extensions/afform
 * @labelField title
 * @iconField type:icon
 * @since 5.31
 * @package Civi\Api4
 */
class AfformTemplate extends Generic\AbstractEntity {

  /**
   * @param bool $checkPermissions
   * @return Action\AfformTemplate\Get
   */
  public static function get($checkPermissions = TRUE) {
    return (new Action\AfformTemplate\Get('AfformTemplate', __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\AfformTemplate\Create
   */
  public static function create($checkPermissions = TRUE) {
    return (new Action\AfformTemplate\Create('AfformTemplate', __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\AfformTemplate\Update
   */
  public static function update($checkPermissions = TRUE) {
    return (new Action\AfformTemplate\Update('AfformTemplate', __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\AfformTemplate\Save
   */
  public static function save($checkPermissions = TRUE) {
    return (new Action\AfformTemplate\Save('AfformTemplate', __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return \Civi\Api4\Generic\AutocompleteAction
   */
  public static function autocomplete($checkPermissions = TRUE) {
    return (new AutocompleteAction('AfformTemplate', __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\AfformTemplate\Revert
   */
  public static function revert($checkPermissions = TRUE) {
    return (new Action\AfformTemplate\Revert('AfformTemplate', __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Generic\BasicGetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new Generic\BasicGetFieldsAction('AfformTemplate', __FUNCTION__, function(BasicGetFieldsAction $self) {
      $fields = [
        [
          'name' => 'name',
          'title' => E::ts('Name'),
          'input_type' => 'Text',
        ],
        [
          'name' => 'type',
          'title' => E::ts('Type'),
          'pseudoconstant' => ['optionGroupName' => 'afform_type'],
          'default_value' => 'form',
          'input_type' => 'Select',
        ],
        [
          'name' => 'requires',
          'title' => E::ts('Requires'),
          'data_type' => 'Array',
          'description' => 'Angular module dependencies; calculated at runtime',
        ],
        [
          'name' => 'entity_type',
          'title' => E::ts('Block Entity'),
          'description' => 'Block used for this entity type',
        ],
        [
          'name' => 'join_entity',
          'title' => E::ts('Join Entity'),
          'description' => 'Used for blocks that join a sub-entity (e.g. Emails for a Contact)',
        ],
        [
          'name' => 'title',
          'title' => E::ts('Title'),
          'required' => $self->getAction() === 'create',
          'input_type' => 'Text',
        ],
        [
          'name' => 'description',
          'title' => E::ts('Description'),
          'input_type' => 'Text',
        ],
        [
          'name' => 'tags',
          'title' => E::ts('Tags'),
          'pseudoconstant' => [
            'callback' => [\Civi\Api4\Utils\AfformTags::class, 'getTagOptions'],
          ],
          'suffixes' => [
            'name',
            'label',
            'color',
            'description',
          ],
          'data_type' => 'Array',
          'input_type' => 'Select',
        ],
        [
          'name' => 'icon',
          'title' => E::ts('Icon'),
          'description' => 'Icon shown in the placement',
        ],
        [
          'name' => 'layout',
          'title' => E::ts('Layout'),
          'data_type' => 'Array',
          'description' => 'HTML form layout; format is controlled by layoutFormat param',
        ],
        [
          'name' => 'modified_date',
          'title' => E::ts('Date Modified'),
          'data_type' => 'Timestamp',
          'readonly' => TRUE,
        ],
        [
          'name' => 'created_id',
          'title' => ts('Created By Contact ID'),
          'data_type' => 'Integer',
          'fk_entity' => 'Contact',
          'fk_column' => 'id',
          'input_type' => 'EntityRef',
          'label' => ts('Created By'),
          'default_value' => NULL,
          'readonly' => TRUE,
          'required' => FALSE,
        ],
        [
          'name' => 'locale',
          'title' => ts('Locale'),
          'data_type' => 'String',
          'input_type' => 'Select',
          'required' => \CRM_Core_I18n::isMultiLingual(),
        ],
      ];

      // Calculated fields returned by get action
      if ($self->getAction() === 'get') {
        $fields[] = [
          'name' => 'module_name',
          'type' => 'Extra',
          'description' => 'Name of generated Angular module (CamelCase)',
          'readonly' => TRUE,
        ];
        $fields[] = [
          'name' => 'directive_name',
          'type' => 'Extra',
          'description' => 'Html tag name to invoke this template (dash-case)',
          'readonly' => TRUE,
        ];
        $fields[] = [
          'name' => 'has_local',
          'type' => 'Extra',
          'data_type' => 'Boolean',
          'description' => 'Whether a local copy is saved on site',
          'readonly' => TRUE,
        ];
        $fields[] = [
          'name' => 'has_base',
          'type' => 'Extra',
          'data_type' => 'Boolean',
          'description' => 'Is provided by an extension',
          'readonly' => TRUE,
        ];
        $fields[] = [
          'name' => 'base_module',
          'type' => 'Extra',
          'data_type' => 'String',
          'description' => 'Name of extension which provides this template',
          'readonly' => TRUE,
          'pseudoconstant' => ['callback' => ['CRM_Core_BAO_Managed', 'getBaseModules']],
          'input_type' => 'Select',
        ];
        $fields[] = [
          'name' => 'search_displays',
          'type' => 'Extra',
          'data_type' => 'Array',
          'readonly' => TRUE,
          'description' => 'Embedded search displays, formatted like ["search-name.display-name"]',
        ];
      }

      return $fields;
    }))->setCheckPermissions($checkPermissions);
  }

  /**
   * @return array
   */
  public static function permissions() {
    return [
      'meta' => ['access CiviCRM'],
      'default' => ['manage own afform'],
      'get' => [],
      'getOptions' => [],
    ];
  }

  /**
   * @inheritDoc
   */
  public static function getInfo() {
    $info = parent::getInfo();
    $info['primary_key'] = ['name'];
    return $info;
  }

}
