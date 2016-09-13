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
  if($result['is_error'] == 0) {
    $finalResult = array('success' => 'Authenticated successfully');
    return $finalResult;
  }
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
  $activityOptions = CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE);
  asort($activityOptions);

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

      $resultOutlookContact = array();
      //Get the contact details - CiviCRM "Contact get" API matches on the primary email address by default.
      $resultOutlookContact = civicrm_api3('Contact', 'get', $paramGetEmail);

      //Lookup for other email types
      if (empty($resultOutlookContact['values'])) {
        $resultOutlookContact = civicrm_api3('Email', 'get', $paramGetEmail);
      }

      $filteredArray = array();
      foreach ($resultOutlookContact['values'] as $key => $details) {
        if (CRM_Utils_Array::value('contact_id', $details)) {
          $filteredArray[$key][contact_id] = $details['contact_id'];
        }
        if (CRM_Utils_Array::value('contact_type', $details)) {
          $filteredArray[$key][contact_type] = $details['contact_type'];
        }
        if (CRM_Utils_Array::value('sort_name', $details)) {
          $filteredArray[$key][sort_name] = $details['sort_name'];
        }
        else {
          if (CRM_Utils_Array::value('contact_id', $details)) {
            $sort_name = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $details['contact_id'], 'sort_name', 'id');
            if ($sort_name) {
              $filteredArray[$key][sort_name] = $sort_name;
            }
          }
        }
        if (CRM_Utils_Array::value('email', $details)) {
          $filteredArray[$key][email] = $details['email'];
        }
      }

      $resultOutlookContact[values] = $filteredArray;
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
          $customActivityParams['target_contact_id'] = $resultOutlookContact['values'][$resultOutlookContact['id']]['contact_id'];
          $singleContactExists = array();
          $singleContactExists['singleContactExists'] = $resultOutlookContact['values'][$resultOutlookContact['id']]['contact_id'];
          return $singleContactExists;
        }
        else {
          //Create new contact
          $contact = array();
          $contact['contact_type'] = "Individual";
          $contact['email']=  $recipientEmail;
          $contactCreate = civicrm_api3('Contact', 'create', $contact );
          $singleContactCreated = array();
          $singleContactCreated['singleContactCreated'] = $contactCreate['id'];
          return $singleContactCreated;
        }
      }
      if(CRM_Utils_Array::value('case_id', $params)) {
        $customActivityParams['case_id'] = $params['case_id'];
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
      //
      $activityTypeDefaultSetting = CRM_Core_BAO_Setting::getItem(CRM_Outlookapi_Form_Setting::OUTLOOK_SETTING_GROUP,
      'activity_type', NULL, FALSE
      );
      if (CRM_Utils_Array::value('activity_type', $params)) {
        $customActivityParams['activity_type_id'] = $params['activity_type'];
      }
      else {
        $customActivityParams['activity_type_id'] = $activityTypeDefaultSetting;
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
  $catchAPIResult = civicrm_api3_create_success($finalresults, $params);
  $apiResult = array();
  $apiResult = $catchAPIResult['values'][0]['values'][0];

  //Handling this because XML Reader in outlook doesn't parse <0> as valid tag
  if(!empty($apiResult)) {
    return $apiResult;
  }
  else {
    return civicrm_api3_create_success($finalresults, $params);
  }
}

/**
 * Function to capture request/response params from Outlook
 * @param type $entity
 * @param type $action
 * @param type $request
 * @param type $response
 * @return type
 */
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

/**
 * Get all labels
 */
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

/*
 * Wrapper for logging
 */
function outlook_civicrm_api3($entity, $action, $customParams, $entitycivioutlook, $actioncivioutlook, $params) {
  $result = civicrm_api3($entity, $action, $customParams);
  civicrm_api3_civi_outlook_insertauditlog($entitycivioutlook, $actioncivioutlook, $params, $result);
  return $result;
}

/*
 * Outlook Settings - Set user defaults
 */
function civicrm_api3_civi_outlook_userdefault($params) {
  $params['email'] = trim($params['email']);
  if (preg_match('!\(([^\)]+)\)!', $params['email'], $match)) {
    $params['email'] = $match[1];
  }
  $source_contact_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $params['contact_api_key'], 'id', 'api_key');

  if (isset($source_contact_id) && !empty($source_contact_id)) {
    $baoQuery = "INSERT INTO `outlook_civicrm_user_defaults` (`date_created`,`source_contact_id`, `dupe_target_contact_id`, `email`) VALUES (now(), %1, %2, %3)";
    $queryParams = array(
      1 => array($source_contact_id, 'Integer'),
      2 => array($params['dupe_target_contact_id'], 'Integer'),
      3 => array($params['email'], 'String'),
    );
    $dao = CRM_Core_DAO::executeQuery($baoQuery, $queryParams);
  }
  return civicrm_api3_create_success($dao, $params);
}

/*
 * Outlook settings - Get user defaults
 */
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

/*
 * Process attachment for Activities
 */
function civicrm_api3_civi_outlook_processattachments($params) {
  //Get mime type of the attachment
  $mimeType = CRM_Utils_Type::escape($_REQUEST['mimeType'], 'String');
  $params['mime_type'] = $mimeType;

  $config = CRM_Core_Config::singleton();
  $directoryName = $config->customFileUploadDir;
  CRM_Utils_File::createDir($directoryName);

  //Process the below only if there is any attachment found
  if (CRM_Utils_Array::value("name", $_FILES['file'])) {
    $tmp_name = $_FILES['file']['tmp_name'];
    $name = str_replace(' ', '_', $_FILES['file']['name']);
    //Replace any spaces in name with underscore

    $fileExtension = new SplFileInfo($name);
    if ($fileExtension->getExtension()) {
      $explodeName = explode(".".$fileExtension->getExtension(), $name);
      $name = $explodeName[0]."_".md5($name).".".$fileExtension->getExtension();
    }

    $_FILES['file']['uri'] = $name;
    move_uploaded_file($tmp_name, "$directoryName$name");

    foreach ($_FILES['file'] as $key => $value) {
      $params[$key] = $value;
    }
    $result = civicrm_api3('File', 'create', $params);
    if (CRM_Utils_Array::value('id', $result)) {
      if(CRM_Utils_Array::value('activityID', $params)) {
        $lastActivityID = $params['activityID'];
      }

      $entityFileDAO = new CRM_Core_DAO_EntityFile();
      $entityFileDAO->entity_table = 'civicrm_activity';
      $entityFileDAO->entity_id = $lastActivityID;
      $entityFileDAO->file_id = $result['id'];
      $entityFileDAO->save();
    }
  }
  return civicrm_api3_create_success($entityFileDAO, $params);
}

/*
 * Get list of Civi Cases
 */
function civicrm_api3_civi_outlook_getcivicases($params) {
  $customCiviParams = array(
  'sequential' => 1,
  );
  if (isset($params['contact_id']) && !empty($params['contact_id'])) {
    $customCiviParams['contact_id'] = $params['contact_id'];
  }
  $result = outlook_civicrm_api3('Case', 'get', $customCiviParams, 'CiviOutlook', 'getcivicases', $params);
  $finalArray = array();

  foreach($result as $key => $value) {
    foreach($value as $k => $v) {
      $v['start_date'] = date("jS F, Y", strtotime($v['start_date']));
      $finalArray['case'.$k]  = $v;
      /*
       * Hack to show only the active cases for the contact i.e show cases only for which is_deleted = 0
       * This had to be done since the native API returns the deleted cases for the contact along with the other active cases
       */
      if ($v['is_deleted'] == 1) {
        unset($finalArray['case'.$k]);
      }
    }
  }
  return civicrm_api3_create_success($finalArray, $params);
}

/*
 *  Get Civi Case Types
 */
function civicrm_api3_civi_outlook_getcivicasetypes($params) {
  $caseTypes = CRM_Case_PseudoConstant::caseType('title', TRUE);
  $result = array();
  if (CRM_Utils_Array::value("case_type_name", $params)) {
    $result['id'] = array_search($params['case_type_name'], $caseTypes);
  }
  else {
    $result = $caseTypes;
  }
  return $result;
}

/*
 * Get Civi Case Status
 */
function civicrm_api3_civi_outlook_getcivicasestatus($params) {
  $caseStatuses = CRM_Case_PseudoConstant::caseStatus('label', TRUE);
  $result = array();
  if (CRM_Utils_Array::value("case_status_name", $params)) {
    $result['id'] = array_search($params['case_status_name'], $caseStatuses);
  }
  else {
    $result = $caseStatuses;
  }
  return $result;
}


/*
 * Create new Civi Case
 */
function civicrm_api3_civi_outlook_createnewcase($params) {
  $customCiviParams = array(
  'sequential' => 1,
  );
  $caseTypes = CRM_Case_PseudoConstant::caseType('title', TRUE);
  $caseStatuses = CRM_Case_PseudoConstant::caseStatus('label', TRUE);

  /**
   * Hack for the error - "creator id is not of type int" Native API throws this error
   * http://civicrm.stackexchange.com/questions/2727/why-doesnt-creating-a-case-from-api-work
   */
  if (CRM_Utils_Array::value("api_key", $_REQUEST)) {
    $source_contact_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $_REQUEST['api_key'], 'id', 'api_key');
    $params['creator_id'] = $customCiviParams['creator_id'] = intval($source_contact_id);
  }
  if(CRM_Utils_Array::value("case_type_name", $params)) {
    $customCiviParams['case_type_id'] = array_search($params['case_type_name'], $caseTypes);
  }
  if(CRM_Utils_Array::value("details", $params)) {
    $customCiviParams['details'] = $params['details'];
  }
  if(CRM_Utils_Array::value("subject", $params)) {
    $customCiviParams['subject'] = $params['subject'];
  }
  if(CRM_Utils_Array::value("start_date", $params)) {
    $customCiviParams['start_date'] = $params['start_date'];
  }
  if(CRM_Utils_Array::value("status_name", $params)) {
    $customCiviParams['status_id'] = array_search($params['status_name'], $caseStatuses);
  }
  if(CRM_Utils_Array::value("contact_id", $params)) {
    $customCiviParams['contact_id'] = $params['contact_id'];
  }

  $result = outlook_civicrm_api3('Case', 'create', $customCiviParams, 'CiviOutlook', 'createnewcase', $params);
  $finalArray = array();

  foreach($result as $key => $value) {
    foreach($value as $k => $v) {
      $finalArray['case_'.$k]  = $v;
    }
  }
  return civicrm_api3_create_success($finalArray, $params);
}

/*
 * Get Case Activity Types
 */
function civicrm_api3_civi_outlook_getcaseactivitytype($params) {
  $activityOptions = CRM_Case_PseudoConstant::caseActivityType();
  $result = array();
  foreach ($activityOptions as $key => $values) {
    $result[] = $key;
  }
  return $result;
}

/**
 * Get Contact Activity Types
 */
function civicrm_api3_civi_outlook_getactivitytype($params) {
  $result = array();
  $activityContact = CRM_Core_PseudoConstant::activityType(FALSE);
  if (!CRM_Utils_Array::value("Email", $activityContact)) {
    $activityContact[] = "Email";
  }
  $result = $activityContact;
  return $result;
}

/**
 * Get default activity type from CiviCRM setting
 */
function civicrm_api3_civi_outlook_getdefaultactivitytype($params) {
  $activityOptions = CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE);
  $result = array();
  $activityType = CRM_Core_BAO_Setting::getItem(CRM_Outlookapi_Form_Setting::OUTLOOK_SETTING_GROUP,
      'activity_type', NULL, FALSE
    );

  if($activityType) {
    $result[$activityType] = $activityOptions[$activityType];
  }
  return $result;
}

/**
 * Get groups and their contacts
 */
function civicrm_api3_civi_outlook_getgroupcontacts($params) {
  $groupData = $result = $temp = array();

  //get only the Outlook syncable groups
  $query = "
      SELECT entity_id as group_id
      FROM civicrm_value_outlook_group_settings_11
      WHERE sync_to_outlook_15 = '1'";

  $dao = CRM_Core_DAO::executeQuery($query);

  while ($dao->fetch()) {
    if ($dao->group_id) {
      //get Outlook syncable group contacts
      $contactValues = civicrm_api3('GroupContact', 'get', array(
        'sequential' => 1,
        'group_id' => $dao->group_id,
        'status' => "Added",
      ));
      if (CRM_Utils_Array::value("values", $contactValues)) {
        $groupData[][$dao->group_id] = $contactValues['values'];
      }
    }
  }

  //send only the essential contact details
  $contactMainDetails = array();

  foreach($groupData as $groupContactdetails) {
    foreach ($groupContactdetails as $groupID => $contactDetails) {
      //get group details
      $groupDetails = civicrm_api3('Group', 'get', array(
        'sequential' => 1,
        'id' => $groupID,
        'is_active' => 1,
      ));
      foreach($contactDetails as $dontBother => $values) {
        if (CRM_Utils_Array::value("values", $groupDetails)) {
          //get contact details
          $contactInfo = civicrm_api3('Contact', 'get', array(
            'sequential' => 1,
            'id' => $values['contact_id'],
            'group' => $groupDetails['values'][0]['id'],
          ));
          $contactMainDetails[$values['contact_id']]['group_id'] = $groupDetails['values'][0]['id'];
          $contactMainDetails[$values['contact_id']]['group_title'] = $groupDetails['values'][0]['title'];
        }
        $contactMainDetails[$values['contact_id']]['contact_id'] = $contactInfo['values'][0]['contact_id'];
        $contactMainDetails[$values['contact_id']]['first_name'] = $contactInfo['values'][0]['first_name'];
        $contactMainDetails[$values['contact_id']]['last_name'] = $contactInfo['values'][0]['last_name'];
        $contactMainDetails[$values['contact_id']]['email'] = $contactInfo['values'][0]['email'];
      }
      $temp[] = $contactMainDetails;
      unset($contactMainDetails);
      unset($groupDetails);
    }
  }

  $result['values'] = call_user_func_array('array_merge', $temp);
  return $result;
}
