<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from xml/schema/CRM/Core/Country.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:0a6cc17a97d1804a8ef0634e097b6315)
 */

/**
 * Base for concrete DAO/BAO classes which are defined with a schema/xml file.
 */
abstract class CRM_Core_DAO_Base extends CRM_Core_DAO {

  /**
   * @return string[]
   */
  protected function getPrimaryKey(): array {
    $definition = static::getEntityDefinition();
    if (empty($definition->primaryKey->fieldName) && empty($definition->primaryKey->name)) {
      return parent::getPrimaryKey();
    }
    if (empty($definition->primaryKey->fieldName)) {
      return [(string) $definition->primaryKey->name];
    }
    $primaryKeys = [];
    foreach ($definition->primaryKey->fieldName as $fieldName) {
      $primaryKeys[] = (string) $fieldName;
    }
    return $primaryKeys;
  }

  /**
   * Returns system paths related to this entity (as defined in the xml schema)
   *
   * @return array
   */
  public static function getEntityPaths(): array {
    $definition = static::getEntityDefinition();
    return (array) ($definition->paths ?? []);
  }

  /**
   * Returns user-friendly description of this entity based on the xml table <comment>.
   *
   * @return string
   */
  public static function getEntityDescription(): ?string {
    $definition = static::getEntityDefinition();
    $description = (string) ($definition->comment ?? NULL);
    return $description ? static::_ts($description) : NULL;
  }

  /**
   * Returns localized name of this table
   *
   * @return string
   */
  public static function getTableName() {
    $definition = static::getEntityDefinition();
    return (string) $definition->name;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog(): bool {
    $definition = $this::getEntityDefinition();
    $log = (string) ($definition->log ?? 'false');
    return strtolower($log) === 'true';
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    $fields = (Civi::$statics[static::class]['fields'] ??= static::getSchemaFields());
    return $fields;
  }

  private static function getSchemaFields(): array {
    $fields = [];
    $definition = static::getEntityDefinition();
    $baoName = CRM_Core_DAO_AllCoreTables::getBAOClassName(static::class);
    $primaryKey = (string) ($definition->primaryKey->name ?? '');

    $foreignKeys = [];
    foreach ($definition->foreignKey ?? [] as $fkXml) {
      if (empty($fkXml->drop)) {
        $foreignKeys[(string) $fkXml->name] = [
          'FKClassName' => CRM_Core_DAO_AllCoreTables::getClassForTable($fkXml->table),
          'FKColumnName' => (string) ($fkXml->key ?? 'id'),
        ];
      }
    }
    foreach ($definition->dynamicForeignKey ?? [] as $dfkXml) {
      if (empty($dfkXml->drop)) {
        $foreignKeys[(string) $dfkXml->idColumn] = [
          'DFKEntityColumn' => (string) $dfkXml->typeColumn,
          'FKColumnName' => (string) ($dfkXml->key ?? 'id'),
        ];
      }
    }

    foreach ($definition->field ?? [] as $fieldXml) {
      if (!empty($fieldXml->drop)) {
        continue;
      }
      $fieldName = (string) $fieldXml->name;
      $fieldTypeAttrs = CRM_Utils_Schema::getTypeAttributes($fieldXml);
      $field = [
        'name' => $fieldName,
        'type' => $fieldTypeAttrs['crmTypeValue'],
        'title' => static::_ts(((string) ($fieldXml->title ?? '')) ?: CRM_Utils_Schema::composeTitle($fieldName)),
      ];
      if (!empty($fieldXml->comment)) {
        $field['description'] = static::_ts((string) $fieldXml->comment);
      }
      if (strtolower((string) ($fieldXml->required ?? '')) === 'true') {
        $field['required'] = TRUE;
      }
      if (!empty($fieldTypeAttrs['length'])) {
        $field['maxlength'] = $fieldTypeAttrs['length'];
      }
      if (!empty($fieldTypeAttrs['precision'])) {
        $field['precision'] = explode(',', $fieldTypeAttrs['precision']);
      }
      if (!empty($fieldTypeAttrs['size'])) {
        $field['size'] = is_numeric($fieldTypeAttrs['size']) ? (int) $fieldTypeAttrs['size'] : constant($fieldTypeAttrs['size']);
      }
      if (isset($fieldTypeAttrs['rows'])) {
        $field['rows'] = $fieldTypeAttrs['rows'];
      }
      if (isset($fieldTypeAttrs['cols'])) {
        $field['cols'] = $fieldTypeAttrs['cols'];
      }
      $field['usage'] = CRM_Utils_Schema::getFieldUsage($fieldXml);
      if ($field['usage']['import']) {
        $field['import'] = TRUE;
      }
      $field['where'] = ((string) $definition->name) . ".$fieldName";
      if (!empty($fieldXml->headerPattern)) {
        $field['headerPattern'] = (string) $fieldXml->headerPattern;
      }
      if (!empty($fieldXml->dataPattern)) {
        $field['dataPattern'] = (string) $fieldXml->dataPattern;
      }
      if ($field['usage']['export'] || (!$field['usage']['export'] && $field['usage']['import'])) {
        $field['export'] = $field['usage']['export'];
      }
      if (!empty($fieldXml->contactType)) {
        $field['contactType'] = (string) $fieldXml->contactType;
      }
      if (!empty($fieldXml->rule)) {
        $field['rule'] = (string) $fieldXml->rule;
      }
      $permission = CRM_Utils_Schema::getFieldPermission($fieldXml);
      if ($permission) {
        $field['permission'] = $permission;
      }
      if (isset($fieldXml->default)) {
        // Default value in the xml may or may not be quoted
        $default = trim((string) $fieldXml->default, '"\'');
        $field['default'] = $default === 'NULL' ? NULL : $default;
      }
      $field['table_name'] = (string) $definition->name;
      $field['entity'] = (string) ($definition->entity ?? $definition->class);
      $field['bao'] = $baoName;
      $field['localizable'] = (int) ($fieldXml->localizable ?? 0);
      if (!empty($fieldXml->localize_context)) {
        $field['localize_context'] = (string) $fieldXml->localize_context;
      }
      $field += $foreignKeys[$fieldName] ?? [];
      if (!empty($fieldXml->component)) {
        $field['component'] = (string) $fieldXml->component;
      }
      if (!empty($fieldXml->serialize)) {
        $field['unique_title'] = constant('CRM_Core_DAO::SERIALIZE_' . $fieldXml->serialize);
      }
      if (!empty($fieldXml->uniqueTitle)) {
        $field['unique_title'] = static::_ts((string) $fieldXml->uniqueTitle);
      }
      if (!empty($fieldXml->deprecated)) {
        $field['deprecated'] = TRUE;
      }
      $html = CRM_Utils_Schema::getFieldHtml($fieldXml);
      if ($html) {
        $field['html'] = $html;
      }
      $pseudoconstant = CRM_Utils_Schema::getFieldPseudoconstant($fieldXml);
      if ($pseudoconstant) {
        $field['pseudoconstant'] = $pseudoconstant;
      }
      if ($fieldName === $primaryKey || !empty($fieldXml->readonly)) {
        $field['readonly'] = TRUE;
      }
      $field['add'] = CRM_Utils_Schema::toString('add', $fieldXml);
      $fieldKey = (string) ($fieldXml->uniqueName ?? $fieldName);
      $fields[$fieldKey] = $field;
    }
    CRM_Core_DAO_AllCoreTables::invoke(static::class, 'fields_callback', $fields);
    return $fields;
  }

  private static function getEntityDefinition(): SimpleXMLElement {
    if (!isset(Civi::$statics[static::class][__FUNCTION__])) {
      [, $dir, , $name] = explode('_', static::class);
      $reflection = new ReflectionClass(static::class);
      $classDir = dirname($reflection->getFileName());
      $xmlFile = $classDir . "/../../../xml/schema/$dir/$name.xml";
      [$definition, $error] = CRM_Utils_XML::parseFile($xmlFile);
      if ($error) {
        throw new CRM_Core_Exception(static::class . ': ' . $error);
      }
      Civi::$statics[static::class][__FUNCTION__] = $definition;
    }
    return Civi::$statics[static::class][__FUNCTION__];
  }

  /**
   * @inheritDoc
   */
  public static function getEntityIcon(string $entityName, int $entityId = NULL): ?string {
    $definition = static::getEntityDefinition();
    return isset($definition->icon) ? (string) $definition->icon : NULL;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices(bool $localize = TRUE): array {
    $definition = static::getEntityDefinition();
    $indices = [];
    foreach ($definition->index ?? [] as $indexXml) {
      if (!empty($indexXml->drop)) {
        continue;
      }
      $index = [
        'name' => (string) $indexXml->name,
        'field' => [],
        'localizable' => FALSE,
      ];
      if (!empty($indexXml->unique)) {
        $index['unique'] = TRUE;
      }
      // populate fields
      foreach ($indexXml->fieldName as $field) {
        $fieldName = (string) ($field);
        $index['field'][] = $fieldName;
        foreach ($definition->field as $fieldXml) {
          if (((string) $fieldXml->name) === $fieldName && !empty($fieldXml->localizable)) {
            $index['localizable'] = TRUE;
          }
        }
      }
      $index['sig'] = ((string) $definition->name) . '::' . ((int) ($index['unique'] ?? 0)) . '::' . implode('::', $index['field']);
      $indices[$index['name']] = $index;
    }
    return ($localize && $indices) ? CRM_Core_DAO_AllCoreTables::multilingualize(static::class, $indices) : $indices;
  }

  private static function _ts(string $str) {
    $params = ['domain' => [self::getExtensionName(), NULL]];
    return _ts($str, $params);
  }

  public static function getExtensionName(): ?string {
    if (!isset(Civi::$statics[static::class][__FUNCTION__])) {
      $reflection = new ReflectionClass(static::class);
      $classDir = dirname($reflection->getFileName());
      $infoFile = $classDir . "/../../../info.xml";
      [$info, $error] = CRM_Utils_XML::parseFile($infoFile);
      $extensionKey = $info ? ($info->attributes()->key ?? NULL) : NULL;
      Civi::$statics[static::class][__FUNCTION__] = (string) ($extensionKey ?? 'civicrm');
    }
    return Civi::$statics[static::class][__FUNCTION__];
  }

  /**
   * @return string
   *   Version in which table was added
   */
  protected static function getTableAddVersion(): string {
    $definition = static::getEntityDefinition();
    return (string) ($definition->add ?? '1.0');
  }

}