<?php

/**
 * OSSMailView preview view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
Class OSSMailView_Preview_View extends Vtiger_Index_View
{

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordPermission = \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function process(\App\Request $request)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$load = $request->get('noloadlibs');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$from = $recordModel->getDisplayValue('from_email');
		$to = $recordModel->getDisplayValue('to_email');
		$to = explode(',', $to);
		$cc = $recordModel->getDisplayValue('cc_email');
		$bcc = $recordModel->getDisplayValue('bcc_email');
		$subject = $recordModel->getDisplayValue('subject');
		$owner = $recordModel->getDisplayValue('assigned_user_id');
		$sent = $recordModel->getDisplayValue('createdtime');

		// pobierz załączniki
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				'Documents' ActivityType,vtiger_attachments.type  FileType,vtiger_crmentity.modifiedtime,
				vtiger_seattachmentsrel.attachmentsid attachmentsid, vtiger_notes.notesid crmid, vtiger_notes.notecontent description,vtiger_notes.*
				from vtiger_notes
				LEFT JOIN vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid =vtiger_notes.notesid
				LEFT JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid= vtiger_users.id
				LEFT JOIN vtiger_ossmailview_files ON vtiger_ossmailview_files.documentsid =vtiger_notes.notesid
				WHERE vtiger_ossmailview_files.ossmailviewid = ?";
		$params = [$record];
		$result = $db->pquery($query, $params, true);
		$num = $db->numRows($result);

		$attachments = [];
		for ($i = 0; $i < $num; $i++) {
			$attachments[$i]['name'] = $db->queryResult($result, $i, 'title');
			$attachments[$i]['file'] = $db->queryResult($result, $i, 'filename');
			$attachments[$i]['id'] = $db->queryResult($result, $i, 'crmid');
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('NOLOADLIBS', $load);
		$viewer->assign('FROM', $from);
		$viewer->assign('TO', $to);
		$viewer->assign('CC', $cc);
		$viewer->assign('BCC', $bcc);
		$viewer->assign('SUBJECT', $subject);
		$viewer->assign('URL', "index.php?module=$moduleName&view=Mbody&record=$record");
		$viewer->assign('OWNER', $owner);
		$viewer->assign('SENT', $sent);
		$viewer->assign('ATTACHMENTS', $attachments);
		$viewer->assign('RECORD', $record);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('ISMODAL', $request->isAjax());
		$viewer->assign('SCRIPTS', $this->getModalScripts($request));
		$viewer->assign('SMODULENAME', $request->getByType('smodule'));
		$viewer->assign('SRECORD', $request->getInteger('srecord'));
		$viewer->view('preview.tpl', 'OSSMailView');
	}

	public function getModalScripts(\App\Request $request)
	{
		$scripts = [
			'~layouts/basic/modules/OSSMailView/resources/preview.js'
		];
		$modalScripts = $this->checkAndConvertJsScripts($scripts);
		return $modalScripts;
	}
}
