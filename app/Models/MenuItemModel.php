<?php
namespace App\Models;

class MenuItemModel extends AppModel
{
	/**
	 * Status
	 */
	const STATUS_SHOW = 'SHOW';
	const STATUS_DISABLE = 'DISABLE';
	/**
	 * Link type
	 */
	const LINK_TYPE_DEFAULT = 1;
	const LINK_TYPE_SINGLE_ARTICLE = 2;
	const LINK_TYPE_ARTICE_CATEGORY = 3;
	const LINK_TYPE_PAGE = 4;
	const LINK_TYPE_PRODUCT = 5;
	const LINK_TYPE_PRODUCT_CATEGORY = 6;
	const LINK_TYPE_GALLERY = 7;
	const LINK_TYPE_TAG = 8;
	const LINK_TYPE_PAGE_CATEGORY = 9;
	/**
	 * Open target 
	 */
	const OPEN_TARGET_CURRENT_TAB = 1;
	const OPEN_TARGET_OTHER_TAB = 2;
	const OPEN_TARGET_OTHER_PAGE = 3;
	const OPEN_TARGET_POPUP = 4;

	public $multipleLangKey = 'MenuItem';
	protected $primaryKey = 'id';
	
	protected $table = 'menu_items';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'link_type', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'link_data', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'avatar', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'class', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'open_target', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'lft', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'rgt', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'parent_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'foreign_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => 'Show'),
		array('name' => 'uuid_code', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'metadata', 'type' => 'text', 'default' => ':NULL')
	);
	
	protected $validate = array(
		'rules' => array(
			'link' => array(
				'Required' => true
			),				
			'left' => array(
				'Required' => true
			),
			'right' => array(
				'Required' => true
			),
			'parent_id' => array(
				'Required' => true
			),
			'foreign_id' => array(
				'Required' => true
			),				
			'status' => array(
				'Required' => true
			),			
		)
	);
	
	
	
	public $i18n = array('name');
	
	public static function factory($attr=array())
	{
		return new MenuItemModel($attr);
	}
	
	public function getNode($locale_id, $id=null, $menuId =  null)
	{
		$tree = array();
		// retrieve the left and right value of the $id node
		if (!is_null($id))
		{
			$this->where('t1.id', $id);
		}
		$arr = $this->limit(1)->findAll()->getData();
		if (count($arr) === 1)
		{
			// start with an empty $right stack
			$right = array();
			// now, retrieve all descendants of the $id node
	
			$descendants = $this->reset()
			->select('t1.*, t2.content AS `name`')
			->join('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'MenuItem' AND t2.locale = '$locale_id' AND t2.field = 'name'", 'left outer')
			->where(sprintf("t1.lft BETWEEN '%u' AND '%u'", $arr[0]['lft'], $arr[0]['rgt']))
			->where('t1.foreign_id', $menuId)
			->orwhere('t1.id', 1)
			->orderBy('t1.lft ASC')
			->findAll()
			->getData();
			// display each row
			foreach ($descendants as $descendant)
			{
				// only check stack if there is one
				if (count($right) > 0)
				{
					// check if we should remove a node from the stack
					while ($right[count($right) - 1] < $descendant['rgt'])
					{
						array_pop($right);
					}
				}
				// display indented node title
				$repeatAmount = count($right) - 1;
				if($repeatAmount < 0)
				{
					$repeatAmount = 0;
				}
				if ($descendant['id'] != $id)
				{
					$tree[] = array(
							'deep' => $repeatAmount,
							'children' => ($descendant['rgt'] - $descendant['lft'] - 1) / 2,
							'data' => $descendant
					);
				}
				// add this node to the stack
				$right[] = $descendant['rgt'];
			}
			$siblings = array();
			foreach ($tree as $k => $v)
			{
				if (!isset($siblings[$v['deep']."|".$v['data']['parent_id']]))
				{
					$siblings[$v['deep']."|".$v['data']['parent_id']] = 0;
				}
				$siblings[$v['deep']."|".$v['data']['parent_id']] += 1;
			}
			foreach ($tree as $k => $v)
			{
				$tree[$k]['siblings'] = isset($siblings[$v['deep']."|".$v['data']['parent_id']]) ? $siblings[$v['deep']."|".$v['data']['parent_id']] : 0;
			}
		}
		return $tree;
	}
	
	public function rebuildTree($parent_id, $left)
	{
		// the right value of this node is the left value + 1
		$right = $left + 1;
		// get all children of this node
		$arr = $this->reset()->where('t1.parent_id', $parent_id)->orderBy('t1.lft ASC')->findAll()->getDataPair('id', 'id');
		foreach ($arr as $id)
		{
			// recursive execution of this function for each
			// child of this node
			// $right is the current right value, which is
			// incremented by the rebuildTree method
			$right = $this->rebuildTree($id, $right);
		}
		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$this->reset()->set('id', $parent_id)->modify(array('lft' => $left, 'rgt' => $right));
		// return the right value of this node + 1
		return $right + 1;
	}
	
	public function saveNode($data, $parent_id)
	{
		if ((int) $parent_id > 0)
		{
			$parent = $this->reset()->find($parent_id)->getData();
			if (count($parent) > 0)
			{
				$_lft = $parent['rgt'];
				$_rgt = $_lft + 1;
				$rgt = $parent['rgt'] - 1;
			} else {
				// Parent node does not exists
				return -1;
			}
		} else {
			$_lft = 1;
			$_rgt = 2;
		}
	
		$data = array_merge($data, array('lft' => $_lft, 'rgt' => $_rgt));
		$id = $this->reset()->setAttributes($data)->insert()->getInsertId();
		if ($id !== false && (int) $id > 0 && isset($rgt))
		{
			$this
			->reset()
			->where('id !=', $id)
			->where('rgt >', $rgt)
			->modifyAll(array('rgt' => ':rgt + 2'))
			->reset()
			->where('id !=', $id)
			->where('lft >', $rgt)
			->modifyAll(array('lft' => ':lft + 2'));
		}
		$this->rebuildTree(1, 1);
		return $id;
	}
	
	public function updateNode($data)
	{
		$this->reset()->set('id', $data['id'])->modify($data);
		$this->rebuildTree(1, 1);
	}
	
	public function deleteNode($node_id, $node=null)
	{
		if (is_null($node))
		{
			$node = $this->reset()->find($node_id)->getData();
		}
		if (count($node) > 0)
		{
			$rgt = $node['rgt'];
		} else {
			// Node does not exists
			return -1;
		}
	
		// Delete children nodes
		$children = $this->reset()->where('t1.parent_id', $node_id)->findAll()->getData();
		foreach ($children as $child)
		{
			$this->deleteNode($child['id'], $child);
		}
	
		// Delete node
		$result = $this->reset()->set('id', $node_id)->erase()->getAffectedRows();
		if ((int) $result > 0)
		{
			$this
			->reset()
			->where('rgt >', $rgt)
			->modifyAll(array('rgt' => ':rgt - 2'))
			->reset()
			->where('lft >', $rgt)
			->modifyAll(array('lft' => ':lft - 2'));
		}
		return $result;
	}
}
?>