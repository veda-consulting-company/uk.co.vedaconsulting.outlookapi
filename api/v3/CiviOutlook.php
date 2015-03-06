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
    
  $resultGetEmail = array();
  if (CRM_Utils_Array::value('email', $params)) {
  $resultGetEmail['email']= $params['email'];
  } 
  $resultOutlookContact = civicrm_api3('Contact', 'get', $resultGetEmail );
  
   //Contact exists 
  if (array_key_exists('id', $resultOutlookContact) && CRM_Utils_Array::value('id', $resultOutlookContact) ){
    $customParams['source_contact_id']= $resultOutlookContact['id'];  
  }
  else {
    //Create new contact
    $contact = array();
    $contact['contact_type'] = "Individual";
    $contact['email']=  $params['email'];
    $contactCreate = civicrm_api3('Contact', 'create', $contact );
    $customParams['source_contact_id']= $contactCreate['id'];
  }
  if (CRM_Utils_Array::value('key', $params)) {
  $customParams['key']= $params['key'];
  }
  if (CRM_Utils_Array::value('api_key', $params)) {
  $customParams['api_key'] = $params['api_key'];
  }
  if (CRM_Utils_Array::value('activity_type_id', $params)) {
  $customParams['activity_type_id'] = $params['activity_type_id'];
  }
  if (CRM_Utils_Array::value('source_contact_id', $params)) {
  $customParams['source_contact_id'] = $params['source_contact_id'];
  }
  if (CRM_Utils_Array::value('subject', $params)) {
  $customParams['subject'] = $params['subject'];
  }
  
  $result = civicrm_api3('Activity', 'create', $customParams);
  return $result;  
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
