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
 * CiviOutlook API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_civi_outlook_getvalidid_spec(&$spec) {
  $spec['contact_id']['api.required'] = 1;
}

/**
 * CiviOutlook.GetValidId API
 * This API validates the contact_id, activity_id and checks whether an activity belongs to that particular contact
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_civi_outlook_getvalidid($params) {
  $result = array();
  //Check whether the contact exists in Civi
  if (CRM_Utils_Array::value('contact_id', $params)) {
    try {
      $contact = civicrm_api3('Contact', 'get', array(
        'sequential' => 1,
        'contact_type' => "Individual",
        'id' => $params['contact_id'],
      ));

      //if contact exists, add it to the results
      if (CRM_Utils_Array::value('id', $contact)) {
        $result['values'][0]['contact_id'] = $contact['id'];
      }
      //if contact wasn't found, make sure it's removed from outlook_civicrm_user_defaults table
      else {
        $sql = "DELETE FROM outlook_civicrm_user_defaults
        WHERE dupe_target_contact_id = ".$params['contact_id'];
        CRM_Core_DAO::executeQuery($sql);
        //Assign it to results
        $result['values'][0]['isContactDeletedInCivi'] = true;
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message($error);
    }
  }

  //Check if activity_id exists in Civi
  if (CRM_Utils_Array::value('activity_id', $params)) {
    try {
      $activity = civicrm_api3('Activity', 'get', array(
        'sequential' => 1,
        'id' => $params['activity_id'],
      ));

      //if activity exists, add it to the results
      if (CRM_Utils_Array::value('id', $activity)) {
        $result['values'][0]['activity_id'] = $activity['id'];
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message($error);
    }
  }

  //Check if this activity belongs to this contact
  $isActivityRelatedToContact = FALSE;
  if (CRM_Utils_Array::value('contact_id', $params) &&
      CRM_Utils_Array::value('activity_id', $params)) {
    try {
      $activityContact = civicrm_api3('ActivityContact', 'get', array(
        'sequential' => 1,
        'contact_id' => $params['contact_id'],
        'activity_id' => $params['activity_id'],
        'record_type_id' => "Activity Targets",
      ));

      //if activity belongs to contact, set the isActivityRelatedToContact to true and add it to results
      if (CRM_Utils_Array::value('id', $activityContact['values'][0])) {
        $isActivityRelatedToContact = TRUE;
        $result['values'][0]['isActivityRelatedToContact'] = $isActivityRelatedToContact;
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message($error);
    }
  }
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
       /*
      * Check if this file extension is allowed
      * Check against values in "Outlook Safe File Extensions" custom field in Civi
      */
      $allFileExtensions = array();
      //Get all the file extensions
      try {
        $result = civicrm_api3('OptionValue', 'get', array(
          'sequential' => 1,
          'option_group_id' => "ignore_file_extensions",
        ));
        if (!empty($result)) {
          foreach ($result['values'] as $ext) {
            $allFileExtensions[$ext['value']] = $ext['name'];
          }
        }

        //Get source_contact_id and check if they have any file extensions added that need to be ignored when processing attachments
        $source_contact_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $_REQUEST['api_key'], 'id', 'api_key');

        if ($source_contact_id) {
          try {
            $result = civicrm_api3('CustomValue', 'get', array(
              'sequential' => 1,
              'entity_id' => $source_contact_id,
            ));
            foreach ($result['values'][0]['latest'] as $key => $val) {
              if ($allFileExtensions[$val] == $fileExtension->getExtension()) {
                return;
              }
            }
          }
          catch (CiviCRM_API3_Exception $e) {
            $error = $e->getMessage();
            CRM_Core_Error::debug_log_message($error);
          }
        }
      }
      catch (CiviCRM_API3_Exception $e) {
        $error = $e->getMessage();
        CRM_Core_Error::debug_log_message($error);
      }

      // calling civi method to make attached file name. This makes unique name regardless of same file name
      $name = CRM_Utils_File::makeFileName($_FILES['file']['name']);
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
function _civicrm_api3_civi_outlook_getcaseactivitytype_spec(&$spec) {
  $spec['case_id']['api.required'] = 0;
}

function civicrm_api3_civi_outlook_getcaseactivitytype($params) {
  $case = civicrm_api3('Case', 'getsingle', array(
    'id' => $params['case_id'],
  ));

  $allowedActivities = civicrm_api3('CaseType', 'getsingle', array(
    'sequential'  => 1,
    'id'          => $case['case_type_id'],
    'return'      => 'definition',
  ));

  $result = array();
  foreach ($allowedActivities['definition']['activityTypes'] as $value) {
    $result[] = $value['name'];
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
  $groupData = $groupDetails = $result = $temp = array();

  //check if distlists are sent from outlook. If yes only these lists would be synced with CiviCRM groups
  $outlookDistLists = array();
  if (CRM_Utils_Array::value("outlook_requested_groups", $params)) {
    $distLists = trim($params['outlook_requested_groups'], "::");
    $outlookDistLists = explode("::", $distLists);
  }

  //get only the Outlook syncable groups
  $query = "
      SELECT se.entity_id as group_id, grp.title
      FROM civicrm_value_outlook_group_settings_11 se
      INNER JOIN civicrm_group grp
      ON se.entity_id = grp.id
      WHERE se.sync_to_outlook_15 = '1'";

  //if group names are from outlook check is they are set to syncable in Civi
  if (!empty($outlookDistLists)) {
    foreach($outlookDistLists as $key => $list) {
      $outlookDistLists[$key] = "'".addslashes($list)."'";
    }
    $query .= " AND grp.title IN (".implode($outlookDistLists, ',').")";
  }

  $dao = CRM_Core_DAO::executeQuery($query);

  while ($dao->fetch()) {
    // process further only if group is present
    if ($dao->group_id) {
      //get Outlook syncable group contacts
      $contactValues = civicrm_api3('Contact', 'get', array(
        'sequential' => 1,
        'group'      => $dao->group_id,
        'status'     => "Added",
        'options'    => array(
          'limit'    => 0,
        ),
      ));

      if (CRM_Utils_Array::value("values", $contactValues)) {
        $groupData[$dao->group_id] = $contactValues['values'];
        $groupDetails[$dao->group_id] = $dao->title;
      }
      unset($contactValues);
    }
  }

  //get Outlook and CiviCRM field mapping for additional contact details
  $mappings = civicrm_api3_civi_outlook_getadditionalfieldmapping();

  //send only the essential/required contact details
  if (!empty($groupData)) {
    foreach ($groupData as $groupID => $groupContactdetails) {
      foreach ($groupContactdetails as $key => $contactDetails) {
        //get the additional email address(s) for this contact. Here we get email address(s) that are not primary
        $additionalEmails = array();
        try {
          $resultEmails = civicrm_api3('Email', 'get', array(
            'sequential' => 1,
            'contact_id' => $contactDetails['contact_id'],
            'is_primary' => 0,
          ));
          if (!empty($resultEmails['values'])) {
            foreach ($resultEmails['values'] as $dontCare => $emailDetails) {
              $additionalEmails[$emailDetails['location_type_id']] = $emailDetails['email'];
            }
          }
        }
        catch (CiviCRM_API3_Exception $e) {
          $error = $e->getMessage();
          CRM_Core_Error::debug_log_message($error);
        }

        //get additional phone number(s) for this contact. Here we get phone number(s) that are  primary and not primary
        $additionalPhoneNumbers = array();
        try {
          $resultPhoneNumbers = civicrm_api3('Phone', 'get', array(
            'sequential' => 1,
            'contact_id' => $contactDetails['contact_id'],
          ));

          if (!empty($resultPhoneNumbers['values'])) {
            foreach ($resultPhoneNumbers['values'] as $dontCare => $phoneDetails) {
              $additionalPhoneNumbers[$phoneDetails['location_type_id']][$phoneDetails['phone_type_id']] = $phoneDetails['phone'];
            }
          }
        }
        catch (CiviCRM_API3_Exception $e) {
          $error = $e->getMessage();
          CRM_Core_Error::debug_log_message($error);
        }

        //get additional phone type - assistant
        $phoneTypes = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Phone', 'phone_type_id');
        $assistantId = array_search(PHONE_TYPE_ASSISTANT, $phoneTypes) ? array_search(PHONE_TYPE_ASSISTANT, $phoneTypes) : '';

        //get additional address(s) for this contact. Here we get address(s) that are primary and not primary
        $additionalAddresses = array();
        try {
          $resultAddresses = civicrm_api3('Address', 'get', array(
            'sequential' => 1,
            'contact_id' => $contactDetails['contact_id'],
          ));
          if (!empty($resultAddresses['values'])) {
            foreach ($resultAddresses['values'] as $dontCare => $addressDetails) {
              $additionalAddresses[$addressDetails['location_type_id']]['street_address']    = $addressDetails['street_address'];
              $additionalAddresses[$addressDetails['location_type_id']]['city']              = $addressDetails['city'];
              $additionalAddresses[$addressDetails['location_type_id']]['postal_code']       = $addressDetails['postal_code'];
              $additionalAddresses[$addressDetails['location_type_id']]['state_province_id'] = $addressDetails['state_province_id'] ? CRM_Core_PseudoConstant::stateProvince($addressDetails['state_province_id']) : "";
              $additionalAddresses[$addressDetails['location_type_id']]['country_id']        = $addressDetails['country_id'] ? CRM_Core_PseudoConstant::country($addressDetails['country_id']) : "";
            }
          }
        }
        catch (CiviCRM_API3_Exception $e) {
          $error = $e->getMessage();
          CRM_Core_Error::debug_log_message($error);
        }

        //get custom field(s)(if any) for this contact
        $customData = array();
        try {
          $resultCustomData = civicrm_api3('CustomValue', 'get', array(
            'sequential' => 1,
            'entity_id'  => $contactDetails['contact_id'],
          ));
          if (!empty($resultCustomData['values'])) {
            foreach ($resultCustomData['values'] as $dontCare => $customDataDetails) {
              //get custom field name by id
              $resultCustomField = civicrm_api3('CustomField', 'get', array(
                'sequential' => 1,
                'id'         => $customDataDetails['id'],
              ));
              if (!empty($resultCustomField['values'][0])) {
                $customData[$resultCustomField['values'][0]['name']] = $customDataDetails['latest'];

                //if option group/value are used in the custom fields, get option_value label
                if(CRM_Utils_Array::value('option_group_id', $resultCustomField['values'][0])) {
                  $resultOptionValue = civicrm_api3('OptionValue', 'get', array(
                    'sequential' => 1,
                    'option_group_id' => $resultCustomField['values'][0]['option_group_id'],
                    'value' => $customDataDetails['latest'],
                  ));
                  $customData[$resultCustomField['values'][0]['name']] = $resultOptionValue['values'][0]['label'];
                }
              }
            }
          }
        }
        catch (CiviCRM_API3_Exception $e) {
          $error = $e->getMessage();
          CRM_Core_Error::debug_log_message($error);
        }

        //let's build a temp array that we'll send to Outlook as consolidated result
        $temp[$groupID][$key]['group_id']                     = $groupID;
        $temp[$groupID][$key]['group_title']                  = $groupDetails[$groupID];
        $temp[$groupID][$key]['contact_id']                   = $contactDetails['contact_id'];
        $temp[$groupID][$key]['first_name']                   = $contactDetails['first_name'];
        $temp[$groupID][$key]['last_name']                    = $contactDetails['last_name'];

        //primary email (Maps to Outlook field type -> Email)
        $temp[$groupID][$key]['email']                        = $contactDetails['email'];

        //additional contact field(s)
        $temp[$groupID][$key]['current_employer']             = $contactDetails[$mappings['values']['CompanyName']];
        $temp[$groupID][$key]['job_title']                    = $contactDetails[$mappings['values']['JobTitle']];

        //additional email(s): key mapping -> email_2 = email_[just_a_random_number]
        $temp[$groupID][$key]['email_2']                      = $additionalEmails[$mappings['values']['Email2Address']];
        $temp[$groupID][$key]['email_3']                      = $additionalEmails[$mappings['values']['Email3Address']];

        //additional phone number(s): key mapping -> phone_1_1 = phone_[location_type_id]_[phone_type_id]
        /* Following are the mappings of phone field(s)(Outlook vs CiviCRM)
        * Outlook         CiviCRM
                          Location type       Phone type
        * Home         -> Home              - Phone
        * Home 2       -> Home              - Mobile
        * Home fax     -> Home              - Fax
        * Business     -> Work              - Phone
        * Business 2   -> Work              - Mobile
        * Business fax -> Work              - Fax
        * Assistant    -> Work              - Assistant
        */
        $temp[$groupID][$key]['phone_1_1']                    = $additionalPhoneNumbers[$mappings['values']['HomeTelephoneNumber']][1];
        $temp[$groupID][$key]['phone_1_2']                    = $additionalPhoneNumbers[$mappings['values']['Home2TelephoneNumber']][2];
        $temp[$groupID][$key]['phone_1_3']                    = $additionalPhoneNumbers[$mappings['values']['HomeFaxNumber']][3];
        $temp[$groupID][$key]['phone_2_1']                    = $additionalPhoneNumbers[$mappings['values']['BusinessTelephoneNumber']][1];
        $temp[$groupID][$key]['phone_2_2']                    = $additionalPhoneNumbers[$mappings['values']['Business2TelephoneNumber']][2];
        $temp[$groupID][$key]['phone_2_3']                    = $additionalPhoneNumbers[$mappings['values']['BusinessFaxNumber']][3];
        $temp[$groupID][$key]['phone_2_6']                    = $additionalPhoneNumbers[$mappings['values']['AssistantTelephoneNumber']][$assistantId];

        //additional address(s): key mapping -> address_2 = address_[location_type_id]
        /* Following are the mappings of address field(s)(Outlook vs CiviCRM)
        * Outlook         CiviCRM
                          Location type
        * Home         -> Home
        * Business     -> Work
        * Other        -> Other
        */
        //home address
        $temp[$groupID][$key]['address_1']                    = $additionalAddresses[$mappings['values']['HomeAddressStreet']];
        $temp[$groupID][$key]['address_1']                    = $additionalAddresses[$mappings['values']['HomeAddressCity']];
        $temp[$groupID][$key]['address_1']                    = $additionalAddresses[$mappings['values']['HomeAddressPostalCode']];
        $temp[$groupID][$key]['address_1']                    = $additionalAddresses[$mappings['values']['HomeAddressState']];
        $temp[$groupID][$key]['address_1']                    = $additionalAddresses[$mappings['values']['HomeAddressCountry']];
        //business address
        $temp[$groupID][$key]['address_2']                    = $additionalAddresses[$mappings['values']['BusinessAddressStreet']];
        $temp[$groupID][$key]['address_2']                    = $additionalAddresses[$mappings['values']['BusinessAddressCity']];
        $temp[$groupID][$key]['address_2']                    = $additionalAddresses[$mappings['values']['BusinessAddressPostalCode']];
        $temp[$groupID][$key]['address_2']                    = $additionalAddresses[$mappings['values']['BusinessAddressState']];
        $temp[$groupID][$key]['address_2']                    = $additionalAddresses[$mappings['values']['BusinessAddressCountry']];
        //other address
        $temp[$groupID][$key]['address_4']                    = $additionalAddresses[$mappings['values']['OtherAddressStreet']];
        $temp[$groupID][$key]['address_4']                    = $additionalAddresses[$mappings['values']['OtherAddressCity']];
        $temp[$groupID][$key]['address_4']                    = $additionalAddresses[$mappings['values']['OtherAddressPostalCode']];
        $temp[$groupID][$key]['address_4']                    = $additionalAddresses[$mappings['values']['OtherAddressState']];
        $temp[$groupID][$key]['address_4']                    = $additionalAddresses[$mappings['values']['OtherAddressCountry']];

        //assign custom data array to temp array
        $temp[$groupID][$key][custom_fields]                   = $customData;

        //perform cleanup
        unset($additionalEmails, $additionalPhoneNumbers, $additionalAddresses);
      }
    }
  }

  //build final result and return to Outlook
  $result['values'] = call_user_func_array('array_merge', $temp);
  return $result;
}


/**
 * Get Outlook and CiviCRM field mapping for additional contact details
 */
function civicrm_api3_civi_outlook_getadditionalfieldmapping($params) {
  $queryMapping = "SELECT *
  FROM outlook_civicrm_additional_contact_field_mapping
  WHERE 1";

  $dao = CRM_Core_DAO::executeQuery($queryMapping);
  $result = array();
  while ($dao->fetch()) {
    $result[$dao->outlook_field] = $dao->civicrm_field;
  }

  return civicrm_api3_create_success($result, $params);
}
