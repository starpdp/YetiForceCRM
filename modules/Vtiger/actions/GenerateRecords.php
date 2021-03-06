<?php

/**
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_GenerateRecords_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getByType('target'), 'CreateView') || !$userPriviligesModel->hasModuleActionPermission($request->getModule(), 'RecordMappingList')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function checkMandatoryFields($recordModel)
	{
		$mandatoryFields = $recordModel->getModule()->getMandatoryFieldModels();
		foreach ($mandatoryFields as $field) {
			if (empty($recordModel->get($field->getName()))) {
				return true;
			}
		}
		return false;
	}

	public function process(\App\Request $request)
	{
		$records = $request->getArray('records');
		$moduleName = $request->getModule();
		$template = $request->getInteger('template');
		$targetModuleName = $request->getByType('target');
		$method = $request->getInteger('method');
		$success = [];
		if (!empty($template)) {
			$templateRecord = Vtiger_MappedFields_Model::getInstanceById($template);
			foreach ($records as $recordId) {
				if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && $templateRecord->checkFiltersForRecord(intval($recordId))) {
					if ($method == 0) {
						$recordModel = Vtiger_Record_Model::getCleanInstance($targetModuleName);
						$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
						$recordModel->setRecordFieldValues($parentRecordModel);
						if ($this->checkMandatoryFields($recordModel)) {
							continue;
						}
						$recordModel->save();
						if (\App\Record::isExists($recordModel->getId())) {
							$success[] = $recordId;
						}
					} else {
						$success[] = $recordId;
					}
				}
			}
		}
		$output = ['all' => count($records), 'ok' => $success, 'fail' => array_diff($records, $success)];
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		return $request->validateWriteAccess();
	}
}
