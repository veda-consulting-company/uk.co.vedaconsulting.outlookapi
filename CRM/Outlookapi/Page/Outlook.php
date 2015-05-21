<?php

require_once 'CRM/Core/Page.php';

class CRM_Outlookapi_Page_Outlook extends CRM_Core_Page {
  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Outlook'));

    // Example: Assign a variable for use in a template
    $this->assign('currentTime', date('Y-m-d H:i:s'));
    $selectQuery = "SELECT  `datetime`,`action`, `entity`,`request`,`response` FROM `outlook_civicrm_audit`";
    $outlookaudit = CRM_Core_DAO::executeQuery($selectQuery);
    $result = array();

    while ($outlookaudit->fetch()) {
      $result[$outlookaudit->datetime] = $outlookaudit->toArray();
    }
    $this->assign('outlookaudit', $result);
    parent::run();
  }
}

//    $dao = CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);
//$currentEmployer = array();
//while ($dao->fetch()) {
//  $currentEmployer[$dao->id]['org_id'] = $dao->employer_id;
//  $currentEmployer[$dao->id]['org_name'] = $dao->organization_name;
//}