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

class Vtiger_UserReference_UIType extends Vtiger_Base_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_numeric($value) || \App\User::isExists($value)) {
			throw new \App\Exceptions\SaveRecord('ERR_ILLEGAL_FIELD_VALUE', 406);
		}
		$this->validate = true;
	}

	/**
	 * Function to get the edit value in display view
	 * @param mixed $value
	 * @param Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if ($value) {
			return \App\Fields\Owner::getLabel($value);
		}
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($value, $recordId = false, $recordInstance = false, $rawText = false)
	{
		$displayValue = $this->getEditViewDisplayValue($value, $recordInstance);
		if (App\User::getCurrentUserModel()->isAdmin() && !$rawText) {
			$recordModel = Users_Record_Model::getCleanInstance('Users');
			$recordModel->setId($value);
			return '<a href="' . $recordModel->getDetailViewUrl() . '">' . \vtlib\Functions::textLength($displayValue) . '</a>';
		}
		return \vtlib\Functions::textLength($displayValue);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/Reference.tpl';
	}
}
