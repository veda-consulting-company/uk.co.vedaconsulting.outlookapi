<?php

require_once 'outlookapi.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function outlookapi_civicrm_config(&$config) {
  _outlookapi_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function outlookapi_civicrm_xmlMenu(&$files) {
  _outlookapi_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function outlookapi_civicrm_install() {
  return _outlookapi_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function outlookapi_civicrm_uninstall() {
  return _outlookapi_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function outlookapi_civicrm_enable() {
  return _outlookapi_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function outlookapi_civicrm_disable() {
  return _outlookapi_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function outlookapi_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _outlookapi_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function outlookapi_civicrm_managed(&$entities) {
  return _outlookapi_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function outlookapi_civicrm_caseTypes(&$caseTypes) {
  _outlookapi_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function outlookapi_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _outlookapi_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 *  alterAPIPermissions() hook allows you to change the permissions checked when doing API 3 calls.
 */
function outlookapi_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  //Native APIs
  $permissions['domain'] = array('get' => array('access CiviCRM', 'access AJAX API'));
  $permissions['activity'] = array('create' => array('access CiviCRM', 'access AJAX API', 'add contacts', 'view all contacts', 'view all activities', 'access uploaded files'));

  //Custom APIs
  $permissions['civi_outlook']['getdomain'] = array('access CiviCRM','access AJAX API');
  $permissions['civi_outlook']['createactivity'] = array('access CiviCRM', 'access AJAX API', 'add contacts', 'view all contacts', 'view all activities', 'access uploaded files');
  $permissions['civi_outlook']['insertauditlog'] = array('access CiviCRM', 'access AJAX API');
  $permissions['civi_outlook']['getlables'] = array('access CiviCRM', 'access AJAX API');
  $permissions['civi_outlook']['userdefault'] = array('access CiviCRM', 'access AJAX API');
  $permissions['civi_outlook']['getuserdefaults'] = array('access CiviCRM', 'access AJAX API');
  $permissions['civi_outlook']['setting'] = array('access CiviCRM', 'access AJAX API');
  $permissions['civi_outlook']['processattachments'] = array('access CiviCRM', 'access AJAX API', 'access uploaded files');
  $permissions['civi_outlook']['getactivitytype'] = array('access CiviCRM', 'access AJAX API', 'access uploaded files', 'access my cases and activities', 'add cases');
  $permissions['civi_outlook']['getcaseactivitytype'] = array('access CiviCRM', 'access AJAX API', 'access my cases and activities');
  $permissions['civi_outlook']['getcaseactivitytype'] = array('access CiviCRM', 'access AJAX API', 'access my cases and activities');
  $permissions['civi_outlook']['createnewcase'] = array('access CiviCRM', 'access AJAX API', 'add cases', 'access my cases and activities');
  $permissions['civi_outlook']['getcivicasestatus'] = array('access CiviCRM', 'access AJAX API', 'access my cases and activities');
  $permissions['civi_outlook']['getcivicasetypes'] = array('access CiviCRM', 'access AJAX API', 'access my cases and activities');
  $permissions['civi_outlook']['getcivicases'] = array('access CiviCRM', 'access AJAX API', 'access my cases and activities');
}
