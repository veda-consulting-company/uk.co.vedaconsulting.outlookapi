<?php

/**
 * CiviOutlook.GetDomain API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_civi_outlook_getdomain_spec(&$spec) {
  $spec['magicword']['api.required'] = 0;
}

/**
 * CiviOutlook.GetDomain API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_outlook_getdomain($params) {
  $customParams = array(
  'sequential' => 1,
  );
  if (isset($params['key']) && !empty($params['key'])) {
    $customParams['key'] = $params['key'];
  }
  if (isset($params['api_key']) && !empty($params['api_key'])) {
    $customParams['api_key'] = $params['api_key'];
  }
  $result = civicrm_api3('Domain', 'get', $customParams);
  return $result;
}

