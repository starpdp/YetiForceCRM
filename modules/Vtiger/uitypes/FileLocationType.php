<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_FileLocationType_UIType extends Vtiger_Picklist_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		parent::validate($value, $isUserFormat);
		$this->validate = false;
		$allowedPicklist = $this->getPicklistValues();
		if (!isset($allowedPicklist[$value])) {
			throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$values = $this->getPicklistValues();
		return \App\Purifier::encodeHtml(isset($values[$value]) ? $values[$value] : $value);
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		$moduleName = $this->get('field')->getModuleName();
		return ['I' => \App\Language::translate('LBL_INTERNAL', $moduleName), 'E' => \App\Language::translate('LBL_EXTERNAL', $moduleName)];
	}

	/**
	 * Function defines empty picklist element availability
	 * @return boolean
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		return false;
	}
}
