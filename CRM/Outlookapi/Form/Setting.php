<?php
class CRM_Outlookapi_Form_Setting extends CRM_Core_Form {
  const
    OUTLOOK_SETTING_GROUP = 'Outlook Preferences';

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    // Get all activity types
    $activityTypes = CRM_Core_PseudoConstant::activityType(FALSE);
    $this->addElement('select', 'activity_type', ts('Activity Type'), array('3' => ts('Email')) + $activityTypes);

    // Create the Submit Button.
    $buttons = array(
      array(
        'type' => 'submit',
        'name' => ts('Save'),
      ),
    );

    // Add the Buttons.
    $this->addButtons($buttons);
    parent::buildQuickForm();
  }

  public function setDefaultValues() {
    $defaults = $details = array();
    $activity_type = CRM_Core_BAO_Setting::getItem(self::OUTLOOK_SETTING_GROUP,
      'activity_type', NULL, FALSE
    );
    $defaults['activity_type'] = $activity_type;
    return $defaults;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    // Store the submitted values in an array.
    $params = $this->controller->exportValues($this->_name);

    if (CRM_Utils_Array::value('activity_type', $params)) {
      CRM_Core_BAO_Setting::setItem($params['activity_type'],
        self::OUTLOOK_SETTING_GROUP,
        'activity_type'
      );
    }

    CRM_Core_Session::setStatus(ts('Changes saved!'), ts('Outlook Settings'), 'success');
  }
}