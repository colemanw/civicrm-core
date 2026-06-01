<?php

namespace Civi\Api4\Action\AfformTemplate;

/**
 * This action is used by the Afform Admin extension to load metadata for the Admin GUI.
 *
 * @package Civi\Api4\Action\AfformTemplate
 */
class LoadAdminData extends \Civi\Api4\Action\Afform\LoadAdminData {

  /**
   * The APIv4 entity name (Afform or AfformTemplate)
   * @var string
   */
  protected $entityType = 'AfformTemplate';

}
