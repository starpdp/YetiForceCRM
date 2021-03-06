<?php

/**
 * RelationAjax Class for SSalesProcesses
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SSalesProcesses_RelationAjax_Action extends Vtiger_RelationAjax_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getHierarchyCount');
	}

	public function getHierarchyCount(\App\Request$request)
	{
		$sourceModule = $request->getModule();
		$recordId = $request->getInteger('record');
		if (!\App\Privilege::isPermitted($sourceModule, 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$focus = CRMEntity::getInstance($sourceModule);
		$hierarchy = $focus->getHierarchy($recordId);
		$response = new Vtiger_Response();
		$response->setResult(count($hierarchy['entries']) - 1);
		$response->emit();
	}
}
