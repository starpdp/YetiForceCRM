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

/**
 * Class Vtiger_Multiowner_UIType
 */
class Vtiger_Multiowner_UIType extends Vtiger_Base_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_array($value)) {
			throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
		}
		foreach ($value as $shownerid) {
			if (!is_numeric($shownerid)) {
				throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
			}
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($values, $record = false, $recordInstance = false, $rawText = false)
	{
		if ($values === null && !is_array($values)) {
			return '';
		}
		foreach ($values as $value) {
			if (self::getOwnerType($value) === 'User') {
				$userModel = Users_Record_Model::getCleanInstance('Users');
				$userModel->setId($value);
				$detailViewUrl = $userModel->getDetailViewUrl();
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if (!$currentUser->isAdminUser()) {
					return \App\Fields\Owner::getLabel($value);
				}
			} else {
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if (!$currentUser->isAdminUser()) {
					return \App\Fields\Owner::getLabel($value);
				}
				$recordModel = new Settings_Groups_Record_Model();
				$recordModel->set('groupid', $value);
				$detailViewUrl = $recordModel->getDetailViewUrl();
			}
			if ($rawText) {
				$displayvalue[] = \App\Fields\Owner::getLabel($value);
			} else {
				$displayvalue[] = "<a href=" . $detailViewUrl . ">" . \App\Fields\Owner::getLabel($value) . "</a>&nbsp";
			}
		}
		return implode(',', $displayvalue);
	}

	/**
	 * Function to know owner is either User or Group
	 * @param integer $id userId/GroupId
	 * @return string User/Group
	 */
	public static function getOwnerType($id)
	{
		return \App\Fields\Owner::getType($id);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/MultiOwner.tpl';
	}
}
