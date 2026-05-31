<?php

namespace Civi\Api4\Action\Afform;

/**
 * @inheritDoc
 * @package Civi\Api4\Action\Afform
 */
class Get extends \Civi\Api4\Generic\BasicGetAction {

  use \Civi\Api4\Utils\AfformGetTrait;

  protected function getScannerServiceName(): string {
    return 'afform_scanner';
  }

  protected function getHookName(): string {
    return 'civi.afform.get';
  }

  protected function getHookParamKey(): string {
    return 'afforms';
  }

  protected function addExtraComputedFields(array &$afforms) {
    if ($afforms && $this->_isFieldSelected('submission_count', 'submission_date', 'user_submission_count', 'submit_currently_open')) {
      $userContactId = \CRM_Core_Session::getLoggedInContactID();
      $afformSubmissions = \Civi\Api4\AfformSubmission::get(FALSE)
        ->addSelect('afform_name', 'COUNT(id) AS count', 'MAX(submission_date) AS date')
        ->addWhere('afform_name', 'IN', array_keys($afforms))
        ->addWhere('status_id:name', '!=', 'Draft')
        ->addGroupBy('afform_name')
        ->execute()->indexBy('afform_name');
      foreach ($afforms as $name => $record) {
        $afforms[$name]['submission_count'] = $afformSubmissions[$name]['count'] ?? 0;
        $afforms[$name]['submission_date'] = $afformSubmissions[$name]['date'] ?? NULL;
        $afforms[$name]['submit_currently_open'] = ($record['submit_enabled'] ?? TRUE) && (empty($record['submit_limit']) || $record['submit_limit'] > $afforms[$name]['submission_count']);

        // Check per-user submission limit
        if ($userContactId && ($this->_isFieldSelected('user_submission_count') || (!empty($afforms[$name]['submit_limit_per_user']) && $afforms[$name]['submit_currently_open']))) {
          $userSubmissions = \Civi\Api4\AfformSubmission::get(FALSE)
            ->addWhere('afform_name', '=', $name)
            ->addWhere('contact_id', '=', $userContactId)
            ->addWhere('status_id:name', '!=', 'Draft')
            ->selectRowCount()
            ->execute();
          $afforms[$name]['user_submission_count'] = $userSubmissions->countMatched();
          if (!empty($afforms[$name]['submit_limit_per_user']) && $afforms[$name]['submit_currently_open']) {
            $afforms[$name]['submit_currently_open'] = $userSubmissions->countMatched() < $afforms[$name]['submit_limit_per_user'];
          }
        }
      }
    }
  }

}
