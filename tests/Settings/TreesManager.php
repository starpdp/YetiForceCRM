<?php
/**
 * TreesManager test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
namespace Tests\Settings;

class TreesManager extends \Tests\Init\Base
{

	/**
	 * Array of trees id
	 * @var array
	 */
	private static $treesId;

	/**
	 * Testing creation tree
	 * @param int|string $key
	 * @param int|null $moduleId
	 * @param array $tree
	 * @dataProvider providerForTree
	 */
	public function testAddTree($key, $moduleId = NULL, $tree = [], $share = [])
	{
		if (empty($moduleId)) {
			$moduleId = \App\Module::getModuleId('Dashboard');
		}

		$recordModel = new \Settings_TreesManager_Record_Model();
		$recordModel->set('name', 'TestTree' . $key);
		$recordModel->set('module', $moduleId);
		$recordModel->set('tree', $tree);
		$recordModel->set('share', $share);
		$recordModel->set('replace', "");
		$recordModel->save();
		static::$treesId[$key] = $recordModel->getId();

		$row = (new \App\Db\Query())->from('vtiger_trees_templates')->where(['templateid' => static::$treesId[$key]])->one();
		$this->assertEquals($row['name'], 'TestTree' . $key);
		$this->assertEquals($row['module'], $moduleId);
		$this->assertEquals($row['share'], \Settings_TreesManager_Record_Model::getShareFromArray($share));
		$this->assertEquals((new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => static::$treesId[$key]])->count(), static::countItems($tree));
	}

	/**
	 * Testing edition tree
	 * @param int|string $key
	 * @param array $tree
	 * @dataProvider providerForEditTree
	 */
	public function testEditTree($key, $tree = [], $share = [])
	{
		$recordModel = \Settings_TreesManager_Record_Model::getInstanceById(static::$treesId[$key]);
		$this->assertNotNull($recordModel, 'Settings_TreesManager_Record_Model is null');
		$recordModel->set('name', 'TestTreeEdit' . $key);
		$recordModel->set('tree', $tree);
		$recordModel->set('share', $share);
		$recordModel->set('replace', "");
		$recordModel->save();

		$row = (new \App\Db\Query())->from('vtiger_trees_templates')->where(['templateid' => static::$treesId[$key]])->one();
		$this->assertEquals($row['name'], 'TestTreeEdit' . $key);
		$this->assertEquals($row['share'], \Settings_TreesManager_Record_Model::getShareFromArray($share));
		$this->assertEquals((new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => static::$treesId[$key]])->count(), static::countItems($tree));
	}

	/**
	 * Testing deletion tree
	 * @param int|string $key
	 * @param int|null $moduleId
	 * @param array $tree
	 * @dataProvider providerForTree
	 */
	public function testDeleteTree($key, $moduleId = NULL, $tree = [], $share = [])
	{
		$recordModel = \Settings_TreesManager_Record_Model::getInstanceById(static::$treesId[$key]);
		$recordModel->delete();

		$this->assertFalse((new \App\Db\Query())->from('vtiger_trees_templates')->where(['templateid' => static::$treesId[$key]])->exists(), 'The record was not removed from the database ID: ' . static::$treesId[$key]);

		$this->assertEquals((new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => static::$treesId[$key]])->count(), 0, 'The records were not removed from the table "vtiger_trees_templates_data"');
	}

	/**
	 * Data provider for testAddTree
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function providerForTree()
	{
		$tree1[] = $this->createItemForTree('item1', 1);
		$tree1[] = $this->createItemForTree('item2', 2);

		$share2[] = \App\Module::getModuleId('Contacts');
		$share2[] = \App\Module::getModuleId('Leads');
		$share2[] = \App\Module::getModuleId('Calendar');

		$tree4[] = $this->createItemForTree('item1', 1);
		$tree4[] = $this->createItemForTree('item2', 2, [$this->createItemForTree('item3', 3), $this->createItemForTree('item4', 4)]);

		return [
			[0, NULL, [], []],
			[1, NULL, $tree1, []],
			[2, NULL, [], $share2],
			[3, NULL, $tree1, $share2],
			[4, NULL, $tree4, []],
		];
	}

	/**
	 * Data provider for testEditTree
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function providerForEditTree()
	{
		$tree0[] = $this->createItemForTree('itemEdit1', 1);
		$tree0[] = $this->createItemForTree('itemEdit2', 2);

		$share1[] = \App\Module::getModuleId('Contacts');
		$share1[] = \App\Module::getModuleId('Leads');
		$share1[] = \App\Module::getModuleId('Calendar');

		$tree4[] = $this->createItemForTree('item1Edit', 1);
		$tree4[] = $this->createItemForTree('item2Edit', 2, [$this->createItemForTree('item3Edit', 3), $this->createItemForTree('itemEdit4', 4)]);

		return [
			[0, $tree0, []],
			[1, [], $share1],
			[2, $tree4, $share1],
			[3, [], []],
			[4, [], []],
		];
	}

	/**
	 * Create item for tree array
	 * @param string $itemName
	 * @param int $id
	 * @return array
	 * @codeCoverageIgnore
	 */
	private function createItemForTree($itemName, $id, $children = [])
	{
		return [
			'id' => $id,
			'text' => $itemName,
			'icon' => '1',
			'li_attr' => ['id' => $id],
			'a_attr' => ['href' => '#', 'id' => $id . '_anchor'],
			'state' => ['loaded' => '1', 'opened' => false, 'selected' => false, 'disabled' => false],
			'data' => [],
			'children' => $children,
		];
	}

	/**
	 * Count item in tree
	 * @param array $tree
	 * @return int
	 */
	private static function countItems($tree)
	{
		$cnt = count($tree);
		foreach ($tree as $item) {
			if (is_array($item['children'])) {
				$cnt += static::countItems($item['children']);
			}
		}
		return $cnt;
	}
}
