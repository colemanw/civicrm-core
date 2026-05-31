<?php

namespace Civi\Api4\Action\AfformTemplate;

/**
 * @inheritDoc
 * @package Civi\Api4\Action\AfformTemplate
 */
class Get extends \Civi\Api4\Generic\BasicGetAction {

  use \Civi\Api4\Utils\AfformGetTrait;

  protected function getScannerServiceName(): string {
    return 'afform_template_scanner';
  }

  protected function getHookName(): string {
    return 'civi.afformTemplate.get';
  }

  protected function getHookParamKey(): string {
    return 'afformTemplates';
  }

  protected function addExtraComputedFields(array &$records) {
    // No-op for templates
  }

}
