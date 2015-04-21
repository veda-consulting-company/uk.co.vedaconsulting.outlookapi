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
  $paramGetEmail = $recipientEmail = $finalresults = array();

  //Email is required here
  if (CRM_Utils_Array::value('email', $params)) {
    $recipientEmail = $params['email'];

    if (preg_match('!\(([^\)]+)\)!', $recipientEmail, $match)) {
        $recipientEmail = $match[1];
      }
      $paramGetEmail['email']= $recipientEmail;
      $resultOutlookContact = civicrm_api3('Contact', 'get', $paramGetEmail );

      //If there are duplicate contacts return those contacts to Outlook
      if (!array_key_exists('ot_target_contact_id', $params)) {
        if (!empty($resultOutlookContact)) {
          $countContact = count(array_keys($resultOutlookContact['values']));
          if ($countContact > 1) {
            return $resultOutlookContact;
          }
        }
      }

      if (CRM_Utils_Array::value('ot_target_contact_id', $params)) {
        $customActivityParams['target_contact_id'] = $params['ot_target_contact_id'];
      }
      else {
        //Contact exists
        if (array_key_exists('id', $resultOutlookContact) && CRM_Utils_Array::value('id', $resultOutlookContact) ){
          //If outlook has sent a target contact id then create activity with that id
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
      }
      if ($_REQUEST['api_key']) {
        $source_contact_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $_REQUEST['api_key'], 'id', 'api_key');
        $customActivityParams['source_contact_id'] = $source_contact_id;
      }
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
      unset($params['ot_target_contact_id']);
  }
  return civicrm_api3_create_success($finalresults, $params);
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

function civicrm_api3_civi_outlook_userdefault($params) {
  $params['email'] = trim($params['email']);
  if (preg_match('!\(([^\)]+)\)!', $params['email'], $match)) {
    $params['email'] = $match[1];
  }
  $source_contact_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $params['contact_api_key'], 'id', 'api_key');

  $selectQuery = "SELECT `id`
      FROM `outlook_civicrm_setting`
      WHERE `setting_name` = 'remember_duplicate_contacts'
      AND `setting_value` = 1";
  $daoSettings = CRM_Core_DAO::executeQuery($selectQuery);
  $sid = '';
  while ($daoSettings->fetch()) {
    $sid = $daoSettings->id;
  }
  if (isset($sid) && !empty($sid)) {
    $baoQuery = "INSERT INTO `outlook_civicrm_user_defaults` (`date_created`,`source_contact_id`, `dupe_target_contact_id`, `email`, `sid`) VALUES (now(), %1, %2, %3, $sid)";
    $queryParams = array(
      1 => array($source_contact_id, 'Integer'),
      2 => array($params['dupe_target_contact_id'], 'Integer'),
      3 => array($params['email'], 'String'),
    );
    $dao = CRM_Core_DAO::executeQuery($baoQuery, $queryParams);
  }
  return civicrm_api3_create_success($dao, $params);
}

function civicrm_api3_civi_outlook_getuserdefaults($params) {
  $params['email'] = trim($params['email']);
  if (preg_match('!\(([^\)]+)\)!', $params['email'], $match)) {
    $params['email'] = $match[1];
  }
  $source_contact_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $params['contact_api_key'], 'id', 'api_key');
  $selectQuery = "SELECT *
      FROM `outlook_civicrm_user_defaults`
      WHERE `source_contact_id` = $source_contact_id
      AND email = '{$params['email']}' ORDER BY date_created desc limit 1";

  $dao = CRM_Core_DAO::executeQuery($selectQuery);
  $values = array();
  while($dao->fetch()) {
    $values[] = array('dupe_target_contact_id' => $dao->dupe_target_contact_id);
  }
  return civicrm_api3_create_success($values, $params);
}

function civicrm_api3_civi_outlook_setting($params) {
  $baoQuery = "UPDATE `outlook_civicrm_setting`
      SET setting_value = %2 , date_created = now()
      WHERE setting_name= %1";
  $queryParams = array(
    1 => array($params['setting_name'], 'String'),
    2 => array($params['setting_value'], 'Boolean'),
  );
  $dao = CRM_Core_DAO::executeQuery($baoQuery, $queryParams);
  return civicrm_api3_create_success($dao, $params);
}
