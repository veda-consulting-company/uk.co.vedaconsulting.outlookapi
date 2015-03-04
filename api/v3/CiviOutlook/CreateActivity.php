<?php

/**
 * CiviOutlook.CreateActivity API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_civi_outlook_createactivity_spec(&$spec) {
  $spec['magicword']['api.required'] = 0;
}

/**
 * CiviOutlook.CreateActivity API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_outlook_createactivity($params) {
  $customParams = array(
  'sequential' => 1,
  );
 
  if (isset($params['key']) && !empty($params['key'])) {
    $customParams['key'] = $params['key'];
  }
  if (isset($params['api_key']) && !empty($params['api_key'])) {
    $customParams['api_key'] = $params['api_key'];
  }
  if (isset($params['activity_type_id']) && !empty($params['activity_type_id'])) {
    $customParams['activity_type_id'] = $params['activity_type_id'];
  }
  if (isset($params['source_contact_id']) && !empty($params['source_contact_id'])) {
    $customParams['source_contact_id'] = $params['source_contact_id'];
  }
  if (isset($params['subject']) && !empty($params['subject'])) {
    $customParams['subject'] = $params['subject'];
  }
  
  $result = civicrm_api3('Activity', 'create', $customParams);
  return $result;
}

