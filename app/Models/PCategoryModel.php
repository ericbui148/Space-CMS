<?php
namespace App\Models;

class PCategoryModel extends AppModel
{
	protected $primaryKey = 'id';
	
	protected $table = 'pcategories';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'avatar', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'parent_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'lft', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'rgt', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'uuid_code', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'template', 'type' => 'varchar', 'default' => ':NULL')
	);
	
	protected $i18n = array('name', 'desc');
	
	public static function factory($attr=array())
	{
		return new self($attr);
	}
	
	public function getNode($locale_id, $id = null)
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
	    		->join('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'PCategory' AND t2.locale = '$locale_id' AND t2.field = 'name'", 'left outer')
	    		->where(sprintf("t1.lft BETWEEN '%u' AND '%u'", $arr[0]['lft'], $arr[0]['rgt']))
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