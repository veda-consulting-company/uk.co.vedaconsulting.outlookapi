<?php

/**
 * CiviOutlook API specification (optional)
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
 * CiviOutlook.GetDomain API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_outlook_getdomain($params) {
  $customDomainParams = array(
      'sequential' => 1,
  );
  if (isset($params['key']) && !empty($params['key'])) {
    $customDomainParams['key'] = $params['key'];
  }
  if (isset($params['api_key']) && !empty($params['api_key'])) {
    $customDomainParams['api_key'] = $params['api_key'];
  }
  $result = outlook_civicrm_api3('Domain', 'get', $customDomainParams, 'CiviOutlook', 'getdomain', $params);
  return $result;
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
  $customActivityParams = array(
  'sequential' => 1,
  );
  $paramGetEmail = $checkMultipleRecipients = array();

  //Email is required here
  if (CRM_Utils_Array::value('email', $params)) {
    $checkMultipleRecipients = explode(";", $params['email']);
    $finalresults = array();
    foreach($checkMultipleRecipients as $key => $recipientEmail) {
      if (preg_match('!\(([^\)]+)\)!', $recipientEmail, $match)) {
        $recipientEmail = $match[1];
      }
      $paramGetEmail['email']= $recipientEmail;
      $resultOutlookContact = civicrm_api3('Contact', 'get', $paramGetEmail );

      //If there are duplicate contacts return those contacts to Outlook
      if (!empty($resultOutlookContact)) {
        $countContact = count(array_keys($resultOutlookContact['values']));
        if ($countContact > 1) {
          return $resultOutlookContact;
        }
      }
      //Contact exists
      if (array_key_exists('id', $resultOutlookContact) && CRM_Utils_Array::value('id', $resultOutlookContact) ){
        $customActivityParams['target_contact_id'] = $resultOutlookContact['id'];
      }
      else {
        //Create new contact
        $contact = array();
        $contact['contact_type'] = "Individual";
        $contact['email']=  $recipientEmail;
        $contactCreate = civicrm_api3('Contact', 'create', $contact );
        $customActivityParams['target_contact_id'] = $contactCreate['id'];
      }
      if ($_REQUEST['api_key']) {
        $source_contact_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $_REQUEST['api_key'], 'id', 'api_key');
        $customActivityParams['source_contact_id'] = $source_contact_id;
      }
//      else {
//        $getId = civicrm_api3('Contact', 'get', array('sequential' => 1, 'api_key' => $params['api_key']));
//        if (CRM_Utils_Array::value('id', $getId)) {
//          $customActivityParams['source_contact_id'] = $getId['id'];
//        }
//      }
      if (CRM_Utils_Array::value('key', $params)) {
        $customActivityParams['key']= $params['key'];
      }
      if (CRM_Utils_Array::value('api_key', $params)) {
        $customActivityParams['api_key'] = $params['api_key'];
      }
      if (CRM_Utils_Array::value('activity_type_id', $params)) {
        $customActivityParams['activity_type_id'] = $params['activity_type_id'];
      }
      if (CRM_Utils_Array::value('subject', $params)) {
        $customActivityParams['subject'] = $params['subject'];
      }
      if (CRM_Utils_Array::value('email_body', $params)) {
        $customActivityParams['details'] = $params['email_body'];
      }
      $result = outlook_civicrm_api3('Activity', 'create', $customActivityParams, 'CiviOutlook', 'createactivity', $params);
      $finalresults[] = $result;
    }
    return civicrm_api3_create_success($finalresults, $params);
  }
}
function civicrm_api3_civi_outlook_insertauditlog($entity, $action, $request, $response) {

    if (empty($entity) || empty($action) || empty($request) || empty($response)) {
        return;
    }
    $insertQuery = "INSERT INTO `outlook_civicrm_audit` (`datetime`, `entity`, `action`, `request`, `response`) VALUES (now(), %1, %2, %3, %4)";
    $insertParams = array(
      1 => array($entity, 'String'),
      2 => array($action, 'String'),
      3 => array(serialize($request), 'String'),
      4 => array(serialize($response), 'String'),
    );
    try {
        CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
    }
    catch (CRM_Core_Exception $e) {
        return;
    }
}
function civicrm_api3_civi_outlook_getlables() {
  $customLablesParams = array(
    'civi_results_url' => ts('CiviCRM Resource URL'),
    'api_key' => ts('Api Key'),
    'site_key' => ts('Site Key'),
    'help_for_civicrm_resource_url' => ts('(Absolute URL of the location where the civicrm module is installed)
      e.g http://www.example.com/sites/all/modules/civicrm'),
    'help_for_api_key' => ts('(Key provided by your Site Admin)'),
    'help_for_site_key' => ts('(Site Key available to you in civicrm.setting.php file)'),
  );
  return $customLablesParams;
}
function outlook_civicrm_api3($entity, $action, $customParams, $entitycivioutlook, $actioncivioutlook, $params) {
  $result = civicrm_api3($entity, $action, $customParams);
  civicrm_api3_civi_outlook_insertauditlog($entitycivioutlook, $actioncivioutlook, $params, $result);
  return $result;
}
